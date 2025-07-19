<?php

/**
 * 간단한 코드 품질 검사 스크립트
 * 외부 의존성 없이 기본적인 코드 품질을 확인합니다.
 */

echo "🔍 코드 품질 검사 시작...\n\n";

$issues = [];
$totalFiles = 0;
$checkedFiles = 0;

// 검사할 디렉토리들
$directories = [
    'system',
    'modules',
    'scripts',
    'api'
];

foreach ($directories as $dir) {
    if (!is_dir($dir)) {
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS)
    );
    
    foreach ($iterator as $file) {
        if ($file->getExtension() !== 'php') {
            continue;
        }
        
        $totalFiles++;
        $filePath = $file->getPathname();
        $content = file_get_contents($filePath);
        
        // 기본적인 코드 품질 검사
        $fileIssues = [];
        
        // 1. 문법 오류 확인
        $syntaxCheck = shell_exec("php -l " . escapeshellarg($filePath) . " 2>&1");
        if (strpos($syntaxCheck, 'No syntax errors') === false) {
            $fileIssues[] = "문법 오류: " . trim($syntaxCheck);
        }
        
        // 2. 긴 함수 확인 (50줄 이상)
        $lines = explode("\n", $content);
        $functionLines = 0;
        $inFunction = false;
        
        foreach ($lines as $lineNum => $line) {
            $line = trim($line);
            
            if (preg_match('/^\s*(public|private|protected)?\s*function\s+\w+\s*\(/', $line)) {
                $inFunction = true;
                $functionLines = 0;
            } elseif ($inFunction && preg_match('/^\s*}\s*$/', $line)) {
                if ($functionLines > 50) {
                    $fileIssues[] = "긴 함수 발견 (라인 " . ($lineNum - $functionLines + 1) . "): {$functionLines}줄";
                }
                $inFunction = false;
            } elseif ($inFunction) {
                $functionLines++;
            }
        }
        
        // 3. 하드코딩된 비밀번호 확인 (테스트 파일 제외)
        if (preg_match('/password\s*=\s*[\'"][^\'"]{6,}[\'"]/', $content) && 
            !str_contains($filePath, 'test') && 
            !str_contains($filePath, 'TestCase.php')) {
            $fileIssues[] = "하드코딩된 비밀번호 발견";
        }
        
        // 4. SQL 인젝션 취약점 확인
        if (preg_match('/\$_\w+\[[^\]]+\]\s*\.\s*\$/', $content)) {
            $fileIssues[] = "잠재적 SQL 인젝션 취약점";
        }
        
        // 5. 에러 리포팅 확인 (자체 스크립트는 제외)
        if (strpos($content, 'error_reporting(0)') !== false && !str_contains($filePath, 'code_quality_check.php')) {
            $fileIssues[] = "에러 리포팅이 비활성화됨";
        }
        
        if (!empty($fileIssues)) {
            $issues[$filePath] = $fileIssues;
        }
        
        $checkedFiles++;
    }
}

// 결과 출력
echo "📊 검사 결과:\n";
echo "총 파일 수: {$totalFiles}\n";
echo "검사된 파일 수: {$checkedFiles}\n";
echo "문제가 있는 파일 수: " . count($issues) . "\n\n";

if (empty($issues)) {
    echo "✅ 모든 파일이 기본 품질 기준을 통과했습니다!\n";
    exit(0);
} else {
    echo "⚠️ 발견된 문제들:\n\n";
    
    foreach ($issues as $file => $fileIssues) {
        echo "📁 {$file}:\n";
        foreach ($fileIssues as $issue) {
            echo "  - {$issue}\n";
        }
        echo "\n";
    }
    
    echo "💡 권장사항:\n";
    echo "- 긴 함수는 여러 개의 작은 함수로 분리하세요\n";
    echo "- 하드코딩된 비밀번호는 환경 변수로 이동하세요\n";
    echo "- SQL 쿼리는 prepared statements를 사용하세요\n";
    echo "- 에러 리포팅은 개발 환경에서만 활성화하세요\n";
    
    exit(1);
} 