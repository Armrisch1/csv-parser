<?php

namespace src\services;

class ResponseService {
    public static function setApiHeaders(int $statusCode = 200): void {
        header('Content-Type: application/json');
        http_response_code($statusCode);
    }

    public static function sendErrorResponse(int $code, string $message): void {
        header('Content-Type: application/json');
        http_response_code($code);
        echo json_encode(["error" => $message]);
        exit;
    }
}