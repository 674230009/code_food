<?php
/**
 * Food Model
 * ตรง Class Diagram: Food(id, nameTh, category, recipeList[])
 * Methods: init(name, category), display(), addRecipe(recipe)
 */
class Food {
    private PDO $conn;
    private string $table = 'foods';

    public int $id = 0;
    public string $nameTh = '';
    public string $category = '';
    public array $recipeList = [];

    public function __construct(PDO $db) {
        $this->conn = $db;
    }

    // ---- Class Diagram Methods ----

    /** init(name, category) — กำหนดค่าเริ่มต้น */
    public function init(string $name, string $category): void {
        $this->nameTh   = trim($name);
        $this->category = trim($category);
    }

    /**
     * display() — คืน Food พร้อม recipeList
     * ต้องกำหนด $this->id ก่อนเรียก
     */
    public function display(): ?array {
        $sql = "SELECT f.id, f.nameTh, f.category, f.created_at, f.updated_at,
                       r.id AS recipe_id, r.recipeName, r.quantity, r.unitName
                FROM {$this->table} f
                LEFT JOIN recipes r ON f.id = r.food_id
                WHERE f.id = :id
                ORDER BY r.id";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':id' => $this->id]);
        $rows = $stmt->fetchAll();

        if (empty($rows)) {
            return null;
        }

        $food = [
            'id'         => (int)$rows[0]['id'],
            'nameTh'     => $rows[0]['nameTh'],
            'category'   => $rows[0]['category'],
            'created_at' => $rows[0]['created_at'],
            'updated_at' => $rows[0]['updated_at'],
            'recipeList' => [],
        ];

        foreach ($rows as $row) {
            if ($row['recipe_id'] !== null) {
                $food['recipeList'][] = [
                    'id'         => (int)$row['recipe_id'],
                    'recipeName' => $row['recipeName'],
                    'quantity'   => (float)$row['quantity'],
                    'unitName'   => $row['unitName'],
                ];
            }
        }

        return $food;
    }

    /**
     * addRecipe(recipe) — เพิ่ม Recipe เข้าสู่ Food นี้
     * @param array $recipe ['recipeName', 'quantity', 'unitName']
     */
    public function addRecipe(array $recipe): int {
        $sql = "INSERT INTO recipes (food_id, recipeName, quantity, unitName)
                VALUES (:food_id, :recipeName, :quantity, :unitName)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([
            ':food_id'    => $this->id,
            ':recipeName' => trim($recipe['recipeName']),
            ':quantity'   => (float)$recipe['quantity'],
            ':unitName'   => trim($recipe['unitName']),
        ]);
        return (int)$this->conn->lastInsertId();
    }

    // ---- CRUD Methods ----

    /** ดึง Food ทั้งหมด */
    public function getAll(): array {
        $stmt = $this->conn->prepare(
            "SELECT id, nameTh, category, created_at, updated_at
             FROM {$this->table}
             ORDER BY id"
        );
        $stmt->execute();
        return $stmt->fetchAll();
    }

    /** สร้าง Food ใหม่ คืน lastInsertId */
    public function create(): int {
        $this->validate();
        $sql  = "INSERT INTO {$this->table} (nameTh, category) VALUES (:nameTh, :category)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nameTh' => $this->nameTh, ':category' => $this->category]);
        $this->id = (int)$this->conn->lastInsertId();
        return $this->id;
    }

    /** อัปเดต Food คืน true/false */
    public function update(): bool {
        $this->validate();
        $sql  = "UPDATE {$this->table} SET nameTh = :nameTh, category = :category WHERE id = :id";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([':nameTh' => $this->nameTh, ':category' => $this->category, ':id' => $this->id]);
        return $stmt->rowCount() > 0;
    }

    /** ลบ Food (Cascade ลบ recipes ด้วย) */
    public function delete(): bool {
        $stmt = $this->conn->prepare("DELETE FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        return $stmt->rowCount() > 0;
    }

    /** ตรวจสอบว่า id นี้มีอยู่จริง */
    public function exists(): bool {
        $stmt = $this->conn->prepare("SELECT id FROM {$this->table} WHERE id = :id");
        $stmt->execute([':id' => $this->id]);
        return (bool)$stmt->fetch();
    }

    /** Validate fields ก่อน write */
    private function validate(): void {
        if ($this->nameTh === '') {
            throw new InvalidArgumentException('nameTh is required');
        }
        if ($this->category === '') {
            throw new InvalidArgumentException('category is required');
        }
    }
}
