# 🚀 배포 가이드

## 📋 배포 방법 개요

MP Learning Platform은 두 가지 배포 방법을 지원합니다:

1. **자동 배포 (CI/CD)**: GitHub Actions를 통한 자동화된 배포
2. **수동 배포**: 로컬 스크립트를 통한 수동 배포

## 🔄 방법 1: 자동 배포 (CI/CD)

### 1.1 GitHub Secrets 설정

GitHub 저장소의 Settings → Secrets and variables → Actions에서 다음 시크릿을 설정:

```
CAFE24_FTP_SERVER = gukho.net
CAFE24_FTP_USERNAME = [FTP 사용자명]
CAFE24_FTP_PASSWORD = [FTP 비밀번호]
```

### 1.2 자동 배포 실행

1. **메인 브랜치에 푸시**:
   ```bash
   git add .
   git commit -m "feat: 배포 준비 완료"
   git push origin main
   ```

2. **GitHub Actions 확인**:
   - GitHub 저장소 → Actions 탭
   - "Deploy to Cafe24" 워크플로우 실행 확인
   - 각 단계별 진행 상황 모니터링

3. **배포 완료 확인**:
   - 모든 단계가 성공적으로 완료되면 자동으로 Cafe24에 배포됨
   - 사이트 접속: https://gukho.net/mp/

### 1.3 CI/CD 파이프라인 단계

1. **코드 품질 검사**
   - PHP CS Fixer로 코드 스타일 검사
   - PHPStan으로 정적 분석
   - 코드 품질 기준 통과 확인

2. **테스트 실행**
   - 통합 테스트 스위트 실행
   - 모든 기능 테스트 통과 확인
   - 성능 및 보안 테스트

3. **보안 검사**
   - 의존성 취약점 스캔
   - 보안 이슈 확인

4. **배포 패키지 생성**
   - 프로덕션 설정 적용
   - 개발용 파일 제거
   - 최적화된 배포 패키지 생성

5. **Cafe24 배포**
   - FTP를 통한 파일 업로드
   - 배포 후 헬스 체크
   - 배포 완료 알림

## 🔧 방법 2: 수동 배포

### 2.1 사전 준비

1. **FTP 정보 확인**:
   - Cafe24 웹FTP 접속 정보
   - FTP 서버: gukho.net
   - 사용자명과 비밀번호 준비

2. **로컬 환경 확인**:
   ```bash
   php -v  # PHP 8.0 이상
   php -m | grep -E "(pdo|ftp|curl|zip)"  # 필수 확장 확인
   ```

### 2.2 수동 배포 실행

1. **배포 스크립트 실행**:
   ```bash
   # 기본 배포
   php scripts/deploy-cafe24.php
   
   # 옵션과 함께 배포
   php scripts/deploy-cafe24.php --skip-tests --ftp-user=username --ftp-pass=password
   ```

2. **배포 과정 모니터링**:
   - 배포 전 검사
   - 백업 생성
   - 테스트 실행
   - 패키지 준비
   - FTP 업로드
   - 배포 후 작업

### 2.3 수동 배포 옵션

```bash
# 백업 건너뛰기
php scripts/deploy-cafe24.php --skip-backup

# 테스트 건너뛰기
php scripts/deploy-cafe24.php --skip-tests

# FTP 정보 직접 입력
php scripts/deploy-cafe24.php --ftp-user=username --ftp-pass=password

# 모든 옵션 조합
php scripts/deploy-cafe24.php --skip-backup --skip-tests --ftp-user=username --ftp-pass=password
```

## 📊 배포 후 확인사항

### 3.1 기본 확인

1. **사이트 접속 테스트**:
   ```
   https://gukho.net/mp/
   ```

2. **헬스 체크**:
   ```
   https://gukho.net/mp/health.php
   ```

3. **로그인 테스트**:
   - 관리자 계정: admin / admin123
   - 일반 사용자 등록/로그인

### 3.2 기능별 확인

1. **학습 모듈**:
   - 카드 슬라이드쇼: https://gukho.net/mp/modules/learning/card/slideshow.php
   - 어휘 관리: https://gukho.net/mp/modules/learning/voca/voca.php

