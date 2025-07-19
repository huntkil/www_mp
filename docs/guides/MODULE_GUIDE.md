# 모듈 사용 가이드

## 📚 학습 모듈 (`modules/learning/`)

### 단어카드 시스템 (`card/`)
- **접속 URL**: `/modules/learning/card/slideshow.php`
- **기능**: 동물 이미지를 활용한 단어 학습
- **데이터**: `modules/learning/card/data/mykeywords.csv`
- **언어**: 영어/한국어 지원
- **주요 파일**:
  - `slideshow.php`: 메인 슬라이드쇼
  - `wordcard_en.php`: 영어 단어카드
  - `wordcard_ko.php`: 한국어 단어카드

### 단어장 관리 (`voca/`)
- **접속 URL**: `/modules/learning/voca/voca.html`
- **기능**: CRUD 기능의 단어장 관리
- **데이터베이스**: `vocabulary` 테이블
- **특징**: 실시간 추가/수정/삭제

### 단어 롤 시스템 (`inst/`)
- **접속 URL**: `/modules/learning/inst/word_rolls.php`
- **기능**: SNS 스타일 단어 표시
- **특징**: 동적 단어 표시

## 🗂️ 관리 모듈 (`modules/management/`)

### CRUD 데모 (`crud/`)
- **접속 URL**: `/modules/management/crud/data_list.php`
- **기능**: MVC 패턴 기반 개인정보 관리
- **구조**: 
  - `models/`: 데이터 모델
  - `views/`: 뷰 템플릿
  - `controllers/`: 컨트롤러
- **특징**: 다크 모드 지원

### 건강 관리 (`myhealth/`)
- **접속 URL**: `/modules/management/myhealth/health_list.php`
- **기능**: 운동 기록 관리
- **인증**: 로그인 필요
- **데이터베이스**: `myhealth` 테이블

## 🛠️ 도구 모듈 (`modules/tools/`)

### 뉴스 검색 (`news/`)
- **접속 URL**: `/modules/tools/news/search_news.php`
- **API**: News API 연동
- **기능**: 국가별 뉴스 검색
- **특징**: 반응형 디자인

### 박스 호흡 트레이너 (`box/`)
- **접속 URL**: `/modules/tools/box/boxbreathe.php`
- **기능**: 4-4-4-4 호흡 패턴 가이드
- **특징**: 시각적 호흡 가이드

### 가족 여행 플래너 (`tour/`)
- **접속 URL**: `/modules/tools/tour/familytour.html`
- **기능**: 경주 4일 여행 계획
- **지도**: 경주 지도 포함

## 🔧 시스템 관리 (`system/`)

### 시스템 상태 확인 (`admin/`)
- **접속 URL**: `/system/admin/system_check.php`
- **기능**: 시스템 진단 및 상태 확인
- **인증**: 관리자 전용

### 사용자 인증 (`auth/`)
- **로그인**: `/system/auth/login.html`
- **로그인 처리**: `/system/auth/login_check.php`
- **로그아웃**: `/system/auth/logout.php`

### 공통 라이브러리 (`includes/`)
- **설정**: `system/includes/config.php`
- **데이터베이스**: `system/includes/Database.php`
- **세션 보안**: `system/includes/session_security.php`

## 📁 리소스 관리 (`resources/`)

### 스타일시트 (`css/`)
- **메인 스타일**: `resources/css/style.css`
- **다크 모드**: `resources/css/style_dark.css`

### 업로드 파일 (`uploads/`)
- **사용자 업로드**: `resources/uploads/`

## ⚙️ 설정 및 배포 (`config/`)

### 배포 도구 (`deploy/`)
- **파일**: `config/deploy/build.php`
- **기능**: 자동 배포 패키지 생성
- **특징**: 버전 관리 포함

### 로그 관리 (`logs/`)
- **에러 로그**: `config/logs/error.log`
- **시스템 로그**: 데이터베이스 저장

## 📝 사용법 요약

1. **메인 페이지**: `index.html`에서 모든 모듈 접근
2. **로그인**: `system/auth/login.html`에서 인증
3. **관리자**: `system/admin/system_check.php`에서 시스템 관리
4. **모니터링**: `config/logs/`에서 로그 확인

## 🔗 모듈 간 연동

- **세션**: 공통 세션 시스템 사용 (`system/includes/session_security.php`)
- **데이터베이스**: 단일 데이터베이스 공유 (`system/includes/Database.php`)
- **스타일**: 공통 CSS 사용 (`resources/css/`)
- **보안**: 공통 보안 설정 적용 (`system/includes/`)

## 🌐 새로운 모듈 구조의 장점

### 📂 체계적인 그룹화
- **논리적 분리**: 기능별로 명확한 그룹화
- **유지보수 용이**: 관련 파일들이 한 곳에 모임
- **확장성**: 새로운 모듈 추가가 쉬움

### 🔍 빠른 탐색
- **19개 → 5개 디렉토리**: 최상위 구조 단순화
- **직관적 이름**: modules, system, resources 등
- **명확한 역할**: 각 디렉토리의 목적이 분명

### 🛡️ 보안 강화
- **시스템 파일 분리**: 중요한 파일들이 system/에 격리
- **리소스 관리**: 업로드 파일과 CSS가 별도 관리
- **설정 중앙화**: 모든 설정이 config/에 집중 