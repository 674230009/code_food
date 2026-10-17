<?php
require_once __DIR__ . '/../models/Recipe.php';
require_once __DIR__ . '/../helpers/Response.php';

/**
 * RecipeController
 * รับผิดชอบ routing + validation เท่านั้น — business logic อยู่ใน Recipe model
 */
class RecipeController {
    private Recipe $recipe;

    public function __construct(PDO $db) {
        $this->recipe = new Recipe($db);
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
            Response::serverError('Database error');
        }
    }

    // ---- GET ----

    private function handleGet(array $query): void {
        if (isset($query['food_id'])) {
            $this->getByFoodId((int)$query['food_id']);
        } elseif (isset($query['id'])) {
            $this->getOne((int)$query['id']);
        } else {
            Response::error('Provide ?id or ?food_id', 422);
        }
    }

    private function getByFoodId(int $foodId): void {
        if ($foodId <= 0) {
            Response::error('Invalid food_id', 422);
            return;
        }
        $this->recipe->food_id = $foodId;
        $rows = $this->recipe->getAllByFoodId();
        Response::success(array_map(fn($r) => $this->castRecipe($r), $rows));
    }

    private function getOne(int $id): void {
        if ($id <= 0) {
            Response::error('Invalid id', 422);
            return;
        }
        $this->recipe->id = $id;
        $row = $this->recipe->getOne();
        if ($row === null) {
            Response::notFound("Recipe id={$id} not found");
            return;
        }
        Response::success($this->castRecipe($row));
    }

    // ---- POST ----

    private function handlePost(mixed $body): void {
        // [จุดที่แก้ไข/เพิ่มใหม่]: กรอง $body ให้เป็น Array ปลอดภัย ป้องกัน TypeError ใน PHP 8
        $bodyData   = is_array($body) ? $body : [];
        $foodId     = (int)($bodyData['food_id']    ?? 0);
        $recipeName = trim($bodyData['recipeName']  ?? '');
        $quantity   = (float)($bodyData['quantity'] ?? 0);
        $unitName   = trim($bodyData['unitName']    ?? '');

        if ($foodId <= 0 || $recipeName === '' || $quantity <= 0 || $unitName === '') {
            Response::error('food_id, recipeName, quantity (>0), and unitName are required', 422);
            return;
        }

        $this->recipe->food_id    = $foodId;
        $this->recipe->recipeName = $recipeName;
        $this->recipe->quantity   = $quantity;
        $this->recipe->unitName   = $unitName;

        // ตรวจว่า food มีอยู่จริง
        if (!$this->recipe->foodExists()) {
            Response::notFound("Food id={$foodId} not found");
            return;
        }

        $id = $this->recipe->create();
        Response::created([
            'id'         => $id,
            'food_id'    => $foodId,
            'recipeName' => $recipeName,
            'quantity'   => $quantity,
            'unitName'   => $unitName,
            'message'    => 'Recipe added successfully',
        ]);
    }

    // ---- PUT ----

    private function handlePut(mixed $body): void {
        // [จุดที่แก้ไข/เพิ่มใหม่]: กรอง $body ให้เป็น Array ปลอดภัย ป้องกัน TypeError ใน PHP 8
        $bodyData   = is_array($body) ? $body : [];
        $id         = (int)($bodyData['id']         ?? 0);
        $recipeName = trim($bodyData['recipeName']  ?? '');
        $quantity   = (float)($bodyData['quantity'] ?? 0);
        $unitName   = trim($bodyData['unitName']    ?? '');

        if ($id <= 0) {
            Response::error('id is required', 422);
            return;
        }
        if ($recipeName === '' || $quantity <= 0 || $unitName === '') {
            Response::error('recipeName, quantity (>0), and unitName are required', 422);
            return;
        }

        $this->recipe->id         = $id;
        $this->recipe->food_id    = 1; // dummy — validate() ต้องการค่า > 0
        $this->recipe->recipeName = $recipeName;
        $this->recipe->quantity   = $quantity;
        $this->recipe->unitName   = $unitName;

        $existing = $this->recipe->getOne();
        if ($existing === null) {
            Response::notFound("Recipe id={$id} not found");
            return;
        }
        // เก็บ food_id เดิม
        $this->recipe->food_id = (int)$existing['food_id'];

        $updated = $this->recipe->update();
        if ($updated) {
            Response::success(['id' => $id, 'message' => 'Recipe updated successfully']);
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

        $this->recipe->id = $id;

        if ($this->recipe->getOne() === null) {
            Response::notFound("Recipe id={$id} not found");
            return;
        }

        $deleted = $this->recipe->delete();
        if ($deleted) {
            Response::success(['id' => $id, 'message' => 'Recipe deleted successfully']);
        } else {
            Response::serverError('Unable to delete recipe');
        }
    }

    // ---- Helpers ----

    private function castRecipe(array $r): array {
        $r['id']       = (int)$r['id'];
        $r['food_id']  = (int)$r['food_id'];
        $r['quantity'] = (float)$r['quantity'];
        return $r;
    }
}