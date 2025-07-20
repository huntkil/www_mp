<?php
/**
 * Cafe24 수동 배포 스크립트
 * FTP 연결 및 디렉토리 생성, 파일 업로드를 단계별로 처리
 */

// 설정
$ftp_server = getenv('CAFE24_FTP_SERVER') ?: 'your-ftp-server.com';
$ftp_username = getenv('CAFE24_FTP_USERNAME') ?: 'your-username';
$ftp_password = getenv('CAFE24_FTP_PASSWORD') ?: 'your-password';
$remote_dir = '/www/mp/';

echo "🚀 Cafe24 수동 배포 시작...\n";
echo "서버: $ftp_server\n";
echo "사용자: $ftp_username\n";
echo "원격 디렉토리: $remote_dir\n\n";

// FTP 연결
echo "📡 FTP 서버에 연결 중...\n";
$conn_id = ftp_connect($ftp_server);

if (!$conn_id) {
    die("❌ FTP 서버 연결 실패\n");
}

echo "✅ FTP 서버 연결 성공\n";

// 로그인
echo "🔐 로그인 중...\n";
$login_result = ftp_login($conn_id, $ftp_username, $ftp_password);

if (!$login_result) {
    die("❌ FTP 로그인 실패\n");
}

echo "✅ FTP 로그인 성공\n";

// 패시브 모드 설정
ftp_pasv($conn_id, true);

// 원격 디렉토리 확인 및 생성
echo "📁 원격 디렉토리 확인 중...\n";
$current_dir = ftp_pwd($conn_id);
echo "현재 디렉토리: $current_dir\n";

// 디렉토리 존재 확인
$dir_exists = false;
$contents = ftp_nlist($conn_id, '/www/');
if ($contents !== false) {
    foreach ($contents as $item) {
        if (basename($item) === 'mp') {
            $dir_exists = true;
            break;
        }
    }
}

if (!$dir_exists) {
    echo "📁 /www/mp/ 디렉토리 생성 중...\n";
    if (ftp_mkdir($conn_id, '/www/mp/')) {
        echo "✅ /www/mp/ 디렉토리 생성 성공\n";
    } else {
        echo "❌ /www/mp/ 디렉토리 생성 실패\n";
        echo "Cafe24 관리자 패널에서 수동으로 /www/mp/ 디렉토리를 생성해주세요.\n";
        ftp_close($conn_id);
        exit(1);
    }
} else {
    echo "✅ /www/mp/ 디렉토리가 이미 존재합니다\n";
}

// 디렉토리 이동
echo "📂 작업 디렉토리로 이동 중...\n";
if (ftp_chdir($conn_id, $remote_dir)) {
    echo "✅ 작업 디렉토리 이동 성공\n";
} else {
    echo "❌ 작업 디렉토리 이동 실패\n";
    ftp_close($conn_id);
    exit(1);
}

// 업로드할 파일 목록
$upload_files = [
    'index.php',
    'health.php',
    'favicon.ico',
    'LICENSE',
    'lib/utils.js',
    '.php-cs-fixer.php',
    'components.json'
];

$upload_dirs = [
    'system',
    'modules',
    'resources',
    'api',
    'scripts',
    'config',
    'docs'
];

// 파일 업로드
echo "\n📤 파일 업로드 시작...\n";
foreach ($upload_files as $file) {
    if (file_exists($file)) {
        echo "업로드 중: $file\n";
        if (ftp_put($conn_id, basename($file), $file, FTP_BINARY)) {
            echo "✅ $file 업로드 성공\n";
        } else {
            echo "❌ $file 업로드 실패\n";
        }
    } else {
        echo "⚠️ $file 파일이 존재하지 않습니다\n";
    }
}

// 디렉토리 업로드 (재귀적)
function uploadDirectory($ftp_conn, $local_dir, $remote_dir) {
    if (!is_dir($local_dir)) {
        return false;
    }
    
    // 원격 디렉토리 생성
    if (!ftp_nlist($ftp_conn, $remote_dir)) {
        ftp_mkdir($ftp_conn, $remote_dir);
    }
    
    $files = scandir($local_dir);
    foreach ($files as $file) {
        if ($file === '.' || $file === '..') continue;
        
        $local_path = $local_dir . '/' . $file;
        $remote_path = $remote_dir . '/' . $file;
        
        if (is_dir($local_path)) {
            uploadDirectory($ftp_conn, $local_path, $remote_path);
        } else {
            echo "업로드 중: $local_path\n";
            if (ftp_put($ftp_conn, $remote_path, $local_path, FTP_BINARY)) {
                echo "✅ $file 업로드 성공\n";
            } else {
                echo "❌ $file 업로드 실패\n";
            }
        }
    }
}

// 디렉토리 업로드
echo "\n📁 디렉토리 업로드 시작...\n";
foreach ($upload_dirs as $dir) {
    if (is_dir($dir)) {
        echo "디렉토리 업로드 중: $dir\n";
        uploadDirectory($conn_id, $dir, $dir);
    } else {
        echo "⚠️ $dir 디렉토리가 존재하지 않습니다\n";
    }
}

// 연결 종료
ftp_close($conn_id);

echo "\n🎉 배포 완료!\n";
echo "🌐 사이트 URL: http://gukho.net/mp/\n";
echo "📊 헬스 체크: http://gukho.net/mp/health.php\n";
?> 