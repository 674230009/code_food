<?php
/**
 * Endpoint: /api/endpoints/recipes.php
 * ทำหน้าที่เป็น entry point + CORS — ไม่มี business logic ที่นี่
 */
declare(strict_types=1);

// ---- CORS Headers ----
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json; charset=UTF-8');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Max-Age: 3600');

// OPTIONS preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/RecipeController.php';

try {
    $db         = (new Database())->getConnection();
    $controller = new RecipeController($db);
    $body       = json_decode(file_get_contents('php://input'), true) ?? [];
    $controller->handle($_SERVER['REQUEST_METHOD'], $_GET, $body);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Unexpected server error']);
}
