# 🚀 단계별 배포 및 마이그레이션 가이드

## 📦 압축 파일 정보

- **파일명**: `www_mp_deployment.tar.gz`
- **크기**: 약 25MB
- **포함 내용**: 전체 프로젝트 (개발 파일 제외)
- **제외된 파일**: .git, node_modules, tests, 로그 파일

## 🔧 1단계: Cafe24 웹FTP 업로드

### 1.1 웹FTP 접속
1. **Cafe24 웹FTP 접속**
   - URL: https://webftp.cafe24.com/
   - 사용자명과 비밀번호 입력

### 1.2 디렉토리 확인
1. **기본 디렉토리 확인**
   - `/www/` 디렉토리로 이동
   - 기존 `mp` 폴더가 있는지 확인

### 1.3 파일 업로드
1. **압축 파일 업로드**
   - `www_mp_deployment.tar.gz` 파일을 `/www/` 디렉토리에 업로드
   - 업로드 완료까지 대기

### 1.4 압축 해제
1. **기존 폴더 백업** (선택사항)
   - 기존 `mp` 폴더가 있다면 `mp_backup`으로 이름 변경

2. **압축 해제**
   - 업로드한 `www_mp_deployment.tar.gz` 파일 선택
   - "압축풀기" 버튼 클릭
   - 결과: `mp` 폴더 생성

## 🗄️ 2단계: 데이터베이스 설정

### 2.1 데이터베이스 확인
1. **Cafe24 관리자 패널 접속**
   - URL: https://admin.cafe24.com/
   - 데이터베이스 관리 메뉴로 이동

2. **데이터베이스 정보 확인**
   - 데이터베이스명: `[your_db_name]`
   - 사용자명: `[your_db_user]`
   - 비밀번호: `[your_db_password]`
   - 호스트: `localhost`

### 2.2 데이터베이스 설정 파일 수정
1. **설정 파일 위치**
   - `/www/mp/config/credentials/production.php` 파일 수정

2. **데이터베이스 정보 입력**
   ```php
   // Database Credentials
   define('CREDENTIALS_DB_TYPE', 'mysql');
   define('CREDENTIALS_DB_HOST', 'localhost');
   define('CREDENTIALS_DB_USER', '[실제_데이터베이스_사용자명]');
   define('CREDENTIALS_DB_PASS', '[실제_데이터베이스_비밀번호]');
   define('CREDENTIALS_DB_NAME', '[실제_데이터베이스명]');
   ```

### 2.3 데이터베이스 테이블 생성
1. **브라우저에서 접속**
   ```
   https://gukho.net/mp/db_setup.php
   ```

2. **생성되는 테이블 확인**
   - `myUser` - 사용자 관리 테이블
   - `myInfo` - CRUD 데모 테이블
   - `health_records` - 건강 기록 테이블
   - `vocabulary` - 어휘 관리 테이블

## 🔒 3단계: 보안 설정

### 3.1 관리자 계정 설정
1. **기본 관리자 계정**
   - ID: `admin`
   - PW: `admin123`

2. **비밀번호 변경** (중요!)
   - 로그인 후 즉시 비밀번호 변경
   - 안전한 비밀번호로 설정

### 3.2 보안 파일 제거
1. **개발용 파일 삭제**
   - `/www/mp/db_setup.php` 삭제
   - `/www/mp/db_check.php` 삭제 (있다면)
   - `/www/mp/config/credentials/sample.php` 삭제

### 3.3 파일 권한 설정
1. **필요한 디렉토리 권한 설정**
   - `/www/mp/system/uploads/` → 755
   - `/www/mp/system/cache/` → 755
   - `/www/mp/system/logs/` → 755
   - `/www/mp/config/` → 644

## 🧪 4단계: 기능 테스트

### 4.1 기본 접속 테스트
1. **메인 페이지 접속**
   ```
   https://gukho.net/mp/
   ```
   - 페이지가 정상적으로 로딩되는지 확인

2. **헬스 체크**
   ```
   https://gukho.net/mp/health.php
   ```
   - 시스템 상태 확인

### 4.2 인증 시스템 테스트
1. **관리자 로그인**
   - ID: `admin`
   - PW: `admin123`
   - 로그인 성공 확인

2. **로그아웃 테스트**
   - 로그아웃 기능 정상 작동 확인

### 4.3 핵심 기능 테스트

