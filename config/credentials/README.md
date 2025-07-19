# Credentials 관리 가이드

## 📋 개요

이 폴더는 프로젝트의 민감한 정보들(API 키, 데이터베이스 자격증명 등)을 관리하기 위한 곳입니다.

## 🔐 보안 규칙

- **절대 실제 credentials 파일을 Git에 커밋하지 마세요**
- 오직 `sample.php` 파일만 Git에 포함됩니다
- 각 환경별 credentials 파일들은 `.gitignore`에 의해 제외됩니다

## 📁 파일 구조

```
config/credentials/
├── README.md              # 이 파일 (가이드)
├── sample.php             # 샘플 템플릿 (Git에 포함)
├── loader.php             # 자동 로더 (Git에 포함)
├── development.php        # 개발 환경 (Git에서 제외)
├── production.php         # 프로덕션 환경 (Git에서 제외)
├── local.php              # 로컬 환경 (Git에서 제외)
└── staging.php            # 스테이징 환경 (Git에서 제외)
```

## 🚀 빠른 시작

### 1. 개발 환경 설정

```bash
# 프로젝트 클론 후
cd my_www/config/credentials/

# 개발 환경용 credentials 파일 생성
cp sample.php development.php

# 파일 편집
nano development.php  # 또는 선호하는 에디터 사용
```

### 2. 실제 값 설정

`development.php` 파일을 열고 다음 값들을 설정하세요:

```php
// API Keys
define('CREDENTIALS_NEWS_API_KEY', 'your_actual_news_api_key');
define('CREDENTIALS_OPENAI_API_KEY', 'your_actual_openai_api_key');

// Database (로컬 개발용)
define('CREDENTIALS_DB_TYPE', 'sqlite');
define('CREDENTIALS_DB_FILE', __DIR__ . '/../database.sqlite');

// Admin 계정
define('CREDENTIALS_ADMIN_USERNAME', 'admin');
define('CREDENTIALS_ADMIN_PASSWORD', 'secure_password');
```

### 3. 프로덕션 환경 설정

```bash
# 서버에서
cp sample.php production.php

# 프로덕션 값들로 설정
# - 강력한 비밀번호 사용
# - 실제 데이터베이스 정보 입력
# - 모든 API 키 설정
```

## 🔧 사용 방법

### 자동 로드

프로젝트는 자동으로 환경을 감지하여 적절한 credentials 파일을 로드합니다:

```php
// 자동으로 로드됨 (config.php에서)
require_once __DIR__ . '/config/credentials/loader.php';

// 이후 상수들 사용 가능
$apiKey = NEWS_API_KEY;
$dbHost = DB_HOST;
```

### 환경 감지 규칙

- **개발 환경**: `localhost`, `127.0.0.1`, 포트 `8080`
- **프로덕션 환경**: 기타 모든 환경

### 수동 환경 설정

환경을 수동으로 지정하려면:

```bash
# 환경 변수 설정
export APP_ENV=development
# 또는
export APP_ENV=production
```

## 📋 필수 설정 항목

### API Keys
- `CREDENTIALS_NEWS_API_KEY`: [NewsAPI](https://newsapi.org/) 키
- `CREDENTIALS_OPENAI_API_KEY`: OpenAI API 키

### 데이터베이스
- `CREDENTIALS_DB_TYPE`: `sqlite` 또는 `mysql`
- `CREDENTIALS_DB_HOST`: 데이터베이스 호스트
- `CREDENTIALS_DB_USER`: 데이터베이스 사용자명
- `CREDENTIALS_DB_PASS`: 데이터베이스 비밀번호
- `CREDENTIALS_DB_NAME`: 데이터베이스 이름

### 보안
- `CREDENTIALS_ADMIN_USERNAME`: 관리자 계정명
- `CREDENTIALS_ADMIN_PASSWORD`: 관리자 비밀번호
- `CREDENTIALS_HASH_COST`: 비밀번호 해시 비용 (12 권장)

## 🚨 문제 해결

### "Credentials file not found" 오류

```bash
# 1. 적절한 환경 파일이 있는지 확인
ls -la config/credentials/

# 2. 없다면 sample.php를 복사
cp config/credentials/sample.php config/credentials/development.php

# 3. 내용 수정
nano config/credentials/development.php
```

### 환경 감지 문제

```php
// 디버깅용 - 현재 환경 확인
echo "Current environment: " . CREDENTIALS_ENV;
```

### 권한 문제

```bash
# 파일 권한 확인
chmod 600 config/credentials/*.php
```

## 📚 API 키 획득 방법

### News API
1. [NewsAPI.org](https://newsapi.org/register) 방문
2. 무료 계정 생성
3. API 키 복사하여 `CREDENTIALS_NEWS_API_KEY`에 설정

### OpenAI API
1. [OpenAI Platform](https://platform.openai.com/api-keys) 방문
2. API 키 생성
3. `CREDENTIALS_OPENAI_API_KEY`에 설정

## 🔄 배포 프로세스

### 개발 → 스테이징
```bash
# 스테이징 서버에서
cp sample.php staging.php
# 스테이징 환경에 맞게 수정
```

### 스테이징 → 프로덕션
```bash
# 프로덕션 서버에서
cp sample.php production.php
# 프로덕션 환경에 맞게 수정
```

## ⚠️ 주의사항

1. **절대 실제 credentials를 Git에 커밋하지 마세요**
2. **비밀번호는 강력하게 설정하세요**
3. **API 키는 정기적으로 갱신하세요**
4. **로컬 개발 시에만 약한 비밀번호 사용 가능**
5. **프로덕션에서는 환경 변수 사용을 고려하세요**

## 🆘 도움이 필요한 경우

1. `sample.php` 파일 참조
2. 프로젝트 README 문서 확인
3. 팀 개발자에게 문의

---

**기억하세요**: 보안은 모든 개발자의 책임입니다! 🔐 