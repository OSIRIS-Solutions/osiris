"""Local integration checks: python3 tests/http-client.py (PHP/cURL and openssl required)."""
import base64
import http.client
import json
import os
from pathlib import Path
import select
import socket
import ssl
import subprocess
import tempfile
import threading
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer
from urllib.parse import urlsplit

ROOT = Path(__file__).resolve().parents[1]
AUTH = 'Basic ' + base64.b64encode(b'test-user:test-password').decode()
seen = []


class Origin(BaseHTTPRequestHandler):
    def log_message(self, *args):
        pass

    def do_GET(self):
        if self.path == '/slow':
            threading.Event().wait(2)
        if self.path == '/redirect':
            self.send_response(302)
            self.send_header('Location', '/ok')
            self.end_headers()
            return
        body = json.dumps({'path': self.path, 'accept': self.headers.get('Accept'),
                           'proxy_auth': self.headers.get('Proxy-Authorization')}).encode()
        self.send_response(503 if self.path == '/fail' else 200)
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        try:
            self.wfile.write(body)
        except (BrokenPipeError, ssl.SSLError):
            pass


class Proxy(Origin):
    protocol_version = 'HTTP/1.1'
    require_auth = False

    def authorized(self):
        if self.require_auth and self.headers.get('Proxy-Authorization') != AUTH:
            self.send_response(407)
            self.send_header('Proxy-Authenticate', 'Basic realm="test"')
            self.send_header('Content-Length', '0')
            self.end_headers()
            return False
        return True

    def do_GET(self):
        if not self.authorized():
            return
        seen.append(self.path)
        target = urlsplit(self.path)
        connection = http.client.HTTPConnection('127.0.0.1', origin.server_port)
        connection.request('GET', target.path, headers={'Accept': self.headers.get('Accept', '')})
        response = connection.getresponse()
        body = response.read()
        self.send_response(response.status)
        self.send_header('Content-Length', str(len(body)))
        self.end_headers()
        self.wfile.write(body)
        connection.close()

    def do_CONNECT(self):
        if not self.authorized():
            return
        seen.append('CONNECT ' + self.path)
        # All test traffic stays on loopback, regardless of the requested hostname.
        upstream = socket.create_connection(('127.0.0.1', secure.server_port))
        self.send_response(200)
        self.end_headers()
        try:
            peers = [self.connection, upstream]
            while True:
                ready, _, _ = select.select(peers, [], [], 3)
                if not ready:
                    return
                for peer in ready:
                    data = peer.recv(65536)
                    if not data:
                        return
                    (upstream if peer is self.connection else self.connection).sendall(data)
        except (ConnectionError, OSError):
            pass
        finally:
            self.close_connection = True
            upstream.close()


def run(url, config=None, timeout=3, env=None, ca=None):
    code = '''
require $argv[1] . '/php/HttpClient.php';
foreach (json_decode($argv[3], true) as $key => $value) define($key, $value);
try { echo HttpClient::get($argv[2], ['Accept: application/json'], (int)$argv[4]); }
catch (Throwable $e) { fwrite(STDERR, $e->getMessage()); exit(1); }
'''
    clean_env = {k: v for k, v in os.environ.items() if 'proxy' not in k.lower()}
    clean_env.update(env or {})
    args = ['php'] + (['-d', 'curl.cainfo=' + str(ca)] if ca else [])
    return subprocess.run(args + ['-r', code, str(ROOT), url, json.dumps(config or {}), str(timeout)],
                          env=clean_env, capture_output=True, text=True, timeout=8)


def success(result):
    assert result.returncode == 0, result.stderr
    return json.loads(result.stdout)


with tempfile.TemporaryDirectory(prefix='osiris-http-') as folder:
    cert, key = Path(folder) / 'cert.pem', Path(folder) / 'key.pem'
    subprocess.run(['openssl', 'req', '-x509', '-newkey', 'rsa:2048', '-nodes', '-days', '1',
                    '-subj', '/CN=localhost', '-addext', 'subjectAltName=DNS:localhost',
                    '-keyout', str(key), '-out', str(cert)], check=True, capture_output=True)
    origin = ThreadingHTTPServer(('127.0.0.1', 0), Origin)
    secure = ThreadingHTTPServer(('127.0.0.1', 0), Origin)
    context = ssl.SSLContext(ssl.PROTOCOL_TLS_SERVER)
    context.load_cert_chain(cert, key)
    secure.socket = context.wrap_socket(secure.socket, server_side=True)
    proxy = ThreadingHTTPServer(('127.0.0.1', 0), Proxy)
    for server in [origin, secure, proxy]:
        threading.Thread(target=server.serve_forever, daemon=True).start()
    direct = f'http://127.0.0.1:{origin.server_port}'
    proxy_url = f'http://127.0.0.1:{proxy.server_port}'
    config = {'PROXY_URL': proxy_url}
    try:
        assert success(run(direct + '/ok'))['accept'] == 'application/json'
        assert success(run(direct + '/ok', {'PROXY_URL': ''}))['path'] == '/ok'
        assert success(run(direct + '/redirect'))['path'] == '/ok'
        assert 'status 503' in run(direct + '/fail').stderr
        assert 'cURL error 28' in run(direct + '/slow', timeout=1).stderr
        # This hostname cannot resolve: successful access proves the proxy is used.
        remote = 'http://osiris-test.invalid/ok'
        assert success(run(remote, config, env={'no_proxy': '*'}))['path'] == '/ok'
        assert success(run(remote, env={'http_proxy': proxy_url}))['path'] == '/ok'
        assert success(run(remote, {'PROXY_URL': ''}, env={'http_proxy': proxy_url}))['path'] == '/ok'
        Proxy.require_auth = True
        assert run(remote, config).returncode == 1
        config.update(PROXY_USERNAME='test-user', PROXY_PASSWORD='test-password')
        response = success(run(remote, config))
        assert response['proxy_auth'] is None, 'Credentials reached the origin'
        https = f'https://localhost:{secure.server_port}/ok'
        assert success(run(https, config, ca=cert))['path'] == '/ok'
        assert any(item.startswith('CONNECT localhost:') for item in seen)
        failure = run(https, config)
        assert failure.returncode == 1 and 'cURL error 60' in failure.stderr
        assert 'test-password' not in failure.stderr
        print('PASS: direct access, redirects, HTTP errors, timeout, environment proxy,')
        print('      explicit proxy, authentication, HTTPS CONNECT and TLS verification.')
    finally:
        for server in [origin, secure, proxy]:
            server.shutdown()
            server.server_close()
