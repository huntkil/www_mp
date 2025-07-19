<?php

/**
 * API 문서 생성 스크립트
 * 컨트롤러를 분석하여 OpenAPI/Swagger 문서를 자동 생성합니다.
 */

require_once __DIR__ . '/../system/includes/config.php';

use System\Includes\ApiDocGenerator;

// CLI 실행 확인
if (php_sapi_name() !== 'cli') {
    die('This script can only be run from command line.');
}

echo "🚀 API 문서 생성 시작...\n\n";

try {
    // API 문서 생성기 초기화
    $generator = new ApiDocGenerator([
        'title' => 'MP Learning API',
        'version' => '1.0.0',
        'description' => 'MP Learning Platform API Documentation',
        'base_url' => 'https://gukho.net/mp/api',
        'output_format' => 'all' // JSON, YAML, HTML 모두 생성
    ]);

    // 컨트롤러 등록
    echo "📋 컨트롤러 등록 중...\n";
    
    $generator->registerController('System\\Controllers\\AuthController', '/auth');
    $generator->registerController('System\\Controllers\\VocabularyController', '/vocabulary');
    $generator->registerController('System\\Controllers\\UserController', '/users');
    $generator->registerController('System\\Controllers\\HealthController', '/health');
    
    echo "✅ 컨트롤러 등록 완료\n\n";

    // API 문서 생성
    echo "📝 API 문서 생성 중...\n";
    $result = $generator->generate();
    
    if ($result['success']) {
        echo "✅ API 문서 생성 완료!\n";
        echo "📊 생성된 경로 수: {$result['paths_count']}\n";
        echo "📋 생성된 스키마 수: {$result['schemas_count']}\n";
        echo "📁 출력 파일:\n";
        
        foreach ($result['output_files'] as $file) {
            echo "   - {$file}\n";
        }
        
        echo "\n🌐 API 문서 확인:\n";
        echo "   - HTML: https://gukho.net/mp/docs/api/index.html\n";
        echo "   - JSON: https://gukho.net/mp/docs/api/openapi.json\n";
        echo "   - YAML: https://gukho.net/mp/docs/api/openapi.yaml\n";
        
    } else {
        echo "❌ API 문서 생성 실패: {$result['error']}\n";
        exit(1);
    }

} catch (Exception $e) {
    echo "❌ 오류 발생: {$e->getMessage()}\n";
    exit(1);
}

echo "\n🎉 API 문서 생성이 완료되었습니다!\n"; 