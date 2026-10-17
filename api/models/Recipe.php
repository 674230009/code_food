<?php
/**
 * Recipe Model
 * ตรง Class Diagram: Recipe(id, food_id, recipeName, quantity, unitName)
 */
class Recipe {
    private PDO $conn;
    private string $table = 'recipes';

    public int $id = 0;
    public int $food_id = 0;
    public string $recipeName = '';
    public float $quantity = 0.0;
    public string $unitName = '';

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    /** ดึง Recipes ทั้งหมดของ food_id */
    public function getAllByFoodId(): array {
        $stmt = $this->conn->prepare(
            "SELECT id, food_id, recipeName, quantity, unitName, created_at, updated_at
             FROM {$this->table}
             WHERE food_id = :food_id
             ORDER BY id"
        );
        $stmt->execute([':food_id' => $this->food_id]);
        return $stmt->fetchAll();
    }

    /** ดึง Recipe เดี่ยวตาม id คืน array หรือ null */
    public function getOne(): ?array {
        $stmt = $this->conn->prepare(
            "SELECT id, food_id, recipeName, quantity, unitName, created_at, updated_at
             FROM {$this->table}
             WHERE id = :id"
        );
        $stmt->execute([':id' => $this->id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    /** สร้าง Recipe ใหม่ คืน lastInsertId */
    public function create(): int {
        $this->validate();
        $sql = "INSERT INTO {$this->table} (food_id, recipeName, quantity, unitName)
                VALUES (:food_id, :recipeName, :quantity, :unitName)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':food_id'    => $this->food_id,
            ':recipeName' => $this->recipeName,
            ':quantity'   => $this->quantity,
            ':unitName'   => $this->unitName,
        ]);
        $this->id = (int)$this->conn->lastInsertId();
        return $this->id;
    }

    /** อัปเดต Recipe คืน true/false */
    public function update(): bool {
        $this->validate();
        $sql = "UPDATE {$this->table}
                SET recipeName = :recipeName, quantity = :quantity, unitName = :unitName
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':recipeName' => $this->recipeName,
            ':quantity'   => $this->quantity,
            ':unitName'   => $this->unitName,
            ':id'         => $this->id,
        ]);
        return $stmt->rowCount() > 0;
    }

    /** ลบ Recipe */
    public function delete(): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        return $stmt->rowCount() > 0;
    }

    /** ตรวจสอบ food_id ว่ามีอยู่จริงในตาราง foods */
    public function foodExists(): bool {
        $stmt = $this->conn->prepare("SELECT id FROM foods WHERE id = :id");
        $stmt->execute([':id' => $this->food_id]);
        return (bool)$stmt->fetch();
    }

    private function validate(): void {
        if ($this->food_id <= 0) {
            throw new InvalidArgumentException('food_id is required and must be positive');
        }
        if ($this->recipeName === '') {
            throw new InvalidArgumentException('recipeName is required');
        }
        if ($this->quantity <= 0) {
            throw new InvalidArgumentException('quantity must be greater than 0');
        }
        if ($this->unitName === '') {
            throw new InvalidArgumentException('unitName is required');
        }
    }
}
