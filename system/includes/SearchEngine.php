<?php

namespace System\Includes;

use PDO;

/**
 * 고급 검색 엔진
 * 전체 텍스트 검색, 필터링, 랭킹을 지원하는 검색 시스템
 */
class SearchEngine
{
    private PDO $pdo;
    private Logger $logger;
    private array $config;
    private array $searchableTables;

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->logger = new Logger('search');
        $this->config = array_merge([
            'min_score' => 0.1,
            'max_results' => 100,
            'highlight_enabled' => true,
            'fuzzy_search' => true,
            'synonyms' => [
                'hello' => ['hi', 'hey', 'greetings'],
                'goodbye' => ['bye', 'see you', 'farewell'],
                'computer' => ['pc', 'laptop', 'desktop']
            ]
        ], $config);

        $this->searchableTables = [
            'vocabulary' => [
                'table' => 'vocabulary',
                'fields' => ['word', 'meaning', 'example'],
                'weight' => ['word' => 3, 'meaning' => 2, 'example' => 1],
                'filters' => ['language', 'difficulty', 'is_learned']
            ],
            'users' => [
                'table' => 'users',
                'fields' => ['username', 'email', 'full_name'],
                'weight' => ['username' => 2, 'full_name' => 2, 'email' => 1],
                'filters' => ['role', 'status']
            ],
            'health_records' => [
                'table' => 'health_records',
                'fields' => ['title', 'description', 'notes'],
                'weight' => ['title' => 3, 'description' => 2, 'notes' => 1],
                'filters' => ['category', 'date']
            ]
        ];

