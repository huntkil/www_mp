# 🚀 My Playground - Enhanced PHP Learning Platform

> A comprehensive PHP learning environment with modern architecture, enhanced security, and improved user experience.

## ✨ 주요 개선사항

### 🔒 **보안 강화**
- **세션 보안**: IP 주소, User Agent 검증, 자동 세션 재생성
- **CSRF 보호**: 모든 폼에 CSRF 토큰 적용
- **입력 검증**: 강력한 Validator 클래스로 XSS, SQL Injection 방지
- **에러 처리**: 환경별 적절한 에러 처리 (개발/프로덕션)

### 🏗️ **아키텍처 개선**
- **MVC 패턴**: Controller, Model, View 명확한 분리
- **의존성 주입**: 느슨한 결합으로 테스트 용이성 향상
- **RESTful API**: 표준 HTTP 메소드와 상태 코드 사용
- **라우터 시스템**: 깔끔한 URL 구조와 라우팅

### 📊 **데이터베이스 개선**
- **ORM 기능**: Model 클래스로 데이터베이스 추상화
- **타입 캐스팅**: 자동 데이터 타입 변환
- **관계 처리**: 외래 키와 조인 지원
- **성능 최적화**: 인덱스와 쿼리 최적화

### 🎨 **사용자 경험**
- **반응형 디자인**: 모바일 퍼스트 접근법
- **다크 모드**: CSS 변수 기반 테마 시스템
- **로딩 상태**: 모든 비동기 작업에 로딩 표시
- **에러 페이지**: 사용자 친화적 에러 메시지

## 🛠️ 기술 스택

### Backend
- **PHP 8.2+**: 최신 PHP 기능 활용
- **SQLite/MySQL**: 이중 데이터베이스 지원
- **PDO**: 안전한 데이터베이스 연결
- **Composer**: 의존성 관리 (향후 적용 예정)

### Frontend
- **Tailwind CSS**: 유틸리티 퍼스트 CSS 프레임워크
- **Vanilla JavaScript**: 모듈화된 ES6+ 코드
- **Fetch API**: 현대적인 HTTP 클라이언트
- **Local Storage**: 오프라인 기능 지원

### 개발 도구
- **Git**: 버전 관리
- **PHP CS Fixer**: 코드 스타일 통일
- **PHPUnit**: 단위 테스트 (향후 적용 예정)

## 📁 프로젝트 구조

```
www_mp/
├── api/                          # RESTful API 엔드포인트
│   └── vocabulary.php
├── config/                       # 설정 파일
│   ├── credentials/              # 보안 정보
│   ├── database.sqlite          # SQLite 데이터베이스
│   └── logs/                    # 로그 파일
├── modules/                      # 기능별 모듈
│   ├── learning/                # 학습 도구
│   │   ├── card/               # 카드 슬라이드쇼
│   │   ├── voca/               # 단어장 (개선됨)
│   │   └── inst/               # 단어 롤
│   ├── management/              # 데이터 관리
│   │   ├── crud/               # CRUD 데모 (개선됨)
│   │   └── myhealth/           # 건강 기록
│   └── tools/                   # 유틸리티
│       ├── news/               # 뉴스 검색
│       ├── tour/               # 여행 계획
│       └── box/                # 호흡 훈련
├── system/                      # 핵심 시스템
│   ├── includes/               # 공통 클래스
│   │   ├── Controller.php     # 기본 컨트롤러
│   │   ├── Model.php          # 기본 모델
│   │   ├── Database.php       # 데이터베이스 클래스
│   │   ├── ErrorHandler.php   # 에러 처리
│   │   ├── Validator.php      # 입력 검증
│   │   ├── Router.php         # 라우터
│   │   └── session_security.php # 세션 보안
│   ├── views/                  # 에러 페이지 템플릿
│   └── auth/                   # 인증 시스템
└── resources/                   # 정적 리소스
    ├── css/                    # 스타일시트
    └── uploads/                # 업로드 파일
```

