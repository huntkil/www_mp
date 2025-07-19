<?php

namespace Tests\Integration;

use Tests\TestCase;
use VocabularyController;

class VocabularyApiTest extends TestCase
{
    private VocabularyController $controller;
    
    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = new VocabularyController();
        $this->simulateLoggedInUser('testuser');
    }
    
    public function testIndexReturnsVocabularyList(): void
    {
        $this->simulateAjaxRequest('GET', '/api/vocabulary');
        
        $result = $this->controller->index();
        
        $this->assertIsArray($result);
        $this->assertArrayHasKey('data', $result);
        $this->assertArrayHasKey('total', $result);
        $this->assertArrayHasKey('current_page', $result);
        $this->assertArrayHasKey('total_pages', $result);
        
        // 테스트 데이터가 포함되어 있는지 확인
        $this->assertGreaterThan(0, $result['total']);
        $this->assertNotEmpty($result['data']);
    }
    
    public function testIndexWithPagination(): void
    {
        $this->simulateAjaxRequest('GET', '/api/vocabulary', ['page' => 1, 'per_page' => 1]);
        
        $result = $this->controller->index();
        
        $this->assertEquals(1, $result['current_page']);
        $this->assertEquals(1, count($result['data']));
        $this->assertGreaterThan(1, $result['total_pages']);
    }
    
    public function testIndexWithSearch(): void
    {
        $this->simulateAjaxRequest('GET', '/api/vocabulary', ['search' => 'serendipity']);
        
        $result = $this->controller->index();
        
        $this->assertGreaterThan(0, $result['total']);
        $this->assertNotEmpty($result['data']);
        
        // 검색 결과에 'serendipity'가 포함되어 있는지 확인
        $found = false;
        foreach ($result['data'] as $item) {
            if (stripos($item['word'], 'serendipity') !== false || 
                stripos($item['meaning'], 'serendipity') !== false) {
                $found = true;
                break;
            }
        }
        $this->assertTrue($found, 'Search result should contain "serendipity"');
    }
    
    public function testStoreCreatesNewVocabulary(): void
    {
        $data = [
            'word' => 'ephemeral',
            'meaning' => '일시적인, 덧없는',
            'example' => 'The beauty of cherry blossoms is ephemeral.',
            'language' => 'en',
            'difficulty' => 'hard'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $data);
        
        $result = $this->controller->store();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        $this->assertEquals('ephemeral', $result['data']['word']);
        $this->assertEquals('일시적인, 덧없는', $result['data']['meaning']);
        
        // 데이터베이스에 실제로 저장되었는지 확인
        $this->assertDatabaseHas('vocabulary', [
            'user_id' => 'testuser',
            'word' => 'ephemeral'
        ]);
    }
    
    public function testStoreValidatesRequiredFields(): void
    {
        $data = [
            'meaning' => '뜻만 있고 단어는 없음'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $data);
        
        $result = $this->controller->store();
        
        $this->assertFalse($result['success']);
        $this->assertArrayHasKey('errors', $result);
        $this->assertArrayHasKey('word', $result['errors']);
    }
    
    public function testStorePreventsDuplicateWords(): void
    {
        // 첫 번째 단어 추가
        $data1 = [
            'word' => 'unique',
            'meaning' => '고유한',
            'language' => 'en'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $data1);
        $result1 = $this->controller->store();
        $this->assertTrue($result1['success']);
        
        // 동일한 단어 다시 추가 시도
        $data2 = [
            'word' => 'unique',
            'meaning' => '다른 뜻',
            'language' => 'en'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $data2);
        $result2 = $this->controller->store();
        
        $this->assertFalse($result2['success']);
        $this->assertEquals('Word already exists', $result2['message']);
    }
    
    public function testUpdateModifiesVocabulary(): void
    {
        // 먼저 단어 추가
        $createData = [
            'word' => 'original',
            'meaning' => '원래의',
            'language' => 'en'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $createData);
        $createResult = $this->controller->store();
        $vocabularyId = $createResult['data']['id'];
        
        // 단어 수정
        $updateData = [
            'word' => 'original',
            'meaning' => '원래의, 본래의',
            'example' => 'This is the original version.'
        ];
        
        $this->simulateAjaxRequest('PUT', "/api/vocabulary/{$vocabularyId}", $updateData);
        $updateResult = $this->controller->update($vocabularyId);
        
        $this->assertTrue($updateResult['success']);
        $this->assertEquals('원래의, 본래의', $updateResult['data']['meaning']);
        $this->assertEquals('This is the original version.', $updateResult['data']['example']);
    }
    
    public function testDestroyDeletesVocabulary(): void
    {
        // 먼저 단어 추가
        $createData = [
            'word' => 'temporary',
            'meaning' => '임시의',
            'language' => 'en'
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $createData);
        $createResult = $this->controller->store();
        $vocabularyId = $createResult['data']['id'];
        
        // 단어 삭제
        $this->simulateAjaxRequest('DELETE', "/api/vocabulary/{$vocabularyId}");
        $deleteResult = $this->controller->destroy($vocabularyId);
        
        $this->assertTrue($deleteResult['success']);
        
        // 데이터베이스에서 실제로 삭제되었는지 확인
        $this->assertDatabaseMissing('vocabulary', [
            'id' => $vocabularyId,
            'user_id' => 'testuser'
        ]);
    }
    
    public function testToggleLearnedChangesStatus(): void
    {
        // 먼저 단어 추가 (학습되지 않은 상태)
        $createData = [
            'word' => 'toggle',
            'meaning' => '토글',
            'language' => 'en',
            'learned' => false
        ];
        
        $this->simulateAjaxRequest('POST', '/api/vocabulary', $createData);
        $createResult = $this->controller->store();
        $vocabularyId = $createResult['data']['id'];
        
        // 학습 상태 토글
        $this->simulateAjaxRequest('PATCH', "/api/vocabulary/{$vocabularyId}/toggle");
        $toggleResult = $this->controller->toggleLearned($vocabularyId);
        
        $this->assertTrue($toggleResult['success']);
        $this->assertTrue($toggleResult['data']['learned']);
        
        // 다시 토글하여 학습되지 않은 상태로 변경
        $this->simulateAjaxRequest('PATCH', "/api/vocabulary/{$vocabularyId}/toggle");
        $toggleResult2 = $this->controller->toggleLearned($vocabularyId);
        
        $this->assertTrue($toggleResult2['success']);
        $this->assertFalse($toggleResult2['data']['learned']);
    }
    
    public function testStatsReturnsStatistics(): void
    {
        $this->simulateAjaxRequest('GET', '/api/vocabulary/stats');
        
        $result = $this->controller->stats();
        
        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('data', $result);
        
        $stats = $result['data'];
        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('learned', $stats);
        $this->assertArrayHasKey('this_week', $stats);
        $this->assertArrayHasKey('streak', $stats);
        $this->assertArrayHasKey('difficulty_stats', $stats);
        $this->assertArrayHasKey('language_stats', $stats);
        $this->assertArrayHasKey('monthly_stats', $stats);
        
        // 기본 테스트 데이터가 있으므로 total은 0보다 커야 함
        $this->assertGreaterThan(0, $stats['total']);
    }
    
    public function testExportReturnsData(): void
    {
        $this->simulateAjaxRequest('GET', '/api/vocabulary/export', ['format' => 'json']);
        
        // export 메서드는 직접 출력하므로 출력 버퍼링을 사용
        ob_start();
        $this->controller->export();
        $output = ob_get_clean();
        
        $this->assertNotEmpty($output);
        
        // JSON 형식인지 확인
        $data = json_decode($output, true);
        $this->assertNotNull($data);
        $this->assertIsArray($data);
        
        // 데이터가 포함되어 있는지 확인
        $this->assertNotEmpty($data);
    }
    
    public function testUnauthorizedAccessIsDenied(): void
    {
        // 로그인하지 않은 상태로 시뮬레이션
        $this->simulateSession([]);
        
        $this->simulateAjaxRequest('GET', '/api/vocabulary');
        
        // requireLogin 메서드가 호출되면 리다이렉트되거나 에러가 발생해야 함
        $this->expectException(\Exception::class);
        $this->controller->index();
    }
} 