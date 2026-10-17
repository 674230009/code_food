<?php
/**
 * Response Helper
 * จัดการ HTTP response ให้สม่ำเสมอทุก endpoint
 */
class Response {
    /**
     * ส่ง JSON response พร้อม status code
     */
    public static function json(mixed $data, int $statusCode = 200): void {
        http_response_code($statusCode);
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    public static function success(mixed $data, int $statusCode = 200): void {
        self::json(['success' => true, 'data' => $data], $statusCode);
    }

    public static function created(mixed $data): void {
        self::json(['success' => true, 'data' => $data], 201);
    }

    public static function error(string $message, int $statusCode = 400): void {
        self::json(['success' => false, 'error' => $message], $statusCode);
    }

    public static function notFound(string $message = 'Resource not found'): void {
        self::error($message, 404);
    }

    public static function serverError(string $message = 'Internal server error'): void {
        self::error($message, 500);
    }

    public static function methodNotAllowed(): void {
        self::error('Method not allowed', 405);
    }
}