## 🚀 빠른 시작

### 1. 환경 설정

```bash
# 프로젝트 클론
git clone <repository-url>
cd www_mp

# 개발 환경 설정
cp config/credentials/sample.php config/credentials/development.php
# development.php 파일에서 필요한 설정 수정
```

### 2. 데이터베이스 설정

```bash
# SQLite 사용 (개발 환경)
# config/database.sqlite 파일이 자동으로 생성됩니다

# MySQL 사용 (프로덕션 환경)
# database_schema.sql 파일을 실행하여 테이블 생성
```

### 3. 서버 실행

```bash
# PHP 내장 서버로 실행
php -S localhost:8080

# 브라우저에서 접속
open http://localhost:8080
```

## 📚 주요 기능

### 🎓 Learning Modules

#### **Card Slideshow**
- 이미지 자동 슬라이드쇼
- 속도 조절 및 일시정지
- 터치/스와이프 제스처 지원
- 키보드 단축키 (화살표, 스페이스)

#### **Vocabulary Manager** (개선됨)
- 개인 단어장 관리
- 다국어 지원 (영어, 한국어, 일본어 등)
- 난이도별 분류
- 학습 진행도 추적
- 검색 및 필터링
- CSV/JSON 내보내기
- 통계 대시보드

#### **Word Rolls**
- 단어 롤 학습 시스템
- 자동 재생 및 반복

### 🗂️ Management Modules

#### **CRUD Demo** (개선됨)
- MVC 패턴 구현
- 입력 검증 및 에러 처리
- 페이지네이션
- 검색 기능
- RESTful API

#### **Health Tracking**
- 러닝 기록 관리
- 통계 및 차트
- 목표 설정 및 추적

### 🛠️ Tools Modules

#### **News Search**
- NewsAPI 연동
- 국가/카테고리별 필터링
- 실시간 검색
- 북마크 기능

#### **Box Breathing**
- 인터랙티브 호흡 훈련
- 타이머 및 가이드
- 진행도 추적

#### **Family Tour**
- 경주 가족 여행 계획
- 지도 및 일정 관리

### 🔄 배포 자동화

#### **CI/CD 파이프라인**
- GitHub Actions 기반 자동 배포
- 코드 품질 검사 (PHP CS Fixer, PHPStan)
- 자동화된 테스트 실행
- 보안 취약점 스캔
- 환경별 배포 (개발/스테이징/프로덕션)

#### **환경 관리**
- 개발/스테이징/프로덕션 환경 분리
- 환경별 설정 관리
- 데이터베이스 설정 분리
- 로깅 및 에러 처리 설정

#### **데이터베이스 마이그레이션**
- 버전 관리된 스키마 변경
- 롤백 기능 지원
- 배치 단위 마이그레이션
- 마이그레이션 상태 추적

#### **백업 시스템**
- 전체 백업 (데이터베이스 + 파일)
- 자동 백업 정리
- 백업 복원 기능
- 압축 및 압축률 계산

#### **모니터링 시스템**
- 시스템 메트릭 수집 (CPU, 메모리, 디스크)
- 임계값 기반 알림
- 이메일/Slack/Webhook 알림
- 헬스 체크 엔드포인트

### 🚀 추가 기능

#### **API 문서 자동 생성**
- OpenAPI/Swagger 3.0 스펙 지원
- 컨트롤러 자동 분석
- JSON, YAML, HTML 형식 출력
- Swagger UI 통합

#### **캐싱 시스템**
- 다중 드라이버 지원 (파일, Redis, 메모리)
- 태그 기반 캐시 관리
- 캐시 통계 및 모니터링
- 자동 캐시 정리

#### **파일 업로드 관리**
- 이미지 처리 및 최적화
- 썸네일 자동 생성
- 파일 검증 및 보안
- 업로드 통계 관리

#### **고급 검색**
- 전체 텍스트 검색 (FTS5)
- 필터링 및 정렬
- 검색 제안 및 인기 검색어
- 검색 통계 및 분석

