<?php

/**
 * 간단한 GitHub Actions 상태 확인
 * 토큰 없이도 기본적인 상태를 확인할 수 있습니다.
 */

echo "🔍 GitHub Actions 상태 확인 중...\n\n";

// GitHub Actions 페이지 URL
$actionsUrl = "https://github.com/huntkil/www_mp/actions";

echo "📊 GitHub Actions 정보:\n";
echo str_repeat("=", 50) . "\n";
echo "🌐 Actions 페이지: {$actionsUrl}\n";
echo "📱 실시간 확인: 브라우저에서 위 URL 접속\n\n";

// 현재 시간
echo "⏰ 현재 시간: " . date('Y-m-d H:i:s') . "\n";

// 마지막 커밋 정보
$lastCommit = shell_exec('git log -1 --pretty=format:"%h - %an, %ar : %s" 2>/dev/null');
if ($lastCommit) {
    echo "📝 마지막 커밋: " . trim($lastCommit) . "\n";
}

// 브랜치 정보
$currentBranch = shell_exec('git branch --show-current 2>/dev/null');
if ($currentBranch) {
    echo "🌿 현재 브랜치: " . trim($currentBranch) . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "💡 팁:\n";
echo "- GitHub Actions 페이지에서 실시간 상태 확인\n";
echo "- 각 단계별 상세 로그 확인 가능\n";
echo "- 실패 시 오류 메시지와 해결 방법 제공\n";
echo "- 배포 완료 후 사이트 접속 테스트\n\n";

echo "🌐 배포 완료 후 확인할 URL:\n";
echo "- 메인 사이트: http://gukho.net/mp/\n";
echo "- 헬스 체크: http://gukho.net/mp/health.php\n";

// 브라우저에서 열기 옵션
echo "\n🚀 브라우저에서 Actions 페이지 열기? (y/n): ";
$handle = fopen("php://stdin", "r");
$input = trim(fgets($handle));
fclose($handle);

if (strtolower($input) === 'y' || strtolower($input) === 'yes') {
    echo "🌐 브라우저에서 GitHub Actions 페이지를 열고 있습니다...\n";
    shell_exec("start {$actionsUrl}"); // Windows
} 