<?php
/**
 * 코드 품질 검사 스크립트
 * Windows 환경에서 PHP 경로 문제를 해결하고 깔끔한 출력 제공
 */

class CodeQualityChecker {
    private $phpPath;
    private $issues = [];
    private $totalFiles = 0;
    private $checkedFiles = 0;
    private $problemFiles = 0;
    
    public function __construct() {
        // Windows 환경에서 PHP 경로 자동 감지
        $this->phpPath = $this->detectPhpPath();
    }
    
    private function detectPhpPath(): string {
        $possiblePaths = [
            '/c/xampp/php/php.exe',
            'C:/xampp/php/php.exe',
            'php',
            '/usr/bin/php',
            '/usr/local/bin/php'
        ];
        
        foreach ($possiblePaths as $path) {
            if ($this->isPhpExecutable($path)) {
                return $path;
            }
        }
        
        return 'php'; // 기본값
    }
    
    private function isPhpExecutable($path): bool {
        $output = [];
        $returnCode = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            // Windows 환경
            exec("$path -v 2>nul", $output, $returnCode);
        } else {
            // Unix/Linux 환경
            exec("$path -v 2>/dev/null", $output, $returnCode);
        }
        
        return $returnCode === 0;
    }
    
    public function run(): void {
        echo "🔍 코드 품질 검사 시작...\n";
        echo "📁 PHP 경로: {$this->phpPath}\n\n";
        
        $phpFiles = $this->findPhpFiles();
        $this->totalFiles = count($phpFiles);
        
        foreach ($phpFiles as $file) {
            $this->checkFile($file);
        }
        
        $this->displayResults();
    }
    
    private function findPhpFiles(): array {
        $files = [];
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator('.', RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                $filePath = $file->getPathname();
                
                // 특정 파일/디렉토리 제외
                if ($this->shouldExcludeFile($filePath)) {
                    continue;
                }
                
                $files[] = $filePath;
            }
        }
        
        return $files;
    }
    
    private function shouldExcludeFile($filePath): bool {
        $excludePatterns = [
            '/vendor/',
            '/node_modules/',
            '/.git/',
            '/config/logs/',
            '/config/sessions/',
            '/system/logs/',
            '/system/uploads/',
            '/resources/uploads/',
            '/scripts/code_quality_check.php', // 자기 자신 제외
            '/scripts/test_suite.php', // 테스트 파일 제외
        ];
        
        foreach ($excludePatterns as $pattern) {
            if (strpos($filePath, $pattern) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    private function checkFile($filePath): void {
        $this->checkedFiles++;
        
        // 1. PHP 문법 검사
        $this->checkSyntax($filePath);
        
        // 2. 긴 함수 검사
        $this->checkLongFunctions($filePath);
        
        // 3. 하드코딩된 비밀번호 검사
        $this->checkHardcodedPasswords($filePath);
        
        // 4. SQL 인젝션 위험 검사
        $this->checkSqlInjection($filePath);
        
        // 5. 에러 리포팅 비활성화 검사
        $this->checkErrorReporting($filePath);
    }
    
    private function checkSyntax($filePath): void {
        $output = [];
        $returnCode = 0;
        
        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            exec("{$this->phpPath} -l \"$filePath\" 2>nul", $output, $returnCode);
        } else {
            exec("{$this->phpPath} -l \"$filePath\" 2>/dev/null", $output, $returnCode);
        }
        
        if ($returnCode !== 0) {
            $this->addIssue($filePath, '문법 오류', implode("\n", $output));
        }
    }
    
    private function checkLongFunctions($filePath): void {
        $content = file_get_contents($filePath);
        $lines = explode("\n", $content);
        
        $inFunction = false;
        $functionStart = 0;
        $functionName = '';
        
        foreach ($lines as $lineNum => $line) {
            $lineNum++; // 1-based line numbers
            
            if (preg_match('/^\s*(public|private|protected)?\s*function\s+(\w+)/', $line, $matches)) {
                if ($inFunction && ($lineNum - $functionStart) > 50) {
                    $this->addIssue($filePath, '긴 함수 발견', "라인 {$functionStart}: " . ($lineNum - $functionStart) . "줄");
                }
                
                $inFunction = true;
                $functionStart = $lineNum;
                $functionName = $matches[2];
            } elseif ($inFunction && preg_match('/^\s*}\s*$/', $line)) {
                $functionLength = $lineNum - $functionStart;
                if ($functionLength > 50) {
                    $this->addIssue($filePath, '긴 함수 발견', "라인 {$functionStart}: {$functionLength}줄");
                }
                $inFunction = false;
            }
        }
    }
    
    private function checkHardcodedPasswords($filePath): void {
        $content = file_get_contents($filePath);
        
        $patterns = [
            '/password\s*=\s*[\'"][^\'"]{6,}[\'"]/i',
            '/passwd\s*=\s*[\'"][^\'"]{6,}[\'"]/i',
            '/pwd\s*=\s*[\'"][^\'"]{6,}[\'"]/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->addIssue($filePath, '하드코딩된 비밀번호', '비밀번호가 코드에 직접 포함되어 있습니다');
                break;
            }
        }
    }
    
    private function checkSqlInjection($filePath): void {
        $content = file_get_contents($filePath);
        
        $patterns = [
            '/\$_(GET|POST|REQUEST)\[.*?\]\s*\.\s*["\']\s*SELECT/i',
            '/\$_(GET|POST|REQUEST)\[.*?\]\s*\.\s*["\']\s*INSERT/i',
            '/\$_(GET|POST|REQUEST)\[.*?\]\s*\.\s*["\']\s*UPDATE/i',
            '/\$_(GET|POST|REQUEST)\[.*?\]\s*\.\s*["\']\s*DELETE/i',
        ];
        
        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content)) {
                $this->addIssue($filePath, 'SQL 인젝션 위험', '사용자 입력이 SQL 쿼리에 직접 연결되어 있습니다');
                break;
            }
        }
    }
    
    private function checkErrorReporting($filePath): void {
        $content = file_get_contents($filePath);
        
        // 자체 스크립트는 제외
        if (strpos($filePath, 'code_quality_check.php') !== false) {
            return;
        }
        
        if (preg_match('/error_reporting\s*\(\s*0\s*\)/', $content)) {
            $this->addIssue($filePath, '에러 리포팅 비활성화', 'error_reporting(0)이 사용되어 있습니다');
        }
    }
    
    private function addIssue($filePath, $type, $message): void {
        if (!isset($this->issues[$filePath])) {
            $this->issues[$filePath] = [];
            $this->problemFiles++;
        }
        
        $this->issues[$filePath][] = [
            'type' => $type,
            'message' => $message
        ];
    }
    
    private function displayResults(): void {
        echo "📊 검사 결과:\n";
        echo "총 파일 수: {$this->totalFiles}\n";
        echo "검사된 파일 수: {$this->checkedFiles}\n";
        echo "문제가 있는 파일 수: {$this->problemFiles}\n\n";
        
        if (empty($this->issues)) {
            echo "✅ 모든 파일이 코드 품질 기준을 통과했습니다!\n";
            return;
        }
        
        echo "⚠️ 발견된 문제들:\n\n";
        
        foreach ($this->issues as $filePath => $fileIssues) {
            echo "📁 {$filePath}:\n";
            foreach ($fileIssues as $issue) {
                echo "  - {$issue['type']}: {$issue['message']}\n";
            }
            echo "\n";
        }
        
        echo "💡 권장사항:\n";
        echo "- 긴 함수는 여러 개의 작은 함수로 분리하세요\n";
        echo "- 하드코딩된 비밀번호는 환경 변수로 이동하세요\n";
        echo "- SQL 쿼리는 prepared statements를 사용하세요\n";
        echo "- 에러 리포팅은 개발 환경에서만 활성화하세요\n";
    }
}

// 스크립트 실행
$checker = new CodeQualityChecker();
$checker->run(); 