<?php

namespace System\Includes;

use ReflectionClass;
use ReflectionMethod;
use ReflectionParameter;

/**
 * API 문서 자동 생성기
 * OpenAPI/Swagger 3.0 스펙을 지원하는 API 문서를 자동으로 생성합니다.
 */
class ApiDocGenerator
{
    private array $config;
    private array $controllers = [];
    private string $outputPath;
    private Logger $logger;

    public function __construct(array $config = [])
    {
        $this->config = array_merge([
            'title' => 'MP Learning API',
            'version' => '1.0.0',
            'description' => 'MP Learning Platform API Documentation',
            'base_url' => 'https://gukho.net/mp/api',
            'output_format' => 'json', // json, yaml, html
            'include_examples' => true,
            'include_schemas' => true
        ], $config);

        $this->outputPath = __DIR__ . '/../../docs/api/';
        $this->logger = new Logger('api_docs');
        
        if (!is_dir($this->outputPath)) {
            mkdir($this->outputPath, 0755, true);
        }
    }

    /**
     * 컨트롤러 등록
     */
    public function registerController(string $controllerClass, string $basePath = ''): void
    {
        $this->controllers[] = [
            'class' => $controllerClass,
            'base_path' => $basePath
        ];
    }

    /**
     * API 문서 생성
     */
    public function generate(): array
    {
        try {
            $this->logger->info('Starting API documentation generation');

            $openapi = [
                'openapi' => '3.0.3',
                'info' => $this->generateInfo(),
                'servers' => $this->generateServers(),
                'paths' => $this->generatePaths(),
                'components' => $this->generateComponents(),
                'tags' => $this->generateTags()
            ];

            $result = [
                'success' => true,
                'paths_count' => count($openapi['paths']),
                'schemas_count' => count($openapi['components']['schemas'] ?? []),
                'output_files' => []
            ];

            // JSON 형식으로 저장
            if ($this->config['output_format'] === 'json' || $this->config['output_format'] === 'all') {
                $jsonFile = $this->outputPath . 'openapi.json';
                file_put_contents($jsonFile, json_encode($openapi, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                $result['output_files'][] = $jsonFile;
            }

            // YAML 형식으로 저장
            if ($this->config['output_format'] === 'yaml' || $this->config['output_format'] === 'all') {
                $yamlFile = $this->outputPath . 'openapi.yaml';
                $yaml = $this->arrayToYaml($openapi);
                file_put_contents($yamlFile, $yaml);
                $result['output_files'][] = $yamlFile;
            }

            // HTML 문서 생성
            if ($this->config['output_format'] === 'html' || $this->config['output_format'] === 'all') {
                $htmlFile = $this->outputPath . 'index.html';
                $html = $this->generateHtmlDocument($openapi);
                file_put_contents($htmlFile, $html);
                $result['output_files'][] = $htmlFile;
            }

            $this->logger->info('API documentation generated successfully', $result);

            return $result;
        } catch (\Exception $e) {
            $this->logger->error('API documentation generation failed', [
                'error' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * API 정보 생성
     */
    private function generateInfo(): array
    {
        return [
            'title' => $this->config['title'],
            'version' => $this->config['version'],
            'description' => $this->config['description'],
            'contact' => [
                'name' => 'API Support',
                'email' => 'support@gukho.net'
            ],
            'license' => [
                'name' => 'MIT',
                'url' => 'https://opensource.org/licenses/MIT'
            ]
        ];
    }

    /**
     * 서버 정보 생성
     */
    private function generateServers(): array
    {
        return [
            [
                'url' => $this->config['base_url'],
                'description' => 'Production server'
            ],
            [
                'url' => 'https://staging.gukho.net/mp/api',
                'description' => 'Staging server'
            ]
        ];
    }

    /**
     * API 경로 생성
     */
    private function generatePaths(): array
    {
        $paths = [];

        foreach ($this->controllers as $controller) {
            $controllerPaths = $this->analyzeController($controller);
            $paths = array_merge($paths, $controllerPaths);
        }

        return $paths;
    }

    /**
     * 컨트롤러 분석
     */
    private function analyzeController(array $controller): array
    {
        $paths = [];
        $controllerClass = $controller['class'];
        $basePath = $controller['base_path'];

        try {
            $reflection = new ReflectionClass($controllerClass);
            $methods = $reflection->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach ($methods as $method) {
                $methodName = $method->getName();
                
                // 특정 패턴의 메서드만 분석
                if (preg_match('/^(get|post|put|patch|delete)(.+)$/i', $methodName, $matches)) {
                    $httpMethod = strtolower($matches[1]);
                    $resourceName = $this->camelToKebab($matches[2]);
                    
                    $path = $basePath . '/' . $resourceName;
                    $path = str_replace('//', '/', $path);
                    
                    $paths[$path][$httpMethod] = $this->generatePathItem($method, $controllerClass);
                }
            }
        } catch (\Exception $e) {
            $this->logger->warning('Failed to analyze controller', [
                'controller' => $controllerClass,
                'error' => $e->getMessage()
            ]);
        }

        return $paths;
    }

    /**
     * 경로 아이템 생성
     */
    private function generatePathItem(ReflectionMethod $method, string $controllerClass): array
    {
        $pathItem = [
            'summary' => $this->extractSummary($method),
            'description' => $this->extractDescription($method),
            'tags' => [$this->extractTag($method, $controllerClass)],
            'parameters' => $this->extractParameters($method),
            'requestBody' => $this->extractRequestBody($method),
            'responses' => $this->generateResponses($method),
            'security' => $this->extractSecurity($method)
        ];

        // null 값 제거
        return array_filter($pathItem, function($value) {
            return $value !== null;
        });
    }

    /**
     * 메서드 요약 추출
     */
    private function extractSummary(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        if ($docComment && preg_match('/@summary\s+(.+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }

        // 메서드 이름에서 추출
        $methodName = $method->getName();
        if (preg_match('/^(get|post|put|patch|delete)(.+)$/i', $methodName, $matches)) {
            $action = ucfirst($matches[1]);
            $resource = $this->camelToWords($matches[2]);
            return "{$action} {$resource}";
        }

        return ucfirst($method->getName());
    }

    /**
     * 메서드 설명 추출
     */
    private function extractDescription(ReflectionMethod $method): string
    {
        $docComment = $method->getDocComment();
        if ($docComment && preg_match('/@description\s+(.+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }

        return '';
    }

    /**
     * 태그 추출
     */
    private function extractTag(ReflectionMethod $method, string $controllerClass): string
    {
        $docComment = $method->getDocComment();
        if ($docComment && preg_match('/@tag\s+(.+)/', $docComment, $matches)) {
            return trim($matches[1]);
        }

        // 컨트롤러 이름에서 추출
        $className = basename(str_replace('\\', '/', $controllerClass));
        return str_replace('Controller', '', $className);
    }

    /**
     * 파라미터 추출
     */
    private function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];
        $methodParams = $method->getParameters();

        foreach ($methodParams as $param) {
            $paramName = $param->getName();
            
            // ID 파라미터는 경로 파라미터로 처리
            if (strpos($paramName, 'id') !== false) {
                $parameters[] = [
                    'name' => $paramName,
                    'in' => 'path',
                    'required' => true,
                    'schema' => [
                        'type' => 'integer',
                        'format' => 'int64'
                    ],
                    'description' => ucfirst($paramName)
                ];
            } else {
                // 쿼리 파라미터로 처리
                $parameters[] = [
                    'name' => $paramName,
                    'in' => 'query',
                    'required' => $param->isDefaultValueAvailable() ? false : true,
                    'schema' => [
                        'type' => $this->getParameterType($param)
                    ],
                    'description' => ucfirst($paramName)
                ];
            }
        }

        return $parameters;
    }

    /**
     * 요청 본문 추출
     */
    private function extractRequestBody(ReflectionMethod $method): ?array
    {
        $httpMethod = strtolower(substr($method->getName(), 0, 3));
        
        // POST, PUT, PATCH 메서드만 요청 본문을 가짐
        if (!in_array($httpMethod, ['pos', 'put', 'pat'])) {
            return null;
        }

        $docComment = $method->getDocComment();
        $schemaName = null;

        if ($docComment && preg_match('/@request\s+(.+)/', $docComment, $matches)) {
            $schemaName = trim($matches[1]);
        } else {
            // 메서드 이름에서 추출
            $methodName = $method->getName();
            if (preg_match('/^(post|put|patch)(.+)$/i', $methodName, $matches)) {
                $schemaName = ucfirst($matches[2]) . 'Request';
            }
        }

        if ($schemaName) {
            return [
                'required' => true,
                'content' => [
                    'application/json' => [
                        'schema' => [
                            '$ref' => "#/components/schemas/{$schemaName}"
                        ]
                    ]
                ]
            ];
        }

        return null;
    }

    /**
     * 응답 생성
     */
    private function generateResponses(ReflectionMethod $method): array
    {
        $responses = [
            '200' => $this->createSuccessResponse($method),
            '400' => $this->createErrorResponse('Bad request'),
            '401' => $this->createErrorResponse('Unauthorized'),
            '404' => $this->createErrorResponse('Not found'),
            '500' => $this->createErrorResponse('Internal server error')
        ];

        return $responses;
    }

    /**
     * 성공 응답 생성
     */
    private function createSuccessResponse(ReflectionMethod $method): array
    {
        return [
            'description' => 'Successful operation',
            'content' => [
                'application/json' => [
                    'schema' => $this->extractResponseSchema($method)
                ]
            ]
        ];
    }

    /**
     * 에러 응답 생성
     */
    private function createErrorResponse(string $description): array
    {
        return [
            'description' => $description,
            'content' => [
                'application/json' => [
                    'schema' => [
                        '$ref' => '#/components/schemas/ErrorResponse'
                    ]
                ]
            ]
        ];
    }

    /**
     * 응답 스키마 추출
     */
    private function extractResponseSchema(ReflectionMethod $method): array
    {
        $docComment = $method->getDocComment();
        $schemaName = null;

        if ($docComment && preg_match('/@response\s+(.+)/', $docComment, $matches)) {
            $schemaName = trim($matches[1]);
        } else {
            // 메서드 이름에서 추출
            $methodName = $method->getName();
            if (preg_match('/^(get|post|put|patch|delete)(.+)$/i', $methodName, $matches)) {
                $resourceName = ucfirst($matches[2]);
                $schemaName = $resourceName . 'Response';
            }
        }

        if ($schemaName) {
            return [
                '$ref' => "#/components/schemas/{$schemaName}"
            ];
        }

        // 기본 응답 스키마
        return [
            'type' => 'object',
            'properties' => [
                'success' => [
                    'type' => 'boolean',
                    'description' => 'Operation success status'
                ],
                'data' => [
                    'type' => 'object',
                    'description' => 'Response data'
                ],
                'message' => [
                    'type' => 'string',
                    'description' => 'Response message'
                ]
            ]
        ];
    }

    /**
     * 보안 정보 추출
     */
    private function extractSecurity(ReflectionMethod $method): ?array
    {
        $docComment = $method->getDocComment();
        
        if ($docComment && preg_match('/@auth\s+(.+)/', $docComment, $matches)) {
            $authType = trim($matches[1]);
            
            if ($authType === 'required') {
                return [
                    [
                        'bearerAuth' => []
                    ]
                ];
            }
        }

        return null;
    }

    /**
     * 컴포넌트 생성
     */
    private function generateComponents(): array
    {
        $components = [
            'schemas' => $this->generateSchemas(),
            'securitySchemes' => [
                'bearerAuth' => [
                    'type' => 'http',
                    'scheme' => 'bearer',
                    'bearerFormat' => 'JWT'
                ]
            ]
        ];

        return $components;
    }

    /**
     * 스키마 생성
     */
    private function generateSchemas(): array
    {
        $schemas = [
            'ErrorResponse' => $this->createErrorResponseSchema(),
            'Vocabulary' => $this->createVocabularySchema(),
            'User' => $this->createUserSchema()
        ];

        return $schemas;
    }

    /**
     * 에러 응답 스키마 생성
     */
    private function createErrorResponseSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'success' => [
                    'type' => 'boolean',
                    'example' => false
                ],
                'message' => [
                    'type' => 'string',
                    'example' => 'Error message'
                ],
                'errors' => [
                    'type' => 'object',
                    'description' => 'Field-specific errors'
                ]
            ]
        ];
    }

    /**
     * 어휘 스키마 생성
     */
    private function createVocabularySchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'format' => 'int64',
                    'example' => 1
                ],
                'word' => [
                    'type' => 'string',
                    'example' => 'serendipity'
                ],
                'meaning' => [
                    'type' => 'string',
                    'example' => '뜻밖의 발견'
                ],
                'example' => [
                    'type' => 'string',
                    'example' => 'Finding that book was pure serendipity.'
                ],
                'language' => [
                    'type' => 'string',
                    'enum' => ['en', 'ko', 'ja'],
                    'example' => 'en'
                ],
                'difficulty' => [
                    'type' => 'string',
                    'enum' => ['easy', 'medium', 'hard'],
                    'example' => 'hard'
                ],
                'is_learned' => [
                    'type' => 'boolean',
                    'example' => false
                ],
                'created_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2024-01-01T00:00:00Z'
                ],
                'updated_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2024-01-01T00:00:00Z'
                ]
            ]
        ];
    }

    /**
     * 사용자 스키마 생성
     */
    private function createUserSchema(): array
    {
        return [
            'type' => 'object',
            'properties' => [
                'id' => [
                    'type' => 'integer',
                    'format' => 'int64',
                    'example' => 1
                ],
                'username' => [
                    'type' => 'string',
                    'example' => 'john_doe'
                ],
                'email' => [
                    'type' => 'string',
                    'format' => 'email',
                    'example' => 'john@example.com'
                ],
                'full_name' => [
                    'type' => 'string',
                    'example' => 'John Doe'
                ],
                'role' => [
                    'type' => 'string',
                    'enum' => ['user', 'admin'],
                    'example' => 'user'
                ],
                'status' => [
                    'type' => 'string',
                    'enum' => ['active', 'inactive'],
                    'example' => 'active'
                ],
                'created_at' => [
                    'type' => 'string',
                    'format' => 'date-time',
                    'example' => '2024-01-01T00:00:00Z'
                ]
            ]
        ];
    }

    /**
     * 태그 생성
     */
    private function generateTags(): array
    {
        return [
            [
                'name' => 'Vocabulary',
                'description' => 'Vocabulary management operations'
            ],
            [
                'name' => 'Auth',
                'description' => 'Authentication and authorization'
            ],
            [
                'name' => 'User',
                'description' => 'User management operations'
            ]
        ];
    }

    /**
     * HTML 문서 생성
     */
    private function generateHtmlDocument(array $openapi): string
    {
        $swaggerUiVersion = '5.10.3';
        $jsonUrl = $this->config['base_url'] . '/docs/api/openapi.json';

        return <<<HTML
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$this->config['title']} - API Documentation</title>
    <link rel="stylesheet" type="text/css" href="https://unpkg.com/swagger-ui-dist@{$swaggerUiVersion}/swagger-ui.css" />
    <style>
        html {
            box-sizing: border-box;
            overflow: -moz-scrollbars-vertical;
            overflow-y: scroll;
        }
        *, *:before, *:after {
            box-sizing: inherit;
        }
        body {
            margin:0;
            background: #fafafa;
        }
        .swagger-ui .topbar {
            background-color: #2c3e50;
        }
        .swagger-ui .topbar .download-url-wrapper .select-label {
            color: #fff;
        }
    </style>
