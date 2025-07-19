# Cafe24 호스팅 배포 가이드

## 1. 파일 업로드

1. **mp 폴더 전체를 압축(zip)**
2. **cafe24 웹FTP 접속**
   - [웹FTP 바로가기](https://webftp.cafe24.com/)
   - `public_html` 또는 `www` 폴더에 업로드
3. **압축 해제**
   - 업로드한 zip 파일을 선택 → "압축풀기"
   - 결과: `public_html/mp/` 폴더 생성

## 2. DB 상태 확인

1. **브라우저에서 접속**
   ```
   https://gukho.net/mp/db_check.php
   ```

2. **확인 사항**
   - ✅ DB 연결 성공
   - ✅ 기존 테이블 목록
   - ✅ 필요한 테이블 존재 여부

## 3. DB 설정 (필요시)

만약 필요한 테이블이 없다면:

1. **DB 설정 스크립트 실행**
   ```
   https://gukho.net/mp/db_setup.php
   ```

2. **생성되는 테이블**
   - `myUser` - 사용자 관리
   - `myInfo` - CRUD 데모
   - `health_records` - 건강 기록

3. **기본 관리자 계정**
   - ID: `admin`
   - PW: `admin123`

## 4. 권한 설정

웹FTP에서 다음 폴더 권한을 707 또는 755로 설정:
- `mp/resources/uploads/`
- `mp/config/logs/` (있다면)

## 5. 최종 확인

1. **메인 페이지 접속**
   ```
   https://gukho.net/mp/
   ```

2. **로그인 테스트**
   - 관리자 로그인: admin / admin123
   - 일반 사용자 기능 테스트

3. **주요 기능 테스트**
   - CRUD 데모
   - 건강 기록 관리
   - 파일 업로드

## 6. 문제 해결

### DB 연결 오류
- DB 정보 확인: `config_production.php`
- cafe24 DB 관리툴에서 직접 연결 테스트

### 권한 오류
- 웹FTP에서 폴더/파일 권한 변경
- uploads 폴더는 707 권한 필요

### 페이지 오류
- `db_check.php`로 DB 상태 확인
- 에러 로그 확인

## 7. 보안 주의사항

1. **배포 완료 후 삭제할 파일**
   - `db_check.php` (DB 정보 노출 위험)
   - `db_setup.php` (재실행 방지)

2. **관리자 비밀번호 변경**
   - 기본 비밀번호 `admin123`을 안전한 비밀번호로 변경

## 8. 연락처

문제 발생 시 개발자에게 문의하세요. 