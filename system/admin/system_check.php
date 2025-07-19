<?php
session_start();

// 관리자 권한 확인
if (!isset($_SESSION['id']) || $_SESSION['id'] !== 'admin') {
    http_response_code(403);
    die('Access denied. Administrator privileges required.');
}

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/Database.php';

$pageTitle = "System Check";
$additional_css = '<style>
    .status-good { color: #22c55e; }
    .status-warning { color: #f59e0b; }
    .status-error { color: #ef4444; }
    .info-table { border-collapse: collapse; width: 100%; margin-top: 1rem; }
    .info-table th, .info-table td { padding: 0.5rem; text-align: left; border-bottom: 1px solid #e5e7eb; }
    .info-table th { background-color: #f9fafb; }
</style>';

include __DIR__ . '/../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="space-y-2">
            <h1 class="text-3xl font-bold">System Check</h1>
            <p class="text-muted-foreground">시스템 상태 및 설정 확인</p>
        </div>

        <?php
        $checks = [];
        
        // 1. 데이터베이스 연결 확인
        try {
            $db = Database::getInstance();
            $checks[] = [
                'name' => 'Database Connection',
                'status' => $db->isConnected() ? 'good' : 'error',
                'message' => $db->isConnected() ? 'Connected successfully' : 'Connection failed'
            ];
        } catch (Exception $e) {
            $checks[] = [
                'name' => 'Database Connection',
                'status' => 'error',
                'message' => 'Error: ' . $e->getMessage()
            ];
        }

        // 2. 필수 디렉토리 확인
        $required_dirs = [
            'system/includes',
            'system/admin',
            'system/auth',
            'modules/learning',
            'modules/management',
            'modules/tools',
            'resources/css',
            'resources/uploads',
            'config/logs'
        ];

        foreach ($required_dirs as $dir) {
            $full_path = __DIR__ . '/../../' . $dir;
            $checks[] = [
                'name' => "Directory: $dir",
                'status' => is_dir($full_path) ? 'good' : 'error',
                'message' => is_dir($full_path) ? 'Exists' : 'Missing'
            ];
        }

        // 3. 필수 파일 확인
        $required_files = [
            'system/includes/config.php',
            'system/includes/Database.php',
            'system/auth/login_check.php',
            'index.php'
        ];

        foreach ($required_files as $file) {
            $full_path = __DIR__ . '/../../' . $file;
            $checks[] = [
                'name' => "File: $file",
                'status' => file_exists($full_path) ? 'good' : 'error',
                'message' => file_exists($full_path) ? 'Exists' : 'Missing'
            ];
        }

        // 4. PHP 설정 확인
        $checks[] = [
            'name' => 'PHP Version',
            'status' => version_compare(PHP_VERSION, '8.0.0', '>=') ? 'good' : 'warning',
            'message' => PHP_VERSION
        ];

        $checks[] = [
            'name' => 'Session Support',
            'status' => extension_loaded('session') ? 'good' : 'error',
            'message' => extension_loaded('session') ? 'Enabled' : 'Disabled'
        ];

        $checks[] = [
            'name' => 'PDO MySQL',
            'status' => extension_loaded('pdo_mysql') ? 'good' : 'error',
            'message' => extension_loaded('pdo_mysql') ? 'Enabled' : 'Disabled'
        ];

        // 5. 권한 확인
        $upload_dir = __DIR__ . '/../../resources/uploads';
        $logs_dir = __DIR__ . '/../../config/logs';
        
        $checks[] = [
            'name' => 'Upload Directory Writable',
            'status' => is_writable($upload_dir) ? 'good' : 'warning',
            'message' => is_writable($upload_dir) ? 'Writable' : 'Not writable'
        ];

        $checks[] = [
            'name' => 'Logs Directory Writable',
            'status' => is_writable($logs_dir) ? 'good' : 'warning',
            'message' => is_writable($logs_dir) ? 'Writable' : 'Not writable'
        ];

        // 6. API 키 확인
        $checks[] = [
            'name' => 'News API Key',
            'status' => !empty(NEWS_API_KEY) ? 'good' : 'warning',
            'message' => !empty(NEWS_API_KEY) ? 'Configured' : 'Not configured'
        ];
        ?>

        <div class="bg-card text-card-foreground rounded-lg border p-6">
            <h2 class="text-xl font-semibold mb-4">System Status</h2>
            <div class="space-y-3">
                <?php foreach ($checks as $check): ?>
                    <div class="flex items-center justify-between p-3 bg-muted/50 rounded-lg">
                        <span class="font-medium"><?php echo htmlspecialchars($check['name']); ?></span>
                        <span class="status-<?php echo $check['status']; ?> font-semibold">
                            <?php echo htmlspecialchars($check['message']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <?php if (IS_LOCAL): ?>
        <div class="bg-card text-card-foreground rounded-lg border p-6">
            <h2 class="text-xl font-semibold mb-4">System Information</h2>
            <table class="info-table">
                <tr><th>Environment</th><td><?php echo IS_LOCAL ? 'Local Development' : 'Production'; ?></td></tr>
                <tr><th>PHP Version</th><td><?php echo PHP_VERSION; ?></td></tr>
                <tr><th>Server Software</th><td><?php echo $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown'; ?></td></tr>
                <tr><th>Document Root</th><td><?php echo $_SERVER['DOCUMENT_ROOT'] ?? 'Unknown'; ?></td></tr>
                <tr><th>Current User</th><td><?php echo get_current_user(); ?></td></tr>
                <tr><th>Memory Limit</th><td><?php echo ini_get('memory_limit'); ?></td></tr>
                <tr><th>Upload Max Size</th><td><?php echo ini_get('upload_max_filesize'); ?></td></tr>
                <tr><th>Post Max Size</th><td><?php echo ini_get('post_max_size'); ?></td></tr>
                <tr><th>Max Execution Time</th><td><?php echo ini_get('max_execution_time'); ?> seconds</td></tr>
            </table>
        </div>

        <div class="bg-card text-card-foreground rounded-lg border p-6">
            <h2 class="text-xl font-semibold mb-4">Session Information</h2>
            <table class="info-table">
                <tr><th>Session ID</th><td><?php echo session_id(); ?></td></tr>
                <tr><th>Session Name</th><td><?php echo session_name(); ?></td></tr>
                <tr><th>User ID</th><td><?php echo $_SESSION['id'] ?? 'Not set'; ?></td></tr>
                <tr><th>Session Variables</th><td><?php echo count($_SESSION); ?></td></tr>
            </table>
        </div>
        <?php endif; ?>

        <div class="flex gap-4">
            <a href="<?php echo $nav->getHomeUrl(); ?>" class="inline-flex items-center gap-2 px-4 py-2 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="m12 19-7-7 7-7"/>
                    <path d="M19 12H5"/>
                </svg>
                Back to Home
            </a>
            <button onclick="window.location.reload()" class="inline-flex items-center gap-2 px-4 py-2 bg-secondary text-secondary-foreground rounded-lg hover:bg-secondary/90 transition-colors">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12a9 9 0 1 1-2.7-6.4"/>
                    <path d="M21 6v6h-6"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?> 