        $this->initializeSearchIndexes();
    }

    /**
     * 검색 인덱스 초기화
     */
    private function initializeSearchIndexes(): void
    {
        // FTS5 가상 테이블 생성 (SQLite)
        foreach ($this->searchableTables as $name => $config) {
            $this->createSearchIndex($name, $config);
        }
    }

    /**
     * 검색 인덱스 생성
     */
    private function createSearchIndex(string $name, array $config): void
    {
        $tableName = $config['table'];
        $indexName = "search_{$name}";
        
        // FTS5 가상 테이블 생성
        $fields = implode(', ', $config['fields']);
        $sql = "CREATE VIRTUAL TABLE IF NOT EXISTS {$indexName} USING fts5(
            {$fields},
            content='{$tableName}',
            content_rowid='id'
        )";
        
        $this->pdo->exec($sql);
        
        // 트리거 생성 (데이터 동기화)
        $this->createSyncTriggers($name, $config);
    }

    /**
     * 동기화 트리거 생성
     */
    private function createSyncTriggers(string $name, array $config): void
    {
        $tableName = $config['table'];
        $indexName = "search_{$name}";
        $fields = implode(', ', $config['fields']);
        
        // INSERT 트리거
        $sql = "CREATE TRIGGER IF NOT EXISTS {$indexName}_insert AFTER INSERT ON {$tableName}
                BEGIN
                    INSERT INTO {$indexName}({$fields}) VALUES({$fields});
                END";
        $this->pdo->exec($sql);
        
        // UPDATE 트리거
        $sql = "CREATE TRIGGER IF NOT EXISTS {$indexName}_update AFTER UPDATE ON {$tableName}
                BEGIN
                    UPDATE {$indexName} SET {$fields} = NEW.{$fields} WHERE rowid = NEW.id;
                END";
        $this->pdo->exec($sql);
        
        // DELETE 트리거
        $sql = "CREATE TRIGGER IF NOT EXISTS {$indexName}_delete AFTER DELETE ON {$tableName}
                BEGIN
                    DELETE FROM {$indexName} WHERE rowid = OLD.id;
                END";
        $this->pdo->exec($sql);
    }

    /**
     * 검색 실행
     */
    public function search(string $query, array $options = []): array
    {
        $startTime = microtime(true);
        
        try {
            $this->logger->info('Search started', [
                'query' => $query,
                'options' => $options
            ]);

            // 검색 옵션 설정
            $options = array_merge([
                'tables' => array_keys($this->searchableTables),
                'filters' => [],
                'sort' => 'relevance',
                'limit' => $this->config['max_results'],
                'offset' => 0,
                'highlight' => $this->config['highlight_enabled']
            ], $options);

            // 쿼리 전처리
            $processedQuery = $this->preprocessQuery($query);
            
            // 각 테이블에서 검색
            $results = [];
            foreach ($options['tables'] as $tableName) {
                if (isset($this->searchableTables[$tableName])) {
                    $tableResults = $this->searchTable($tableName, $processedQuery, $options);
                    $results = array_merge($results, $tableResults);
                }
            }

            // 결과 정렬
            $results = $this->sortResults($results, $options['sort']);
            
            // 페이지네이션
            $totalCount = count($results);
            $results = array_slice($results, $options['offset'], $options['limit']);

            $searchTime = (microtime(true) - $startTime) * 1000;

            $this->logger->info('Search completed', [
                'query' => $query,
                'results_count' => count($results),
                'total_count' => $totalCount,
                'search_time_ms' => round($searchTime, 2)
            ]);

            return [
                'success' => true,
                'query' => $query,
                'results' => $results,
                'total_count' => $totalCount,
                'search_time_ms' => round($searchTime, 2),
                'has_more' => ($options['offset'] + $options['limit']) < $totalCount
            ];

        } catch (\Exception $e) {
            $this->logger->error('Search failed', [
                'query' => $query,
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage(),
                'results' => []
            ];
        }
    }

    /**
     * 쿼리 전처리
     */
    private function preprocessQuery(string $query): string
    {
        // 특수문자 제거 및 정규화
        $query = trim($query);
        $query = preg_replace('/[^\w\s가-힣]/u', ' ', $query);
        $query = preg_replace('/\s+/', ' ', $query);
        
        // 동의어 확장
        if ($this->config['fuzzy_search']) {
            $query = $this->expandSynonyms($query);
        }
        
        // FTS5 구문으로 변환
        $terms = explode(' ', $query);
        $terms = array_filter($terms, function($term) {
            return strlen($term) >= 2;
        });
        
        return implode(' OR ', $terms);
    }

    /**
     * 동의어 확장
     */
    private function expandSynonyms(string $query): string
    {
        $expandedTerms = [];
        $terms = explode(' ', $query);
        
        foreach ($terms as $term) {
            $expandedTerms[] = $term;
            
            // 동의어 추가
            foreach ($this->config['synonyms'] as $word => $synonyms) {
                if (strcasecmp($term, $word) === 0) {
                    $expandedTerms = array_merge($expandedTerms, $synonyms);
                }
            }
        }
        
        return implode(' ', array_unique($expandedTerms));
    }

    /**
     * 테이블별 검색
     */
    private function searchTable(string $tableName, string $query, array $options): array
    {
        $config = $this->searchableTables[$tableName];
        $indexName = "search_{$tableName}";
        
        // 기본 검색 쿼리
        $sql = "SELECT 
                    rowid as id,
                    {$indexName}.*,
                    rank as relevance_score
                FROM {$indexName}
                WHERE {$indexName} MATCH ?
                ORDER BY rank";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$query]);
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // 필터 적용
        if (isset($options['filters'][$tableName])) {
            $results = $this->applyFilters($results, $options['filters'][$tableName], $config);
        }
        
        // 하이라이트 적용
        if ($options['highlight']) {
            $results = $this->applyHighlighting($results, $query, $config);
        }
        
        // 메타데이터 추가
        foreach ($results as &$result) {
            $result['table'] = $tableName;
            $result['type'] = $this->getResultType($tableName);
            $result['url'] = $this->generateResultUrl($tableName, $result['id']);
        }
        
        return $results;
    }

    /**
     * 필터 적용
     */
    private function applyFilters(array $results, array $filters, array $config): array
    {
        if (empty($filters)) {
            return $results;
        }
        
        $filteredResults = [];
        
        foreach ($results as $result) {
            $include = true;
            
            foreach ($filters as $field => $value) {
                if (isset($config['filters']) && in_array($field, $config['filters'])) {
                    // 실제 테이블에서 필터링
                    $actualValue = $this->getFieldValue($config['table'], $result['id'], $field);
                    
                    if (is_array($value)) {
                        if (!in_array($actualValue, $value)) {
                            $include = false;
                            break;
                        }
                    } else {
                        if ($actualValue != $value) {
                            $include = false;
                            break;
                        }
                    }
                }
            }
            
            if ($include) {
                $filteredResults[] = $result;
            }
        }
        
        return $filteredResults;
    }

    /**
     * 필드 값 가져오기
     */
    private function getFieldValue(string $table, int $id, string $field)
    {
        $sql = "SELECT {$field} FROM {$table} WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetchColumn();
    }

    /**
     * 하이라이트 적용
     */
    private function applyHighlighting(array $results, string $query, array $config): array
    {
        $terms = explode(' OR ', $query);
        
        foreach ($results as &$result) {
            foreach ($config['fields'] as $field) {
                if (isset($result[$field])) {
                    $highlighted = $result[$field];
                    
                    foreach ($terms as $term) {
                        $term = trim($term);
                        if (strlen($term) >= 2) {
                            $pattern = '/\b(' . preg_quote($term, '/') . ')\b/i';
                            $highlighted = preg_replace($pattern, '<mark>$1</mark>', $highlighted);
                        }
                    }
                    
                    $result["highlighted_{$field}"] = $highlighted;
                }
            }
        }
        
        return $results;
    }

    /**
     * 결과 정렬
     */
    private function sortResults(array $results, string $sortBy): array
    {
        return match($sortBy) {
            'relevance' => $this->sortByRelevance($results),
            'date' => $this->sortByDate($results),
            'name' => $this->sortByName($results),
            default => $results
        };
    }

    /**
     * 관련성 기준 정렬
     */
    private function sortByRelevance(array $results): array
    {
        usort($results, function($a, $b) {
            $scoreA = $a['relevance_score'] ?? 0;
            $scoreB = $b['relevance_score'] ?? 0;
            return $scoreB <=> $scoreA;
        });
        
        return $results;
    }

    /**
     * 날짜 기준 정렬
     */
    private function sortByDate(array $results): array
    {
        usort($results, function($a, $b) {
            $dateA = $this->getResultDate($a);
            $dateB = $this->getResultDate($b);
            return $dateB <=> $dateA;
        });
        
        return $results;
    }

    /**
     * 이름 기준 정렬
     */
    private function sortByName(array $results): array
    {
        usort($results, function($a, $b) {
            $nameA = $this->getResultName($a);
            $nameB = $this->getResultName($b);
            return strcasecmp($nameA, $nameB);
        });
        
        return $results;
    }

    /**
     * 결과 날짜 가져오기
     */
    private function getResultDate(array $result): string
    {
        $dateFields = ['created_at', 'updated_at', 'upload_date'];
        
        foreach ($dateFields as $field) {
            if (isset($result[$field])) {
                return $result[$field];
            }
        }
        
        return '1970-01-01';
    }

    /**
     * 결과 이름 가져오기
     */
    private function getResultName(array $result): string
    {
        $nameFields = ['word', 'username', 'title', 'name'];
        
        foreach ($nameFields as $field) {
            if (isset($result[$field])) {
                return $result[$field];
            }
        }
        
        return '';
    }

    /**
     * 결과 타입 가져오기
     */
    private function getResultType(string $tableName): string
    {
        return match($tableName) {
            'vocabulary' => 'vocabulary',
            'users' => 'user',
            'health_records' => 'health_record',
            default => 'unknown'
        };
    }

    /**
     * 결과 URL 생성
     */
    private function generateResultUrl(string $tableName, int $id): string
    {
        return match($tableName) {
            'vocabulary' => "/modules/learning/voca/voca_edit.php?id={$id}",
            'users' => "/system/admin/user_edit.php?id={$id}",
            'health_records' => "/modules/management/myhealth/edit_health.php?id={$id}",
            default => "#"
        };
    }

    /**
     * 검색 제안
     */
    public function getSuggestions(string $query, int $limit = 10): array
    {
        $suggestions = [];
        
        foreach ($this->searchableTables as $tableName => $config) {
            $indexName = "search_{$tableName}";
            
            $sql = "SELECT DISTINCT word FROM {$indexName}_terms 
                    WHERE word LIKE ? 
                    ORDER BY rank DESC 
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$query . '%', $limit]);
            
            $tableSuggestions = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $suggestions = array_merge($suggestions, $tableSuggestions);
        }
        
        // 중복 제거 및 정렬
        $suggestions = array_unique($suggestions);
        sort($suggestions);
        
        return array_slice($suggestions, 0, $limit);
    }

    /**
     * 인기 검색어
     */
    public function getPopularSearches(int $limit = 10): array
    {
        $popular = [];
        
        foreach ($this->searchableTables as $tableName => $config) {
            $indexName = "search_{$tableName}";
            
            $sql = "SELECT word, rank FROM {$indexName}_terms 
                    ORDER BY rank DESC 
                    LIMIT ?";
            
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$limit]);
            
            $tablePopular = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $popular = array_merge($popular, $tablePopular);
        }
        
        // 랭킹 기준 정렬
        usort($popular, function($a, $b) {
            return ($b['rank'] ?? 0) <=> ($a['rank'] ?? 0);
        });
        
        return array_slice($popular, 0, $limit);
    }

    /**
     * 검색 통계
     */
    public function getSearchStats(): array
    {
        $stats = [
            'total_documents' => 0,
            'total_terms' => 0,
            'by_table' => []
        ];
        
        foreach ($this->searchableTables as $tableName => $config) {
            $indexName = "search_{$tableName}";
            
            // 문서 수
            $docCount = $this->pdo->query("SELECT COUNT(*) FROM {$indexName}")->fetchColumn();
            
            // 용어 수
            $termCount = $this->pdo->query("SELECT COUNT(*) FROM {$indexName}_terms")->fetchColumn();
            
            $stats['total_documents'] += $docCount;
            $stats['total_terms'] += $termCount;
            
            $stats['by_table'][$tableName] = [
                'documents' => $docCount,
                'terms' => $termCount
            ];
        }
        
        return $stats;
    }

    /**
     * 검색 인덱스 재구성
     */
    public function rebuildIndexes(): array
    {
        $results = [];
        
        foreach ($this->searchableTables as $name => $config) {
            try {
                $indexName = "search_{$name}";
                
                // 기존 인덱스 삭제
                $this->pdo->exec("DROP TABLE IF EXISTS {$indexName}");
                
                // 새 인덱스 생성
                $this->createSearchIndex($name, $config);
                
                // 데이터 재인덱싱
                $this->reindexTable($name, $config);
                
                $results[$name] = [
                    'success' => true,
                    'message' => 'Index rebuilt successfully'
                ];
                
            } catch (\Exception $e) {
                $results[$name] = [
                    'success' => false,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return $results;
    }

    /**
     * 테이블 재인덱싱
     */
    private function reindexTable(string $name, array $config): void
    {
        $tableName = $config['table'];
        $indexName = "search_{$name}";
        $fields = implode(', ', $config['fields']);
        
        $sql = "INSERT INTO {$indexName}({$fields}) 
                SELECT {$fields} FROM {$tableName}";
        
        $this->pdo->exec($sql);
    }
} 