2. **도구 모듈**:
   - 뉴스 검색: https://gukho.net/mp/modules/tools/news/search_news.php
   - 박스 브리딩: https://gukho.net/mp/modules/tools/box/boxbreathe.php

3. **관리 모듈**:
   - CRUD 데모: https://gukho.net/mp/modules/management/crud/data_list.php
   - 건강 기록: https://gukho.net/mp/modules/management/myhealth/health_list.php

### 3.3 성능 확인

1. **페이지 로딩 속도**:
   - 홈페이지: < 2초
   - API 응답: < 1초

2. **데이터베이스 연결**:
   - 연결 성공 확인
   - 쿼리 응답 시간 확인

3. **파일 업로드**:
   - 이미지 업로드 테스트
   - 파일 권한 확인

## 🔒 보안 확인사항

### 4.1 배포 후 보안 점검

1. **개발용 파일 제거 확인**:
   - `db_check.php` 삭제됨
   - `db_setup.php` 삭제됨
   - `debug.php` 삭제됨

2. **설정 파일 보안**:
   - 프로덕션 모드 설정 확인
   - 디버그 모드 비활성화 확인

3. **권한 설정**:
   - 업로드 디렉토리 권한: 755
   - 로그 디렉토리 권한: 755
   - 설정 파일 권한: 644

### 4.2 HTTPS 확인

1. **SSL 인증서**:
   - https://gukho.net/mp/ 접속 확인
   - 브라우저에서 보안 연결 표시 확인

2. **보안 헤더**:
   - HSTS 헤더 확인
   - CSP 헤더 확인

## 🚨 문제 해결

### 5.1 일반적인 문제

1. **FTP 연결 실패**:
   ```
   문제: FTP 서버에 연결할 수 없습니다.
   해결: FTP 정보 확인, 방화벽 설정 확인
   ```

2. **파일 업로드 실패**:
   ```
   문제: 특정 파일 업로드 실패
   해결: 파일 권한 확인, 디스크 공간 확인
   ```

3. **데이터베이스 연결 실패**:
   ```
   문제: 사이트 접속 시 DB 오류
   해결: DB 설정 확인, 테이블 존재 확인
   ```

### 5.2 로그 확인

1. **에러 로그**:
   ```
   위치: system/logs/error.log
   확인: 최근 에러 메시지 확인
   ```

2. **배포 로그**:
   ```
   위치: system/logs/deploy.log
   확인: 배포 과정 로그 확인
   ```

### 5.3 롤백 방법

1. **백업에서 복원**:
   ```bash
   php scripts/backup-restore.php --backup=backup_filename
   ```

2. **이전 버전으로 되돌리기**:
   ```bash
   git checkout HEAD~1
   php scripts/deploy-cafe24.php
   ```

## 📈 모니터링

### 6.1 배포 후 모니터링

1. **시스템 모니터링**:
   - CPU, 메모리 사용률
   - 디스크 사용률
   - 네트워크 트래픽

2. **애플리케이션 모니터링**:
   - 응답 시간
   - 에러율
   - 사용자 활동

3. **로그 모니터링**:
   - 에러 로그
   - 접근 로그
   - 보안 로그

### 6.2 알림 설정

1. **배포 완료 알림**:
   - 이메일 알림
   - Slack 알림
   - 텔레그램 알림

2. **오류 알림**:
   - 에러 발생 시 즉시 알림
   - 성능 저하 시 알림

## 📞 지원

### 7.1 문제 발생 시

1. **로그 확인**:
   - 시스템 로그 확인
   - 애플리케이션 로그 확인

2. **개발자 문의**:
   - 문제 상황 상세 설명
   - 로그 파일 첨부
   - 스크린샷 첨부

3. **호스팅 업체 문의**:
   - Cafe24 고객센터: 1544-2020
   - 기술지원팀 문의

### 7.2 연락처

- **개발팀**: [개발자 이메일]
- **시스템 관리자**: [관리자 이메일]
- **긴급연락처**: [긴급연락처]

---

**배포 완료 후 체크리스트:**

- [ ] 사이트 접속 확인
- [ ] 로그인 기능 테스트
- [ ] 주요 기능 동작 확인
- [ ] 데이터베이스 연결 확인
- [ ] 파일 업로드 테스트
- [ ] 보안 설정 확인
- [ ] 성능 테스트
- [ ] 모니터링 설정 확인 