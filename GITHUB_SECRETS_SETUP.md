# GitHub Secrets 설정 가이드

## 🔐 Cafe24 FTP 자격 증명 설정

### 1. GitHub 저장소 접속
1. https://github.com/huntkil/www_mp 접속
2. 저장소가 없다면 새로 생성:
   - "New repository" 클릭
   - Repository name: `www_mp`
   - Description: `MP Learning Platform - Modern PHP Learning Tools`
   - Public 또는 Private 선택
   - **중요**: README, .gitignore, license는 체크하지 마세요!

### 2. GitHub Secrets 설정

#### Settings > Secrets and variables > Actions 접속
1. 저장소 페이지에서 **Settings** 탭 클릭
2. 왼쪽 메뉴에서 **Secrets and variables** 클릭
3. **Actions** 클릭

#### 3개의 Repository secrets 추가

**1️⃣ CAFE24_FTP_SERVER**
- **Name**: `CAFE24_FTP_SERVER`
- **Value**: `huntkil.cafe24.com`
- **설명**: Cafe24 FTP 서버 주소

**2️⃣ CAFE24_FTP_USERNAME**
- **Name**: `CAFE24_FTP_USERNAME`
- **Value**: `huntkil`
- **설명**: Cafe24 FTP 사용자명

**3️⃣ CAFE24_FTP_PASSWORD**
- **Name**: `CAFE24_FTP_PASSWORD`
- **Value**: `kil7310k4!`
- **설명**: Cafe24 FTP 비밀번호

### 3. Secrets 추가 방법

각 Secret을 추가하려면:

1. **"New repository secret"** 버튼 클릭
2. **Name** 필드에 위의 이름 입력
3. **Value** 필드에 위의 값 입력
4. **"Add secret"** 버튼 클릭

### 4. 설정 완료 확인

모든 Secrets가 추가되면 다음과 같이 표시됩니다:
- ✅ CAFE24_FTP_SERVER
- ✅ CAFE24_FTP_USERNAME  
- ✅ CAFE24_FTP_PASSWORD

### 5. 로컬 저장소 푸시

Secrets 설정 후 로컬에서 GitHub로 푸시:

```bash
git push -u origin main
```

### 6. 자동 배포 테스트

1. GitHub 저장소에서 **Actions** 탭 클릭
2. **"Deploy to Cafe24"** 워크플로우 선택
3. **"Run workflow"** 버튼 클릭
4. **"Run workflow"** 클릭하여 배포 시작

### 7. 배포 확인

배포가 완료되면 다음 URL에서 확인:
- 메인 사이트: `https://huntkil.cafe24.com/mp/`
- 헬스 체크: `https://huntkil.cafe24.com/mp/health.php`

## 🔒 보안 주의사항

- ✅ Secrets는 암호화되어 저장됩니다
- ✅ 로그에서 Secrets 값은 `***`로 마스킹됩니다
- ✅ 저장소를 포크해도 Secrets는 공유되지 않습니다
- ⚠️ Secrets 값은 한 번 설정하면 다시 볼 수 없습니다

## 🚨 문제 해결

### FTP 연결 실패
- FTP 서버 주소 확인: `huntkil.cafe24.com`
- 사용자명 확인: `huntkil`
- 비밀번호 확인: `kil7310k4!`
- Cafe24 FTP 설정에서 패시브 모드 활성화

### 배포 실패
- GitHub Actions 로그 확인
- Secrets 값 재확인
- Cafe24 FTP 계정 권한 확인

### 권한 오류
- `/public_html/mp/` 디렉토리 쓰기 권한 확인
- FTP 계정이 해당 디렉토리에 접근 가능한지 확인 