</head>
<body>
    <div id="swagger-ui"></div>
    <script src="https://unpkg.com/swagger-ui-dist@{$swaggerUiVersion}/swagger-ui-bundle.js"></script>
    <script src="https://unpkg.com/swagger-ui-dist@{$swaggerUiVersion}/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: '{$jsonUrl}',
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                validatorUrl: null
            });
        };
    </script>
</body>
</html>
HTML;
    }

    /**
     * 배열을 YAML로 변환
     */
    private function arrayToYaml(array $array, int $indent = 0): string
    {
        $yaml = '';
        $indentStr = str_repeat('  ', $indent);

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                if (empty($value)) {
                    $yaml .= "{$indentStr}{$key}: []\n";
                } else {
                    $yaml .= "{$indentStr}{$key}:\n";
                    $yaml .= $this->arrayToYaml($value, $indent + 1);
                }
            } else {
                $yaml .= "{$indentStr}{$key}: " . $this->yamlValue($value) . "\n";
            }
        }

        return $yaml;
    }

    /**
     * YAML 값 포맷팅
     */
    private function yamlValue($value): string
    {
        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }
        if (is_null($value)) {
            return 'null';
        }
        if (is_string($value) && (strpos($value, "\n") !== false || strpos($value, ':') !== false)) {
            return '"' . addslashes($value) . '"';
        }
        return (string) $value;
    }

    /**
     * 카멜케이스를 케밥케이스로 변환
     */
    private function camelToKebab(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $string));
    }

    /**
     * 카멜케이스를 단어로 변환
     */
    private function camelToWords(string $string): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', ' $0', $string));
    }

    /**
     * 파라미터 타입 가져오기
     */
    private function getParameterType(ReflectionParameter $param): string
    {
        $type = $param->getType();
        
        if ($type) {
            $typeName = $type->getName();
            return match($typeName) {
                'int' => 'integer',
                'float' => 'number',
                'bool' => 'boolean',
                'string' => 'string',
                'array' => 'array',
                default => 'string'
            };
        }

        return 'string';
    }
} 