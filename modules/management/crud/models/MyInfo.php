<?php
require "../../../system/includes/Model.php";

class MyInfo extends Model {
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

    public function getAll($offset = 0, $limit = 10) {
        $sql = "SELECT * FROM {$this->table} ORDER BY no DESC LIMIT ? OFFSET ?";
        return $this->db->query($sql, [$limit, $offset])->fetchAll();
    }

    public function getTotal() {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        return $this->db->query($sql)->fetch()['total'];
    }

    public function getById($id) {
        $sql = "SELECT * FROM {$this->table} WHERE no = ?";
        return $this->db->query($sql, [$id])->fetch();
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
            $this->db->query($sql, [
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
            $this->db->query($sql, [
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
            $this->db->query($sql, [$id]);
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