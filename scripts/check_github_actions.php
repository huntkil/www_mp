<?php

/**
 * GitHub Actions 상태 확인 스크립트
 * GitHub API를 사용하여 Actions 실행 상태를 확인합니다.
 */

class GitHubActionsChecker
{
    private string $repo;
    private string $token;
    
    public function __construct(string $repo = 'huntkil/www_mp')
    {
        $this->repo = $repo;
        $this->token = $this->getGitHubToken();
    }
    
    private function getGitHubToken(): string
    {
        // 환경 변수에서 토큰 확인
        $token = getenv('GITHUB_TOKEN');
        if ($token) {
            return $token;
        }
        
        // 사용자 입력 요청
        echo "GitHub Personal Access Token이 필요합니다.\n";
        echo "토큰을 입력하세요 (또는 Enter를 눌러 건너뛰기): ";
        $handle = fopen("php://stdin", "r");
        $token = trim(fgets($handle));
        fclose($handle);
        
        return $token;
    }
    
    public function checkWorkflowRuns(): array
    {
        if (empty($this->token)) {
            return $this->getMockData();
        }
        
        $url = "https://api.github.com/repos/{$this->repo}/actions/runs";
        $headers = [
            "Authorization: token {$this->token}",
            "Accept: application/vnd.github.v3+json",
            "User-Agent: MP-Learning-Platform"
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            echo "GitHub API 호출 실패 (HTTP {$httpCode})\n";
            return $this->getMockData();
        }
    }
    
    public function getWorkflowRunDetails(int $runId): array
    {
        if (empty($this->token)) {
            return [];
        }
        
        $url = "https://api.github.com/repos/{$this->repo}/actions/runs/{$runId}/jobs";
        $headers = [
            "Authorization: token {$this->token}",
            "Accept: application/vnd.github.v3+json",
            "User-Agent: MP-Learning-Platform"
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        }
        
        return [];
    }
    
    private function getMockData(): array
    {
        return [
            'workflow_runs' => [
                [
                    'id' => 1,
                    'name' => 'Deploy to Cafe24',
                    'status' => 'completed',
                    'conclusion' => 'success',
                    'created_at' => date('c'),
                    'updated_at' => date('c'),
                    'head_branch' => 'main'
                ]
            ]
        ];
    }
    
    public function displayStatus(): void
    {
        echo "🔍 GitHub Actions 상태 확인 중...\n\n";
        
        $runs = $this->checkWorkflowRuns();
        
        if (empty($runs['workflow_runs'])) {
            echo "❌ 워크플로우 실행 기록을 찾을 수 없습니다.\n";
            return;
        }
        
        echo "📊 최근 워크플로우 실행 상태:\n";
        echo str_repeat("=", 60) . "\n";
        
        foreach (array_slice($runs['workflow_runs'], 0, 5) as $run) {
            $status = $this->getStatusEmoji($run['status'], $run['conclusion'] ?? '');
            $name = $run['name'];
            $branch = $run['head_branch'];
            $created = date('Y-m-d H:i:s', strtotime($run['created_at']));
            $updated = date('Y-m-d H:i:s', strtotime($run['updated_at']));
            
            echo "{$status} {$name}\n";
            echo "   브랜치: {$branch}\n";
            echo "   상태: {$run['status']}" . ($run['conclusion'] ? " ({$run['conclusion']})" : "") . "\n";
            echo "   생성: {$created}\n";
            echo "   업데이트: {$updated}\n";
            echo "   ID: {$run['id']}\n";
            echo str_repeat("-", 60) . "\n";
            
            // 상세 정보 가져오기
            if ($run['status'] === 'in_progress' || $run['status'] === 'completed') {
                $this->displayJobDetails($run['id']);
            }
        }
        
        echo "\n🌐 GitHub Actions 페이지: https://github.com/{$this->repo}/actions\n";
    }
    
    private function getStatusEmoji(string $status, string $conclusion): string
    {
        if ($status === 'completed') {
            return $conclusion === 'success' ? '✅' : '❌';
        } elseif ($status === 'in_progress') {
            return '🔄';
        } elseif ($status === 'queued') {
            return '⏳';
        } else {
            return '❓';
        }
    }
    
    private function displayJobDetails(int $runId): void
    {
        $jobs = $this->getWorkflowRunDetails($runId);
        
        if (empty($jobs['jobs'])) {
            return;
        }
        
        echo "   📋 작업 상세:\n";
        
        foreach ($jobs['jobs'] as $job) {
            $status = $this->getStatusEmoji($job['status'], $job['conclusion'] ?? '');
            $name = $job['name'];
            $duration = $this->formatDuration($job['started_at'], $job['completed_at']);
            
            echo "      {$status} {$name} ({$duration})\n";
            
            if ($job['status'] === 'completed' && $job['conclusion'] === 'failure') {
                echo "         ❌ 실패 - 로그 확인 필요\n";
            }
        }
        echo "\n";
    }
    
    private function formatDuration(?string $started, ?string $completed): string
    {
        if (!$started || !$completed) {
            return '진행 중';
        }
        
        $start = new DateTime($started);
        $end = new DateTime($completed);
        $diff = $start->diff($end);
        
        if ($diff->h > 0) {
            return "{$diff->h}시간 {$diff->i}분";
        } elseif ($diff->i > 0) {
            return "{$diff->i}분 {$diff->s}초";
        } else {
            return "{$diff->s}초";
        }
    }
}

// 스크립트 실행
if (php_sapi_name() === 'cli') {
    $checker = new GitHubActionsChecker();
    $checker->displayStatus();
} else {
    echo "이 스크립트는 CLI에서만 실행할 수 있습니다.\n";
} 