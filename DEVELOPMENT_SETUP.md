# 🚀 개발 환경 설정 가이드

## 📋 개요

이 문서는 MP Learning Platform의 개발 환경을 설정하는 방법을 안내합니다.

## 🛠️ 필수 요구사항

### 1. PHP 설치

#### 옵션 A: XAMPP 설치 (권장)
XAMPP는 PHP, MySQL, Apache를 한 번에 설치할 수 있는 통합 패키지입니다.

1. [XAMPP 다운로드](https://www.apachefriends.org/download.html)
2. Windows용 XAMPP 설치 파일 다운로드
3. 설치 프로그램 실행 및 설치 완료
4. XAMPP Control Panel에서 Apache 시작

#### 옵션 B: PHP Standalone 설치
PHP만 독립적으로 설치하는 방법입니다.

1. [PHP 다운로드](https://windows.php.net/download/)
2. Thread Safe 버전 다운로드 (Apache용)
3. 압축 해제 후 원하는 디렉토리에 설치
4. 환경 변수 PATH에 PHP 디렉토리 추가

### 2. Composer 설치

1. [Composer 다운로드](https://getcomposer.org/download/)
2. Composer-Setup.exe 실행
3. PHP 경로 설정 (XAMPP 사용 시: `C:\xampp\php\php.exe`)
4. 설치 완료 후 터미널에서 `composer --version` 확인

## 🔧 개발 환경 설정

### 1. 프로젝트 클론 및 설정

```bash
# 프로젝트 디렉토리로 이동
cd C:\Users\huntk\workspace\git\www_mp

# 개발 환경 설정 스크립트 실행
php scripts/setup_dev_environment.php
```

### 2. Composer 의존성 설치

```bash
# Composer 의존성 설치
composer install

# 개발 의존성 포함 설치
composer install --dev
```

### 3. 환경 설정 확인

프로젝트의 `config/credentials/development.php` 파일이 올바르게 설정되어 있는지 확인:

- ✅ 데이터베이스 타입: `sqlite`
- ✅ 관리자 계정: `admin` / `admin123`
- ✅ 보안 키: 개발용 값으로 설정됨
- ✅ 디버그 모드: `true`

## 🚀 개발 서버 실행

### 방법 1: PHP 내장 서버 사용 (권장)

```bash
# 프로젝트 루트 디렉토리에서
php -S localhost:8080

# 또는 특정 포트 지정
php -S localhost:3000
```

### 방법 2: XAMPP 사용

1. XAMPP Control Panel에서 Apache 시작
2. 프로젝트를 `C:\xampp\htdocs\mp\` 디렉토리에 복사
3. 브라우저에서 `http://localhost/mp/` 접속

## 🌐 접속 및 테스트

### 기본 접속
- **URL**: http://localhost:8080
- **관리자 계정**: admin / admin123

### 주요 기능 테스트

1. **홈페이지**: http://localhost:8080
2. **학습 모듈**: 
   - 카드 슬라이드쇼: http://localhost:8080/modules/learning/card/slideshow.php
   - 단어 카드: http://localhost:8080/modules/learning/card/wordcard_en.php
   - 어휘 관리: http://localhost:8080/modules/learning/voca/voca.php
3. **도구 모듈**:
   - 뉴스 검색: http://localhost:8080/modules/tools/news/search_news_form.php
   - 박스 브리딩: http://localhost:8080/modules/tools/box/boxbreathe.php
4. **관리 모듈**:
   - CRUD 데모: http://localhost:8080/modules/management/crud/data_list.php
   - 건강 기록: http://localhost:8080/modules/management/myhealth/health_list.php

## 🔍 문제 해결

### PHP 명령어 인식 안됨
```bash
# 환경 변수 확인
echo $PATH

# PHP 경로 추가 (XAMPP 사용 시)
export PATH=$PATH:C:\xampp\php

# 또는 전체 경로로 실행
C:\xampp\php\php.exe -v
```

### 데이터베이스 연결 오류
```bash
# SQLite 파일 권한 확인
ls -la config/database.sqlite

# 권한 수정
chmod 666 config/database.sqlite
```

### Composer 오류
```bash
# Composer 캐시 클리어
composer clear-cache

# 의존성 재설치
composer install --no-cache
```

## 📁 프로젝트 구조

```
www_mp/
├── config/                 # 설정 파일
│   ├── credentials/       # 환경별 인증 정보
│   ├── database.sqlite   # SQLite 데이터베이스
│   └── logs/             # 로그 파일
├── modules/               # 기능별 모듈
│   ├── learning/         # 학습 도구
│   ├── tools/            # 유틸리티
│   └── management/       # 관리 도구
├── system/               # 핵심 시스템
│   ├── includes/         # 공통 클래스
│   └── auth/             # 인증 시스템
├── resources/            # 정적 리소스
│   ├── css/              # 스타일시트
│   └── uploads/          # 업로드 파일
└── scripts/              # 유틸리티 스크립트
```

## 🛡️ 보안 주의사항

### 개발 환경 보안
- 개발용 비밀번호는 프로덕션에서 사용하지 마세요
- `config/credentials/development.php`는 Git에 커밋하지 마세요
- 프로덕션 환경에서는 `production.php`를 사용하세요

### 데이터베이스 보안
- SQLite 파일은 적절한 권한으로 설정하세요
- 프로덕션에서는 MySQL 사용을 권장합니다

## 📚 추가 리소스

- [PHP 공식 문서](https://www.php.net/manual/)
- [Composer 문서](https://getcomposer.org/doc/)
- [SQLite 문서](https://www.sqlite.org/docs.html)
- [XAMPP 문서](https://www.apachefriends.org/docs.html)

## 🆘 지원

문제가 발생하면 다음을 확인하세요:

1. PHP 버전이 8.0.0 이상인지 확인
2. 필요한 PHP 확장이 설치되어 있는지 확인
3. 파일 권한이 올바른지 확인
4. 로그 파일에서 오류 메시지 확인

```bash
# 로그 파일 확인
tail -f config/logs/error.log
``` 