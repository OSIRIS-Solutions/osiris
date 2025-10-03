<?php
/**
 * JSON response helper
 */
class JSON
{
    public static function ok(array $data = [], int $code = 200): void {
        self::respond(array_merge(['status' => 'ok'], $data), $code);
    }

    public static function error(string $message, int $code = 400): void {
        self::respond([
            'status' => 'error',
            'error'  => $message
        ], $code);
    }

    private static function respond(array $data, int $code): void {
        http_response_code($code);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}
?>