#### **모바일 최적화**
- 반응형 디자인
- 터치 제스처 지원
- 이미지 최적화 (WebP, 지연 로딩)
- 오프라인 지원

## 🔌 API 문서

### Vocabulary API

#### 단어 목록 조회
```http
GET /api/vocabulary?page=1&per_page=25&search=hello&sort=created_at DESC
```

#### 단어 추가
```http
POST /api/vocabulary
Content-Type: application/json

{
  "word": "serendipity",
  "meaning": "뜻밖의 발견",
  "example": "Finding that book was pure serendipity.",
  "language": "en",
  "difficulty": "hard"
}
```

#### 단어 수정
```http
PUT /api/vocabulary/{id}
Content-Type: application/json

{
  "word": "serendipity",
  "meaning": "뜻밖의 발견, 우연한 행운",
  "example": "Finding that book was pure serendipity."
}
```

#### 단어 삭제
```http
DELETE /api/vocabulary/{id}
```

#### 학습 상태 토글
```http
PATCH /api/vocabulary/{id}/toggle
```

#### 통계 조회
```http
GET /api/vocabulary/stats
```

#### 단어 내보내기
```http
GET /api/vocabulary/export?format=csv
GET /api/vocabulary/export?format=json
```

## 🔒 보안 기능

### 인증 시스템
- **로그인/회원가입**: `/auth/login`, `/auth/register`
- **프로필 관리**: `/auth/profile`, `/auth/change-password`
- **역할 기반 접근 제어**: 사용자/관리자 권한 분리
- **사용자 상태 관리**: 활성/비활성 계정 관리

### 세션 보안
- IP 주소 검증
- User Agent 검증
- 자동 세션 재생성 (5분마다)
- 세션 타임아웃 (1시간)
- CSRF 토큰 보호

### 입력 검증
- XSS 방지 (HTML 태그 제거)
- SQL Injection 방지 (Prepared Statements)
- CSRF 토큰 검증
- 파일 업로드 보안
- 비밀번호 강도 검증

### 에러 처리
- 개발 환경: 상세 에러 정보
- 프로덕션 환경: 사용자 친화적 메시지
- 구조화된 로그 기록
- 보안 정보 노출 방지

## 🧪 테스트

### 자동화된 테스트 (향후 적용 예정)
```bash
# 단위 테스트 실행
./vendor/bin/phpunit

# 코드 커버리지 확인
./vendor/bin/phpunit --coverage-html coverage/
```

### 수동 테스트
```bash
# 테스트 스크립트 실행
php test.php

# 브라우저에서 테스트
open http://localhost:8080/test.html
```

## 📊 성능 최적화

### 프론트엔드
- CSS/JS 압축 및 최적화
- 이미지 지연 로딩
- 캐싱 전략
- CDN 활용

### 백엔드
- 데이터베이스 인덱스 최적화
- 쿼리 캐싱
- 세션 관리 최적화
- 파일 업로드 최적화

## 🚀 배포

### 개발 환경
```bash
# 로컬 서버 실행
php -S localhost:8080

# 환경 변수 설정
export APP_ENV=development
```

### 프로덕션 환경 (Cafe24)
1. 파일 업로드
2. 데이터베이스 설정
3. 권한 설정
4. 보안 파일 제거

자세한 내용은 [DEPLOYMENT.md](DEPLOYMENT.md) 참조

## 🤝 기여하기

1. Fork the Project
2. Create your Feature Branch (`git checkout -b feature/AmazingFeature`)
3. Commit your Changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the Branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## 📝 라이선스

이 프로젝트는 MIT 라이선스 하에 배포됩니다. 자세한 내용은 [LICENSE](LICENSE) 파일을 참조하세요.

## 📞 연락처

프로젝트 링크: [https://github.com/your-username/my-playground](https://github.com/your-username/my-playground)

---

**My Playground** - PHP 학습과 실무 개발을 위한 완벽한 플랫폼 🎯 