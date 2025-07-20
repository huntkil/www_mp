<?php
// Load production config if exists, otherwise development
$config_prod = __DIR__ . '/../../../system/includes/config_production.php';
$config_dev = __DIR__ . '/../../../system/includes/config.php';

if (file_exists($config_prod)) {
    require_once $config_prod;
} else {
    require_once $config_dev;
}

require_once __DIR__ . '/../models/MyInfo.php';

class MyInfoController {
    private $model;
    private $db;

    public function __construct() {
        try {
            // Direct PDO connection to avoid ErrorHandler issues
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->db = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $this->model = new MyInfo($this->db);
        } catch (Exception $e) {
            error_log("MyInfoController constructor error: " . $e->getMessage());
            // Return error instead of throwing
            return;
        }
    }

    public function index() {
        try {
            if (!$this->model) {
                return [
                    'success' => false,
                    'message' => 'Model not initialized',
                    'data' => [],
                    'current_page' => 1,
                    'total_pages' => 1
                ];
            }
            
            $page = $_GET['page'] ?? 1;
            $perPage = 10;
            $offset = ($page - 1) * $perPage;

            $data = $this->model->getAll($offset, $perPage);
            $total = $this->model->getTotal();
            $totalPages = ceil($total / $perPage);

            return [
                'success' => true,
                'data' => $data,
                'current_page' => (int)$page,
                'total_pages' => $totalPages
            ];
        } catch (Exception $e) {
            error_log("MyInfoController index error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to load data: ' . $e->getMessage(),
                'data' => [],
                'current_page' => 1,
                'total_pages' => 1
            ];
        }
    }

    public function create($data) {
        // Validate required fields
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        }
        if (empty($data['phone'])) {
            $errors[] = 'Phone is required';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $data
            ];
        }

        $result = $this->model->create($data);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Record created successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to create record',
                'data' => $data
            ];
        }
    }

    public function edit($id) {
        if (!$id) {
            return [
                'success' => false,
                'message' => 'Invalid ID'
            ];
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => $_POST['name'] ?? '',
                'age' => $_POST['age'] ?? null,
                'birthday' => $_POST['birthday'] ?? null,
                'height' => $_POST['height'] ?? null,
                'weight' => $_POST['weight'] ?? null
            ];

            $result = $this->model->update($id, $data);

            if ($result['success']) {
                $_SESSION['flash_message'] = 'Record updated successfully';
                $_SESSION['flash_type'] = 'success';
                header('Location: data_list.php');
                exit;
            } else {
                $_SESSION['flash_message'] = $result['message'];
                $_SESSION['flash_type'] = 'error';
            }
        }

        $data = $this->model->getById($id);
        if (!$data) {
            return [
                'success' => false,
                'message' => 'Record not found'
            ];
        }

        return [
            'success' => true,
            'data' => $data
        ];
    }

    public function delete($id) {
        if (!$id) {
            $_SESSION['flash_message'] = 'Invalid ID';
            $_SESSION['flash_type'] = 'error';
            header('Location: data_list.php');
            exit;
        }

        $result = $this->model->delete($id);

        if ($result['success']) {
            $_SESSION['flash_message'] = 'Record deleted successfully';
            $_SESSION['flash_type'] = 'success';
        } else {
            $_SESSION['flash_message'] = $result['message'];
            $_SESSION['flash_type'] = 'error';
        }

        header('Location: data_list.php');
        exit;
    }

    public function show($id) {
        return $this->model->getById($id);
    }

    public function update($id, $data) {
        // Validate required fields
        $errors = [];
        if (empty($data['name'])) {
            $errors[] = 'Name is required';
        }
        if (empty($data['email'])) {
            $errors[] = 'Email is required';
        }
        if (empty($data['phone'])) {
            $errors[] = 'Phone is required';
        }

        if (!empty($errors)) {
            return [
                'success' => false,
                'errors' => $errors,
                'data' => $data
            ];
        }

        $result = $this->model->update($id, $data);

        if ($result['success']) {
            return [
                'success' => true,
                'message' => 'Record updated successfully'
            ];
        } else {
            return [
                'success' => false,
                'message' => $result['message'] ?? 'Failed to update record',
                'data' => $data
            ];
        }
    }

    public function search($query) {
        $searchFields = ['name', 'email', 'phone'];
        return $this->model->search($query, $searchFields);
    }
} 