#### 학습 모듈
1. **카드 슬라이드쇼**
   ```
   https://gukho.net/mp/modules/learning/card/slideshow.php
   ```
   - 140개 이미지가 정상적으로 로딩되는지 확인
   - 자동 재생, 속도 조절 기능 테스트

2. **단어 카드**
   ```
   https://gukho.net/mp/modules/learning/card/wordcard_ko.php
   https://gukho.net/mp/modules/learning/card/wordcard_en.php
   ```
   - 한국어/영어 단어 카드 정상 작동 확인

3. **어휘 관리**
   ```
   https://gukho.net/mp/modules/learning/voca/voca.php
   ```
   - 단어 추가, 수정, 삭제 기능 테스트

#### 도구 모듈
1. **뉴스 검색**
   ```
   https://gukho.net/mp/modules/tools/news/search_news.php
   ```
   - 뉴스 검색 기능 테스트

2. **가족 여행**
   ```
   https://gukho.net/mp/modules/tools/tour/familytour.php
   ```
   - 여행 계획 도구 테스트

3. **박스 브리딩**
   ```
   https://gukho.net/mp/modules/tools/box/boxbreathe.php
   ```
   - 호흡 훈련 타이머 테스트

#### 관리 모듈
1. **CRUD 데모**
   ```
   https://gukho.net/mp/modules/management/crud/data_list.php
   ```
   - 데이터 생성, 읽기, 수정, 삭제 기능 테스트

2. **건강 기록**
   ```
   https://gukho.net/mp/modules/management/myhealth/health_list.php
   ```
   - 건강 데이터 관리 기능 테스트

## 📊 5단계: 성능 최적화

### 5.1 캐싱 설정
1. **브라우저 캐싱 확인**
   - CSS, JS, 이미지 파일이 캐싱되는지 확인
   - 개발자 도구에서 Network 탭 확인

### 5.2 이미지 최적화 확인
1. **이미지 로딩 속도**
   - 슬라이드쇼 이미지가 빠르게 로딩되는지 확인
   - 모바일에서도 정상 작동하는지 확인

### 5.3 반응형 디자인 확인
1. **모바일 테스트**
   - 모바일 브라우저에서 접속
   - 터치 제스처 정상 작동 확인
   - 반응형 레이아웃 확인

## 🔍 6단계: 오류 모니터링

### 6.1 오류 로그 확인
1. **PHP 오류 로그**
   - Cafe24 관리자 패널에서 오류 로그 확인
   - 주요 오류가 있는지 점검

### 6.2 사용자 피드백 수집
1. **기능 테스트**
   - 모든 주요 기능이 정상 작동하는지 확인
   - 사용자 경험이 원활한지 확인

## 🚨 7단계: 긴급 대응 준비

### 7.1 롤백 준비
1. **백업 파일 보관**
   - 기존 `mp_backup` 폴더 유지
   - 문제 발생 시 즉시 롤백 가능

### 7.2 연락처 준비
1. **지원 연락처**
   - 개발팀 연락처 준비
   - Cafe24 고객센터: 1544-2020

## 📈 8단계: 배포 완료 확인

### 8.1 최종 체크리스트
- [ ] 메인 페이지 정상 접속
- [ ] 모든 모듈 정상 작동
- [ ] 데이터베이스 연결 성공
- [ ] 보안 설정 완료
- [ ] 성능 최적화 완료
- [ ] 오류 로그 확인 완료

### 8.2 배포 완료 보고
- **배포 일시**: 2024년 12월
- **배포 담당자**: [사용자명]
- **배포 상태**: ✅ 완료
- **사이트 URL**: https://gukho.net/mp/

## 🔄 9단계: 정기 유지보수

### 9.1 일일 점검
- [ ] 시스템 로그 확인
- [ ] 오류 발생 여부 확인
- [ ] 성능 메트릭 확인

### 9.2 주간 점검
- [ ] 보안 업데이트 확인
- [ ] 성능 최적화
- [ ] 사용자 피드백 검토

### 9.3 월간 점검
- [ ] 전체 시스템 점검
- [ ] 보안 감사
- [ ] 성능 분석

---

**배포 완료 후**: 모든 단계가 완료되면 현대화된 MP Learning Platform이 정상적으로 운영됩니다! 🎉

**지원**: 문제 발생 시 개발팀에 문의하거나 이 가이드를 참조하세요. 