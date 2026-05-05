#!/usr/bin/env python3
"""
Backfill OpenAlex metadata for OSIRIS activities with a DOI.

Logic:
- If activity has no openalex block: fetch + store.
- If openalex.fetched_at is older than 30 days: refresh.
- Otherwise: skip.

Prepared for potential API key usage (optional):
- Set OPENALEX_API_KEY env var (currently sent as header; can be changed later).
"""

from __future__ import annotations

import configparser
import os
import re
import sys
import time
from datetime import datetime, timedelta, timezone
from typing import Any, Dict, Optional, Tuple

import requests
from pymongo import MongoClient

nowIso = datetime.now(timezone.utc).isoformat()
nowIsoShort = nowIso.split("T")[0]

DOI_PREFIX_RE = re.compile(r"^\s*doi:\s*", re.IGNORECASE)


def utc_now() -> datetime:
    return datetime.now(timezone.utc)


def parse_dt(value: Any) -> Optional[datetime]:
    """Parse ISO timestamps stored in Mongo (string). Returns UTC-aware datetime or None."""
    if not value or not isinstance(value, str):
        return None
    # Handle "2026-02-22T12:34:56Z" and "2026-02-22T12:34:56+00:00"
    v = value.strip()
    try:
        if v.endswith("Z"):
            v = v[:-1] + "+00:00"
        dt = datetime.fromisoformat(v)
        if dt.tzinfo is None:
            dt = dt.replace(tzinfo=timezone.utc)
        return dt.astimezone(timezone.utc)
    except Exception:
        return None


def normalize_doi(raw: str) -> str:
    doi = DOI_PREFIX_RE.sub("", (raw or "").strip())
    return doi.lower()


def short_id(url: Optional[str]) -> Optional[str]:
    """Extract trailing segment from OpenAlex URL (e.g., https://openalex.org/T123 -> T123)."""
    if not url or not isinstance(url, str):
        return None
    return url.rstrip("/").split("/")[-1] or None


def normalize_topics(topics: Any) -> list[dict]:
    """Flatten OpenAlex topics to a query-friendly structure."""
    if not topics or not isinstance(topics, list):
        return []

    out: list[dict] = []
    for t in topics:
        if not isinstance(t, dict):
            continue

        domain = t.get("domain") or {}
        field = t.get("field") or {}
        subfield = t.get("subfield") or {}

        domain_name = (domain.get("display_name") or "").strip()
        field_name = (field.get("display_name") or "").strip()
        subfield_name = (subfield.get("display_name") or "").strip()

        path_parts = [p for p in [domain_name, field_name, subfield_name] if p]
        path = " → ".join(path_parts) if path_parts else ""

        out.append(
            {
                "id": short_id(t.get("id")),  # e.g., T10102
                "name": t.get("display_name"),
                "score": t.get("score"),
                "domain_id": short_id(domain.get("id")),
                "domain": domain_name or None,
                "field_id": short_id(field.get("id")),
                "field": field_name or None,
                "subfield_id": short_id(subfield.get("id")),
                "subfield": subfield_name or None,
                "path": path or None,
            }
        )

    # Drop entries without id/name
    out = [x for x in out if x.get("id") and x.get("name")]
    # Sort by score desc
    out.sort(key=lambda x: x.get("score") or 0, reverse=True)
    return out


def build_openalex_url(doi: str) -> str:
    doi = requests.utils.quote(doi, safe='')
    return f"https://api.openalex.org/works/doi:{doi}?select=id,cited_by_count,updated_date,topics"


def fetch_openalex_by_doi(doi: str, api_key: Optional[str], timeout: Tuple[int, int]) -> Tuple[Optional[dict], Optional[str]]:
    """Return (json, error)."""
    url = build_openalex_url(doi)
    headers = {
        "Accept": "application/json",
        "User-Agent": "OSIRIS OpenAlex Backfill/1.0",
    }

    if api_key:
        headers["X-API-Key"] = api_key 

    try:
        r = requests.get(url, headers=headers, timeout=timeout)
        if r.status_code == 404:
            return None, "not_found"
        if r.status_code == 429:
            return None, "rate_limited"
        if r.status_code < 200 or r.status_code >= 300:
            return None, f"http_{r.status_code}"
        return r.json(), None
    except requests.RequestException as e:
        return None, f"request_error:{e.__class__.__name__}"


