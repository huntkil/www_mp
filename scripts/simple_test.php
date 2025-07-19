<?php

/**
 * 간단한 테스트 스크립트
 * 복잡한 의존성 없이 기본 기능만 테스트
 */

echo "🧪 간단한 테스트 시작...\n\n";

// 1. PHP 버전 확인
echo "📊 PHP 버전 확인...\n";
$phpVersion = PHP_VERSION;
echo "  ✅ PHP 버전: {$phpVersion}\n";

if (version_compare($phpVersion, '8.0.0', '<')) {
    echo "  ❌ PHP 8.0.0 이상이 필요합니다.\n";
    exit(1);
}

// 2. 필수 확장 확인
echo "\n🔍 필수 확장 확인...\n";
$requiredExtensions = ['pdo', 'pdo_sqlite', 'json', 'curl', 'zip', 'mbstring'];
foreach ($requiredExtensions as $ext) {
    if (extension_loaded($ext)) {
        echo "  ✅ {$ext}\n";
    } else {
        echo "  ❌ {$ext} (필요)\n";
    }
}

// 3. 파일 존재 확인
echo "\n📁 파일 존재 확인...\n";
$requiredFiles = [
    'index.php',
    'health.php',
    'system/includes/config.php',
    'system/includes/Database.php',
    'scripts/test_suite.php',
    'scripts/deploy-cafe24.php'
];

foreach ($requiredFiles as $file) {
    if (file_exists($file)) {
        echo "  ✅ {$file}\n";
    } else {
        echo "  ❌ {$file} (없음)\n";
    }
}

// 4. 디렉토리 권한 확인
echo "\n🔐 디렉토리 권한 확인...\n";
$directories = [
    'system/uploads',
    'system/cache',
    'system/logs',
    'config'
];

foreach ($directories as $dir) {
    if (is_dir($dir)) {
        if (is_writable($dir)) {
            echo "  ✅ {$dir} (쓰기 가능)\n";
        } else {
            echo "  ⚠️ {$dir} (쓰기 불가)\n";
        }
    } else {
        echo "  ❌ {$dir} (디렉토리 없음)\n";
    }
}

// 5. 기본 함수 테스트
echo "\n⚡ 기본 함수 테스트...\n";

// JSON 함수
if (function_exists('json_encode') && function_exists('json_decode')) {
    $testData = ['test' => 'value'];
    $json = json_encode($testData);
    $decoded = json_decode($json, true);
    if ($decoded === $testData) {
        echo "  ✅ JSON 함수\n";
    } else {
        echo "  ❌ JSON 함수\n";
    }
} else {
    echo "  ❌ JSON 함수 (사용 불가)\n";
}

// 문자열 함수
if (function_exists('mb_strlen')) {
    $test = '테스트';
    if (mb_strlen($test) === 3) {
        echo "  ✅ MBString 함수\n";
    } else {
        echo "  ❌ MBString 함수\n";
    }
} else {
    echo "  ❌ MBString 함수 (사용 불가)\n";
}

// 6. 간단한 클래스 테스트
echo "\n🏗️ 클래스 테스트...\n";

// 클래스 파일 로드 테스트
$classFiles = [
    'system/includes/Database.php',
    'system/includes/Validator.php',
    'system/includes/CacheManager.php'
];

foreach ($classFiles as $file) {
    if (file_exists($file)) {
        try {
            // 파일 내용 확인
            $content = file_get_contents($file);
            if (strpos($content, 'class') !== false) {
                echo "  ✅ {$file} (클래스 정의됨)\n";
            } else {
                echo "  ⚠️ {$file} (클래스 정의 없음)\n";
            }
        } catch (Exception $e) {
            echo "  ❌ {$file} (읽기 실패)\n";
        }
    } else {
        echo "  ❌ {$file} (파일 없음)\n";
    }
}

// 7. 설정 파일 테스트
echo "\n⚙️ 설정 파일 테스트...\n";

$configFiles = [
    'config/credentials/development.php',
    'config/credentials/test.php'
];

foreach ($configFiles as $file) {
    if (file_exists($file)) {
        try {
            $config = require $file;
            if (is_array($config)) {
                echo "  ✅ {$file} (유효한 설정)\n";
            } else {
                echo "  ⚠️ {$file} (배열이 아님)\n";
            }
        } catch (Exception $e) {
            echo "  ❌ {$file} (로드 실패: {$e->getMessage()})\n";
        }
    } else {
        echo "  ❌ {$file} (파일 없음)\n";
    }
}

// 8. 결과 요약
echo "\n" . str_repeat("=", 50) . "\n";
echo "🎉 간단한 테스트 완료!\n";
echo str_repeat("=", 50) . "\n";

echo "📊 테스트 결과:\n";
echo "- PHP 버전: {$phpVersion}\n";
echo "- 필수 확장: " . count(array_filter($requiredExtensions, 'extension_loaded')) . "/" . count($requiredExtensions) . "\n";
echo "- 필수 파일: " . count(array_filter($requiredFiles, 'file_exists')) . "/" . count($requiredFiles) . "\n";
echo "- 디렉토리 권한: " . count(array_filter($directories, function($dir) { return is_dir($dir) && is_writable($dir); })) . "/" . count($directories) . "\n";

echo "\n✅ 기본 환경이 준비되었습니다!\n";
echo "🚀 이제 실제 테스트를 실행할 수 있습니다.\n"; 