<?php
require "../../../system/includes/config.php";
require_once __DIR__ . '/../models/MyInfo.php';

class MyInfoController {
    private $model;
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
        $this->model = new MyInfo($this->db);
    }

    public function index() {
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

    public function search($query) {
        $searchFields = ['name', 'email', 'phone'];
        return $this->model->search($query, $searchFields);
    }
} 