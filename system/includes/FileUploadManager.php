<?php

namespace System\Includes;

use PDO;

/**
 * 파일 업로드 관리 시스템
 * 파일 업로드, 검증, 처리, 저장을 관리하는 시스템
 */
class FileUploadManager
{
    private array $config;
    private PDO $pdo;
    private Logger $logger;
    private string $uploadPath;
    private array $allowedTypes;
    private int $maxFileSize;

    public function __construct(PDO $pdo, array $config = [])
    {
        $this->pdo = $pdo;
        $this->config = array_merge([
            'upload_path' => __DIR__ . '/../../system/uploads/',
            'max_file_size' => 10 * 1024 * 1024, // 10MB
            'allowed_types' => [
                'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
                'document' => ['pdf', 'doc', 'docx', 'txt'],
                'video' => ['mp4', 'avi', 'mov'],
                'audio' => ['mp3', 'wav', 'ogg']
            ],
            'image_processing' => [
                'enabled' => true,
                'max_width' => 1920,
                'max_height' => 1080,
                'quality' => 85,
                'create_thumbnails' => true,
                'thumbnail_size' => [150, 150]
            ],
            'storage' => [
                'driver' => 'local', // local, s3, ftp
                'organize_by_date' => true,
                'generate_unique_names' => true
            ]
        ], $config);

        $this->uploadPath = $this->config['upload_path'];
        $this->allowedTypes = $this->config['allowed_types'];
        $this->maxFileSize = $this->config['max_file_size'];
        $this->logger = new Logger('file_upload');

        $this->initializeUploadDirectory();
        $this->createUploadTable();
    }

    /**
     * 업로드 디렉토리 초기화
     */
    private function initializeUploadDirectory(): void
    {
        if (!is_dir($this->uploadPath)) {
            mkdir($this->uploadPath, 0755, true);
        }

        // 하위 디렉토리 생성
        $subdirs = ['images', 'documents', 'videos', 'audio', 'temp'];
        foreach ($subdirs as $dir) {
            $path = $this->uploadPath . $dir;
            if (!is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }

    /**
     * 업로드 테이블 생성
     */
    private function createUploadTable(): void
    {
        $sql = "CREATE TABLE IF NOT EXISTS file_uploads (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            original_name VARCHAR(255) NOT NULL,
            stored_name VARCHAR(255) NOT NULL,
            file_path VARCHAR(500) NOT NULL,
            file_size INTEGER NOT NULL,
            mime_type VARCHAR(100) NOT NULL,
            file_type VARCHAR(50) NOT NULL,
            extension VARCHAR(10) NOT NULL,
            width INTEGER NULL,
            height INTEGER NULL,
            duration INTEGER NULL,
            uploaded_by INTEGER NULL,
            upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_processed BOOLEAN DEFAULT 0,
            processing_status VARCHAR(50) DEFAULT 'pending',
            metadata TEXT NULL,
            FOREIGN KEY (uploaded_by) REFERENCES users(id)
        )";
        
        $this->pdo->exec($sql);
    }

    /**
     * 파일 업로드 처리
     */
    public function upload(array $file, int $userId = null): array
    {
        try {
            $this->logger->info('File upload started', [
                'original_name' => $file['name'],
                'size' => $file['size']
            ]);

            // 파일 검증
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                return [
                    'success' => false,
                    'error' => $validation['error']
                ];
            }

            // 파일 정보 추출
            $fileInfo = $this->extractFileInfo($file);
            
            // 저장 경로 생성
            $storagePath = $this->generateStoragePath($fileInfo);
            
            // 파일 저장
            $storedName = $this->storeFile($file, $storagePath);
            
            // 데이터베이스에 기록
            $uploadId = $this->saveUploadRecord($fileInfo, $storedName, $storagePath, $userId);
            
            // 파일 처리 (이미지 리사이징, 썸네일 생성 등)
            $processingResult = $this->processFile($uploadId, $fileInfo, $storagePath);
            
            $result = [
                'success' => true,
                'upload_id' => $uploadId,
                'file_info' => $fileInfo,
                'storage_path' => $storagePath,
                'stored_name' => $storedName,
                'processing' => $processingResult
            ];

            $this->logger->info('File upload completed', [
                'upload_id' => $uploadId,
                'file_name' => $fileInfo['original_name']
            ]);

            return $result;

        } catch (\Exception $e) {
            $this->logger->error('File upload failed', [
                'error' => $e->getMessage(),
                'file_name' => $file['name'] ?? 'unknown'
            ]);

            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * 파일 검증
     */
    private function validateFile(array $file): array
    {
        // 기본 업로드 오류 확인
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessage = $this->getUploadErrorMessage($file['error']);
            return ['valid' => false, 'error' => $errorMessage];
        }

        // 파일 크기 확인
        if ($file['size'] > $this->maxFileSize) {
            return [
                'valid' => false,
                'error' => "File size exceeds maximum allowed size of " . $this->formatBytes($this->maxFileSize)
            ];
        }

        // 파일 타입 확인
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $mimeType = $file['type'];
        
        if (!$this->isAllowedType($extension, $mimeType)) {
            return [
                'valid' => false,
                'error' => "File type '{$extension}' is not allowed"
            ];
        }

        // 파일 내용 검증
        if (!$this->validateFileContent($file)) {
            return [
                'valid' => false,
                'error' => 'File content validation failed'
            ];
        }

        return ['valid' => true];
    }

    /**
     * 업로드 오류 메시지 가져오기
     */
    private function getUploadErrorMessage(int $errorCode): string
    {
        return match($errorCode) {
            UPLOAD_ERR_INI_SIZE => 'File size exceeds PHP upload_max_filesize',
            UPLOAD_ERR_FORM_SIZE => 'File size exceeds form MAX_FILE_SIZE',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload',
            default => 'Unknown upload error'
        };
    }

    /**
     * 허용된 파일 타입인지 확인
     */
    private function isAllowedType(string $extension, string $mimeType): bool
    {
        foreach ($this->allowedTypes as $category => $extensions) {
            if (in_array($extension, $extensions)) {
                return true;
            }
        }
        return false;
    }

    /**
     * 파일 내용 검증
     */
    private function validateFileContent(array $file): bool
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        
        // 이미지 파일 검증
        if (in_array($extension, $this->allowedTypes['image'])) {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo === false) {
                return false;
            }
        }

