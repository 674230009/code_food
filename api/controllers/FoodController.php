<?php
require_once __DIR__ . '/../models/Food.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * FoodController
 * รับผิดชอบ routing + validation เท่านั้น — business logic อยู่ใน Food model
 */
class FoodController {
    private Food $food;

    public function __construct(PDO $db) {
        $this->food = new Food($db);
    }

    public function handle(string $method, array $query, mixed $body): void {
        try {
            match ($method) {
                'GET'    => $this->handleGet($query),
                'POST'   => $this->handlePost($body),
                'PUT'    => $this->handlePut($body),
                'DELETE' => $this->handleDelete($query),
                default  => Response::methodNotAllowed(),
            };
        } catch (InvalidArgumentException $e) {
            Response::error($e->getMessage(), 422);
        } catch (PDOException $e) {
            // ซ่อน DB detail ใน production
            Response::serverError('Database error');
        }
    }

    // ---- GET ----

    private function handleGet(array $query): void {
        if (isset($query['id'])) {
            $this->getOne((int)$query['id']);
        } else {
            $this->getAll();
        }
    }

    private function getAll(): void {
        $foods = $this->food->getAll();
        // cast types
        $foods = array_map(fn($f) => $this->castFood($f), $foods);
        Response::success($foods);
    }

    private function getOne(int $id): void {
        if ($id <= 0) {
            Response::error('Invalid id');
            return;
        }
        $this->food->id = $id;
        $result = $this->food->display();
        if ($result === null) {
            Response::notFound("Food id={$id} not found");
            return;
        }
        Response::success($result);
    }

    // ---- POST ----

    private function handlePost(mixed $body): void {
        // [จุดที่แก้ไข/เพิ่มใหม่]: กรอง $body ให้เป็น Array ปลอดภัย ป้องกัน TypeError ใน PHP 8
        $bodyData = is_array($body) ? $body : [];
        $nameTh   = trim($bodyData['nameTh']   ?? '');
        $category = trim($bodyData['category'] ?? '');

        if ($nameTh === '' || $category === '') {
            Response::error('nameTh and category are required', 422);
            return;
        }

        $this->food->init($nameTh, $category);
        $id = $this->food->create();

        Response::created([
            'id'       => $id,
            'nameTh'   => $nameTh,
            'category' => $category,
            'message'  => 'Food created successfully',
        ]);
    }

    // ---- PUT ----

    private function handlePut(mixed $body): void {
        // [จุดที่แก้ไข/เพิ่มใหม่]: กรอง $body ให้เป็น Array ปลอดภัย ป้องกัน TypeError ใน PHP 8
        $bodyData = is_array($body) ? $body : [];
        $id       = (int)($bodyData['id']       ?? 0);
        $nameTh   = trim($bodyData['nameTh']    ?? '');
        $category = trim($bodyData['category']  ?? '');

        if ($id <= 0) {
            Response::error('id is required', 422);
            return;
        }
        if ($nameTh === '' || $category === '') {
            Response::error('nameTh and category are required', 422);
            return;
        }

        $this->food->id       = $id;
        $this->food->nameTh   = $nameTh;
        $this->food->category = $category;

        if (!$this->food->exists()) {
            Response::notFound("Food id={$id} not found");
            return;
        }

        $updated = $this->food->update();
        if ($updated) {
            Response::success(['id' => $id, 'message' => 'Food updated successfully']);
        } else {
            Response::error('No changes were made', 422);
        }
    }

    // ---- DELETE ----

    private function handleDelete(array $query): void {
        $id = (int)($query['id'] ?? 0);
        if ($id <= 0) {
            Response::error('id is required', 422);
            return;
        }

        $this->food->id = $id;

        if (!$this->food->exists()) {
            Response::notFound("Food id={$id} not found");
            return;
        }

        $deleted = $this->food->delete();
        if ($deleted) {
            Response::success(['id' => $id, 'message' => 'Food deleted successfully']);
        } else {
            Response::serverError('Unable to delete food');
        }
    }

    // ---- Helpers ----

    private function castFood(array $f): array {
        $f['id'] = (int)$f['id'];
        return $f;
    }
}