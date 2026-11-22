<?php

/** Session-bound tokens can be scoped to independent groups of forms. */
function csrf_token(string $scope): string
{
    $key = $scope . '_csrf';
    if (empty($_SESSION[$key])) $_SESSION[$key] = bin2hex(random_bytes(32));
    return $_SESSION[$key];
}

function valid_csrf_token($token, string $scope): bool
{
    return is_string($token) && !empty($_SESSION[$scope . '_csrf']) && hash_equals($_SESSION[$scope . '_csrf'], $token);
}
