<?php
/**
 * Cafe24 빠른 배포 스크립트
 * FTP 정보를 직접 입력하여 배포
 */

echo "🚀 Cafe24 빠른 배포 스크립트\n";
echo "========================\n\n";

// FTP 정보 입력 (실제 배포 시에는 환경 변수나 설정 파일에서 가져와야 함)
$ftp_server = 'gukho.net'; // Cafe24 FTP 서버
$ftp_username = 'your_username'; // FTP 사용자명
$ftp_password = 'your_password'; // FTP 비밀번호
$remote_dir = '/www/mp/';

echo "📋 배포 정보:\n";
echo "서버: $ftp_server\n";
echo "사용자: $ftp_username\n";
echo "원격 디렉토리: $remote_dir\n\n";

// 배포 전 확인
echo "⚠️  배포 전 확인사항:\n";
echo "1. FTP 정보가 올바른지 확인하세요\n";
echo "2. 백업이 완료되었는지 확인하세요\n";
echo "3. 테스트가 완료되었는지 확인하세요\n\n";

echo "계속하시겠습니까? (y/N): ";
$handle = fopen("php://stdin", "r");
$line = fgets($handle);
fclose($handle);

if (trim(strtolower($line)) !== 'y') {
    echo "배포가 취소되었습니다.\n";
    exit(0);
}

echo "\n📡 FTP 서버에 연결 중...\n";

// FTP 연결
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

// 원격 디렉토리 확인
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

// 업로드할 핵심 파일들
$core_files = [
    'index.php',
    'health.php',
    'favicon.ico',
    'LICENSE'
];

// 업로드할 핵심 디렉토리들
$core_dirs = [
    'system',
    'modules',
    'resources',
    'api',
    'config'
];

// 파일 업로드
echo "\n📤 핵심 파일 업로드 시작...\n";
foreach ($core_files as $file) {
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

// 디렉토리 업로드 함수
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
echo "\n📁 핵심 디렉토리 업로드 시작...\n";
foreach ($core_dirs as $dir) {
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
echo "\n📋 배포 후 확인사항:\n";
echo "1. 메인 페이지 접속 확인\n";
echo "2. 로그인 기능 테스트\n";
echo "3. 주요 기능 동작 확인\n";
echo "4. 오류 로그 확인\n";
?> 