import configparser
import importlib.util
import io
from contextlib import redirect_stderr, redirect_stdout
from datetime import timedelta
from pathlib import Path
import unittest
from unittest.mock import MagicMock, patch

import requests


spec = importlib.util.spec_from_file_location(
    "openalex_topics_citations",
    Path(__file__).resolve().parents[1] / "openalex_topics_citations.py",
)
job = importlib.util.module_from_spec(spec)
spec.loader.exec_module(job)


class OpenAlexJobTests(unittest.TestCase):
    def run_job(self, doc, response=None, exception=None):
        config = configparser.ConfigParser()
        config.read_dict({
            "Database": {"Connection": "mongodb://unused", "Database": "test"},
            "OpenAlex": {"ApiKey": "test-key"},
        })
        collection = MagicMock()
        collection.find.return_value = [doc]
        client = MagicMock()
        client.__getitem__.return_value.__getitem__.return_value = collection
        with patch.object(job.configparser, "ConfigParser", return_value=config), \
                patch.object(job, "MongoClient", return_value=client), \
                patch.object(job.requests, "get", return_value=response, side_effect=exception), \
                patch.object(job.time, "sleep"), \
                redirect_stdout(io.StringIO()), redirect_stderr(io.StringIO()):
            self.assertEqual(job.main(), 0)
        return collection

    def response(self, status=200, data=None):
        response = MagicMock(status_code=status)
        response.json.return_value = data
        return response

    def test_bearer_authentication(self):
        response = self.response(data={"id": "https://openalex.org/W1"})
        with patch.object(job.requests, "get", return_value=response) as get:
            data, error = job.fetch_openalex_by_doi("10.123/example", "test-key", (3, 8))
        self.assertIsNone(error)
        self.assertEqual(data["id"], "https://openalex.org/W1")
        headers = get.call_args.kwargs["headers"]
        self.assertEqual(headers["Authorization"], "Bearer test-key")
        self.assertNotIn("X-API-Key", headers)

    def test_optional_key(self):
        with patch.object(job.requests, "get", return_value=self.response(data={"id": "W1"})) as get:
            job.fetch_openalex_by_doi("10.123/example", None, (3, 8))
        self.assertNotIn("Authorization", get.call_args.kwargs["headers"])

    def test_failed_refresh_preserves_metadata_and_retries(self):
        old_block = {
            "status": "ok", "id": "W1", "cited_by_count": 42,
            "topics": [{"id": "T1"}], "fetched_at": "2020-01-01",
        }
        doc = {"_id": 1, "doi": "10.123/example", "openalex": old_block}
        invalid_json = self.response()
        invalid_json.json.side_effect = ValueError("Invalid JSON")
        for response, exception in [
            (self.response(status=503), None),
            (self.response(status=401), None),
            (self.response(status=429), None),
            (invalid_json, None),
            (self.response(data=[]), None),
            (self.response(data={}), None),
            (None, requests.Timeout()),
        ]:
            with self.subTest(response=response, exception=exception):
                collection = self.run_job(doc, response, exception)
                collection.update_one.assert_not_called()
                self.assertEqual(doc["openalex"], old_block)
                self.assertTrue(job.should_refresh(doc, job.utc_now() - timedelta(days=30)))

    def test_new_document_failure_does_not_start_cooldown(self):
        doc = {"_id": 1, "doi": "10.123/example"}
        collection = self.run_job(doc, self.response(status=503))
        collection.update_one.assert_not_called()
        self.assertTrue(job.should_refresh(doc, job.utc_now() - timedelta(days=30)))

    def test_recent_legacy_error_is_retried_and_replaced_on_success(self):
        doc = {
            "_id": 1, "doi": "10.123/example",
            "openalex": {"status": "error", "fetched_at": job.utc_now().isoformat()},
        }
        collection = self.run_job(doc, self.response(data={"id": "W1", "cited_by_count": 7}))
        block = collection.update_one.call_args.args[1]["$set"]["openalex"]
        self.assertEqual(block["status"], "ok")
        self.assertEqual(block["cited_by_count"], 7)
        self.assertNotIn("error", block)

    def test_recent_success_and_not_found_keep_monthly_cooldown(self):
        for status in ("ok", "not_found"):
            with self.subTest(status=status):
                doc = {"openalex": {"status": status, "fetched_at": job.utc_now().isoformat()}}
                self.assertFalse(job.should_refresh(doc, job.utc_now() - timedelta(days=30)))

    def test_404_is_cached_as_not_found(self):
        collection = self.run_job({"_id": 1, "doi": "10.123/example"}, self.response(status=404))
        block = collection.update_one.call_args.args[1]["$set"]["openalex"]
        self.assertEqual(block["status"], "not_found")
        self.assertEqual(block["error"], "not_found")


if __name__ == "__main__":
    unittest.main()
