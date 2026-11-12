<?php

/** HTTP GET requests for the country import and journal creation. */
class HttpClient
{
    /**
     * @throws RuntimeException on connection errors or non-2xx responses.
     * Error messages intentionally omit URLs, response bodies and credentials.
     */
    public static function get(string $url, array $headers = [], int $timeout = 10): string
    {
        if ($timeout < 1) {
            throw new InvalidArgumentException('HTTP timeout must be positive.');
        }
        if (!extension_loaded('curl')) {
            throw new RuntimeException('The PHP cURL extension is required.');
        }
        $curl = curl_init();
        if ($curl === false) {
            throw new RuntimeException('Could not initialize HTTP client.');
        }
        try {
            $options = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => $headers,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_MAXREDIRS => 3,
                CURLOPT_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_REDIR_PROTOCOLS => CURLPROTO_HTTP | CURLPROTO_HTTPS,
                CURLOPT_CONNECTTIMEOUT => min(5, $timeout),
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_SSL_VERIFYHOST => 2,
            ];
            // Respect a custom CA bundle configured for PHP/cURL (e.g. TLS inspection).
            $caFile = ini_get('curl.cainfo');
            if ($caFile !== false && $caFile !== '') {
                $options[CURLOPT_CAINFO] = $caFile;
            }
            $proxy = defined('PROXY_URL') ? PROXY_URL : null;
            if ($proxy !== null && $proxy !== '') {
                $options[CURLOPT_PROXY] = $proxy;
                // An explicit CONFIG.php proxy takes precedence over NO_PROXY.
                $options[CURLOPT_NOPROXY] = '';
                if (defined('PROXY_USERNAME') && PROXY_USERNAME !== '') {
                    $options[CURLOPT_PROXYUSERNAME] = PROXY_USERNAME;
                    $options[CURLOPT_PROXYPASSWORD] = defined('PROXY_PASSWORD') ? PROXY_PASSWORD : '';
                    $options[CURLOPT_PROXYAUTH] = CURLAUTH_ANY;
                }
            }
            // Without an explicit proxy, libcurl's environment settings still apply.
            curl_setopt_array($curl, $options);
            $body = curl_exec($curl);
            if ($body === false) {
                throw new RuntimeException('HTTP request failed (cURL error ' . curl_errno($curl) . ').');
            }
            $status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            if ($status < 200 || $status >= 300) {
                throw new RuntimeException('HTTP request failed (status ' . $status . ').');
            }
            return $body;
        } finally {
            curl_close($curl);
        }
    }
}
