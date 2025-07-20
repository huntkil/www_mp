<?php

class MyInfo {
    protected $table = 'myinfo';
    protected $primaryKey = 'no';
    protected $fillable = [
        'name',
        'email',
        'phone',
        'age',
        'birthday',
        'height',
        'weight'
    ];
    protected $db;
    
    public function __construct($db) {
        $this->db = $db;
    }

    public function getAll($offset = 0, $limit = 10) {
        try {
            $sql = "SELECT * FROM {$this->table} ORDER BY no DESC LIMIT ? OFFSET ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$limit, $offset]);
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("MyInfo getAll error: " . $e->getMessage());
            return [];
        }
    }

    public function getTotal() {
        try {
            $sql = "SELECT COUNT(*) as total FROM {$this->table}";
            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            return $stmt->fetch()['total'];
        } catch (Exception $e) {
            error_log("MyInfo getTotal error: " . $e->getMessage());
            return 0;
        }
    }

    public function getById($id) {
        try {
            $sql = "SELECT * FROM {$this->table} WHERE no = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch();
        } catch (Exception $e) {
            error_log("MyInfo getById error: " . $e->getMessage());
            return null;
        }
    }

    public function create($data) {
        try {
            $data = $this->validate($data);
            if (isset($data['errors'])) {
                return [
                    'success' => false,
                    'message' => implode(', ', $data['errors'])
                ];
            }

            $sql = "INSERT INTO {$this->table} (name, email, phone, age, birthday, height, weight) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['age'],
                $data['birthday'],
                $data['height'],
                $data['weight']
            ]);

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function update($id, $data) {
        try {
            $data = $this->validate($data);
            if (isset($data['errors'])) {
                return [
                    'success' => false,
                    'message' => implode(', ', $data['errors'])
                ];
            }

            $sql = "UPDATE {$this->table} SET name = ?, email = ?, phone = ?, age = ?, birthday = ?, height = ?, weight = ? WHERE no = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                $data['name'],
                $data['email'],
                $data['phone'],
                $data['age'],
                $data['birthday'],
                $data['height'],
                $data['weight'],
                $id
            ]);

            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function delete($id) {
        try {
            $sql = "DELETE FROM {$this->table} WHERE no = ?";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return ['success' => true];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    public function validate($data) {
        $errors = [];

        if (empty($data['name'])) {
            $errors[] = "Name is required";
        }

        if (empty($data['email'])) {
            $errors[] = "Email is required";
        } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = "Email must be a valid email address";
        }

        if (empty($data['phone'])) {
            $errors[] = "Phone is required";
        }

        if (!empty($data['age']) && !is_numeric($data['age'])) {
            $errors[] = "Age must be a number";
        }

        if (!empty($data['height']) && !is_numeric($data['height'])) {
            $errors[] = "Height must be a number";
        }

        if (!empty($data['weight']) && !is_numeric($data['weight'])) {
            $errors[] = "Weight must be a number";
        }

        if (!empty($errors)) {
            $data['errors'] = $errors;
        }

        return $data;
    }
} 