def should_refresh(doc: dict, cutoff: datetime) -> bool:
    oa = doc.get("openalex")
    if not oa or not isinstance(oa, dict):
        return True
    fetched_at = parse_dt(oa.get("fetched_at"))
    if not fetched_at:
        return True
    return fetched_at < cutoff


def get_doi_from_activity(doc: dict) -> Optional[str]:
    # Adjust keys if your schema differs
    for key in ("doi", "DOI"):
        v = doc.get(key)
        if isinstance(v, str) and v.strip():
            return normalize_doi(v)
    identifiers = doc.get("identifiers") or {}
    v = identifiers.get("doi")
    if isinstance(v, str) and v.strip():
        return normalize_doi(v)
    return None


def main() -> int:
    # read the config file
    config = configparser.ConfigParser()
    path = os.path.dirname(__file__)
    config.read(os.path.join(path, 'config.ini'))

    # set up database connection
    mongo_uri = config['Database']['Connection']
    mongo_db = config['Database']['Database']

    api_key = config['OpenAlex']['ApiKey'].strip() or None
    
    coll_name = "activities"


    # Behavior tuning
    days = 30
    batch_size = 200
    sleep_s = 0.2
    timeout_connect = 3
    timeout_read = 8

    cutoff = utc_now() - timedelta(days=days)

    client = MongoClient(mongo_uri)
    db = client[mongo_db]
    col = db[coll_name]

    # Find activities with any DOI field set
    query = {
        "doi": {"$exists": True, "$type": "string", "$ne": ""},
        "type": "publication"
    }

    projection = {"doi": 1, "DOI": 1, "identifiers.doi": 1, "openalex": 1}

    cursor = col.find(query, projection=projection, batch_size=batch_size)

    processed = 0
    updated = 0
    skipped = 0
    not_found = 0
    errors = 0
    rate_limited = 0

    for doc in cursor:
        processed += 1

        doi = get_doi_from_activity(doc)
        if not doi:
            skipped += 1
            continue

        if not should_refresh(doc, cutoff):
            skipped += 1
            continue

        # Fetch OpenAlex
        data, err = fetch_openalex_by_doi(doi, api_key=api_key, timeout=(timeout_connect, timeout_read))

        if err == "rate_limited":
            rate_limited += 1
            # simple backoff
            time.sleep(5)
            continue

        oa_block: Dict[str, Any]
        if not data or not data.get("id"):
            if err == "not_found":
                not_found += 1
            else:
                errors += 1

            oa_block = {
                "status": "not_found" if err == "not_found" else "error",
                "doi": doi,
                "error": err,
                "fetched_at": nowIsoShort,
                "source": "openalex",
            }
        else:
            oa_block = {
                "status": "ok",
                "id": data.get("id"),  # OpenAlex work id URL
                "doi": doi,
                "cited_by_count": data.get("cited_by_count"),
                "topics": normalize_topics(data.get("topics")),
                "updated_date": data.get("updated_date"),
                "fetched_at": nowIsoShort,
                "source": "openalex",
            }

        res = col.update_one({"_id": doc["_id"]}, {"$set": {"openalex": oa_block}})
        if res.modified_count:
            updated += 1

        time.sleep(sleep_s)

        if processed % 500 == 0:
            print(
                f"[{processed}] updated={updated} skipped={skipped} not_found={not_found} "
                f"errors={errors} rate_limited={rate_limited}"
            )

    print(
        f"Done. processed={processed} updated={updated} skipped={skipped} "
        f"not_found={not_found} errors={errors} rate_limited={rate_limited}"
    )
    return 0


if __name__ == "__main__":
    raise SystemExit(main())