        // 실행 파일 검증
        $dangerousExtensions = ['exe', 'bat', 'cmd', 'com', 'pif', 'scr', 'vbs'];
        if (in_array($extension, $dangerousExtensions)) {
            return false;
        }

        return true;
    }

    /**
     * 파일 정보 추출
     */
    private function extractFileInfo(array $file): array
    {
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $fileType = $this->getFileType($extension);
        
        $info = [
            'original_name' => $file['name'],
            'file_size' => $file['size'],
            'mime_type' => $file['type'],
            'extension' => $extension,
            'file_type' => $fileType
        ];

        // 이미지 파일인 경우 크기 정보 추가
        if ($fileType === 'image') {
            $imageInfo = getimagesize($file['tmp_name']);
            if ($imageInfo) {
                $info['width'] = $imageInfo[0];
                $info['height'] = $imageInfo[1];
            }
        }

        return $info;
    }

    /**
     * 파일 타입 결정
     */
    private function getFileType(string $extension): string
    {
        foreach ($this->allowedTypes as $type => $extensions) {
            if (in_array($extension, $extensions)) {
                return $type;
            }
        }
        return 'unknown';
    }

    /**
     * 저장 경로 생성
     */
    private function generateStoragePath(array $fileInfo): string
    {
        $basePath = $this->uploadPath . $fileInfo['file_type'] . '/';
        
        if ($this->config['storage']['organize_by_date']) {
            $datePath = date('Y/m/d/');
            $basePath .= $datePath;
            
            if (!is_dir($basePath)) {
                mkdir($basePath, 0755, true);
            }
        }
        
        return $basePath;
    }

    /**
     * 파일 저장
     */
    private function storeFile(array $file, string $storagePath): string
    {
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        
        if ($this->config['storage']['generate_unique_names']) {
            $storedName = uniqid() . '_' . time() . '.' . $extension;
        } else {
            $storedName = $this->sanitizeFilename($file['name']);
        }
        
        $fullPath = $storagePath . $storedName;
        
        if (!move_uploaded_file($file['tmp_name'], $fullPath)) {
            throw new \Exception('Failed to move uploaded file');
        }
        
        return $storedName;
    }

    /**
     * 파일명 정리
     */
    private function sanitizeFilename(string $filename): string
    {
        // 특수문자 제거
        $filename = preg_replace('/[^a-zA-Z0-9._-]/', '_', $filename);
        
        // 연속된 언더스코어 제거
        $filename = preg_replace('/_+/', '_', $filename);
        
        // 앞뒤 언더스코어 제거
        $filename = trim($filename, '_');
        
        return $filename;
    }

    /**
     * 업로드 기록 저장
     */
    private function saveUploadRecord(array $fileInfo, string $storedName, string $storagePath, ?int $userId): int
    {
        $sql = "INSERT INTO file_uploads (
            original_name, stored_name, file_path, file_size, mime_type, 
            file_type, extension, width, height, uploaded_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $fileInfo['original_name'],
            $storedName,
            $storagePath,
            $fileInfo['file_size'],
            $fileInfo['mime_type'],
            $fileInfo['file_type'],
            $fileInfo['extension'],
            $fileInfo['width'] ?? null,
            $fileInfo['height'] ?? null,
            $userId
        ]);
        
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * 파일 처리
     */
    private function processFile(int $uploadId, array $fileInfo, string $storagePath): array
    {
        $result = [
            'processed' => false,
            'thumbnails' => [],
            'resized' => false
        ];

        // 이미지 파일 처리
        if ($fileInfo['file_type'] === 'image' && $this->config['image_processing']['enabled']) {
            $imageProcessor = new ImageProcessor($this->config['image_processing']);
            $processingResult = $imageProcessor->process($storagePath . $fileInfo['stored_name']);
            
            if ($processingResult['success']) {
                $result['processed'] = true;
                $result['thumbnails'] = $processingResult['thumbnails'] ?? [];
                $result['resized'] = $processingResult['resized'] ?? false;
                
                // 처리 결과를 데이터베이스에 업데이트
                $this->updateProcessingStatus($uploadId, 'completed', $processingResult);
            }
        }

        return $result;
    }

    /**
     * 처리 상태 업데이트
     */
    private function updateProcessingStatus(int $uploadId, string $status, array $metadata = []): void
    {
        $sql = "UPDATE file_uploads SET 
                is_processed = 1, 
                processing_status = ?, 
                metadata = ? 
                WHERE id = ?";
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            $status,
            json_encode($metadata),
            $uploadId
        ]);
    }

    /**
     * 업로드된 파일 목록 가져오기
     */
    public function getUploads(array $filters = [], int $limit = 50, int $offset = 0): array
    {
        $where = [];
        $params = [];
        
        if (isset($filters['file_type'])) {
            $where[] = 'file_type = ?';
            $params[] = $filters['file_type'];
        }
        
        if (isset($filters['uploaded_by'])) {
            $where[] = 'uploaded_by = ?';
            $params[] = $filters['uploaded_by'];
        }
        
        if (isset($filters['date_from'])) {
            $where[] = 'upload_date >= ?';
            $params[] = $filters['date_from'];
        }
        
        if (isset($filters['date_to'])) {
            $where[] = 'upload_date <= ?';
            $params[] = $filters['date_to'];
        }
        
        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
        
        $sql = "SELECT * FROM file_uploads {$whereClause} ORDER BY upload_date DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 파일 정보 가져오기
     */
    public function getFile(int $uploadId): ?array
    {
        $sql = "SELECT * FROM file_uploads WHERE id = ?";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$uploadId]);
        
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    /**
     * 파일 삭제
     */
    public function deleteFile(int $uploadId): bool
    {
        $file = $this->getFile($uploadId);
        if (!$file) {
            return false;
        }
        
        try {
            // 파일 삭제
            $filePath = $file['file_path'] . $file['stored_name'];
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            
            // 썸네일 삭제
            if ($file['metadata']) {
                $metadata = json_decode($file['metadata'], true);
                if (isset($metadata['thumbnails'])) {
                    foreach ($metadata['thumbnails'] as $thumbnail) {
                        if (file_exists($thumbnail)) {
                            unlink($thumbnail);
                        }
                    }
                }
            }
            
            // 데이터베이스에서 삭제
            $sql = "DELETE FROM file_uploads WHERE id = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$uploadId]);
            
            $this->logger->info('File deleted', [
                'upload_id' => $uploadId,
                'file_name' => $file['original_name']
            ]);
            
            return true;
        } catch (\Exception $e) {
            $this->logger->error('File deletion failed', [
                'upload_id' => $uploadId,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * 파일 통계 가져오기
     */
    public function getStats(): array
    {
        $sql = "SELECT 
                    COUNT(*) as total_files,
                    SUM(file_size) as total_size,
                    file_type,
                    COUNT(*) as count
                FROM file_uploads 
                GROUP BY file_type";
        
        $stmt = $this->pdo->query($sql);
        $typeStats = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $totalStats = $this->pdo->query("SELECT COUNT(*) as total, SUM(file_size) as size FROM file_uploads")->fetch(PDO::FETCH_ASSOC);
        
        return [
            'total_files' => $totalStats['total'] ?? 0,
            'total_size' => $totalStats['size'] ?? 0,
            'total_size_formatted' => $this->formatBytes($totalStats['size'] ?? 0),
            'by_type' => $typeStats
        ];
    }

    /**
     * 바이트를 읽기 쉬운 형태로 변환
     */
    private function formatBytes(int $bytes): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        
        return round($bytes, 2) . ' ' . $units[$i];
    }
}

/**
 * 이미지 처리기
 */
class ImageProcessor
{
    private array $config;

    public function __construct(array $config)
    {
        $this->config = $config;
    }

    public function process(string $imagePath): array
    {
        $result = [
            'success' => false,
            'resized' => false,
            'thumbnails' => []
        ];

        try {
            $imageInfo = getimagesize($imagePath);
            if (!$imageInfo) {
                throw new \Exception('Invalid image file');
            }

            $width = $imageInfo[0];
            $height = $imageInfo[1];
            $mimeType = $imageInfo['mime'];

            // 리사이징 필요 여부 확인
            if ($width > $this->config['max_width'] || $height > $this->config['max_height']) {
                $resizedPath = $this->resizeImage($imagePath, $mimeType);
                $result['resized'] = true;
            }

            // 썸네일 생성
            if ($this->config['create_thumbnails']) {
                $thumbnails = $this->createThumbnails($imagePath, $mimeType);
                $result['thumbnails'] = $thumbnails;
            }

            $result['success'] = true;
            return $result;

        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    private function resizeImage(string $imagePath, string $mimeType): string
    {
        $image = $this->loadImage($imagePath, $mimeType);
        
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        // 비율 유지하면서 리사이징
        $ratio = min(
            $this->config['max_width'] / $originalWidth,
            $this->config['max_height'] / $originalHeight
        );
        
        $newWidth = (int) ($originalWidth * $ratio);
        $newHeight = (int) ($originalHeight * $ratio);
        
        $resized = imagecreatetruecolor($newWidth, $newHeight);
        
        // 투명도 유지
        imagealphablending($resized, false);
        imagesavealpha($resized, true);
        
        imagecopyresampled($resized, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
        
        $resizedPath = $imagePath . '_resized' . pathinfo($imagePath, PATHINFO_EXTENSION);
        $this->saveImage($resized, $resizedPath, $mimeType);
        
        imagedestroy($image);
        imagedestroy($resized);
        
        return $resizedPath;
    }

    private function createThumbnails(string $imagePath, string $mimeType): array
    {
        $thumbnails = [];
        $image = $this->loadImage($imagePath, $mimeType);
        
        $originalWidth = imagesx($image);
        $originalHeight = imagesy($image);
        
        $thumbnailSize = $this->config['thumbnail_size'];
        $thumbWidth = $thumbnailSize[0];
        $thumbHeight = $thumbnailSize[1];
        
        // 정사각형 썸네일 생성
        $size = min($originalWidth, $originalHeight);
        $x = ($originalWidth - $size) / 2;
        $y = ($originalHeight - $size) / 2;
        
        $thumbnail = imagecreatetruecolor($thumbWidth, $thumbHeight);
        
        // 투명도 유지
        imagealphablending($thumbnail, false);
        imagesavealpha($thumbnail, true);
        
        imagecopyresampled($thumbnail, $image, 0, 0, $x, $y, $thumbWidth, $thumbHeight, $size, $size);
        
        $thumbnailPath = $imagePath . '_thumb' . pathinfo($imagePath, PATHINFO_EXTENSION);
        $this->saveImage($thumbnail, $thumbnailPath, $mimeType);
        
        $thumbnails[] = $thumbnailPath;
        
        imagedestroy($image);
        imagedestroy($thumbnail);
        
        return $thumbnails;
    }

    private function loadImage(string $imagePath, string $mimeType)
    {
        return match($mimeType) {
            'image/jpeg' => imagecreatefromjpeg($imagePath),
            'image/png' => imagecreatefrompng($imagePath),
            'image/gif' => imagecreatefromgif($imagePath),
            'image/webp' => imagecreatefromwebp($imagePath),
            default => throw new \Exception("Unsupported image type: {$mimeType}")
        };
    }

    private function saveImage($image, string $path, string $mimeType): void
    {
        $quality = $this->config['quality'];
        
        $result = match($mimeType) {
            'image/jpeg' => imagejpeg($image, $path, $quality),
            'image/png' => imagepng($image, $path, (int) ($quality / 10)),
            'image/gif' => imagegif($image, $path),
            'image/webp' => imagewebp($image, $path, $quality),
            default => throw new \Exception("Unsupported image type: {$mimeType}")
        };
        
        if (!$result) {
            throw new \Exception("Failed to save image: {$path}");
        }
    }
} 