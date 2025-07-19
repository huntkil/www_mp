# GitHub 자동 배포 설정 가이드

## 1. GitHub 저장소 생성

1. GitHub.com에 로그인
2. "New repository" 클릭
3. 저장소 설정:
   - Repository name: `www_mp`
   - Description: `MP Learning Platform - Modern PHP Learning Tools`
   - Public 또는 Private 선택
   - **중요**: README, .gitignore, license는 체크하지 마세요!

## 2. 로컬 저장소를 GitHub에 연결

GitHub에서 저장소를 생성한 후, 다음 명령어를 실행하세요:

```bash
# GitHub 저장소 URL을 추가 (YOUR_USERNAME을 실제 사용자명으로 변경)
git remote add origin https://github.com/YOUR_USERNAME/www_mp.git

# main 브랜치로 설정
git branch -M main

# GitHub에 푸시
git push -u origin main
```

## 3. GitHub Secrets 설정

GitHub 저장소에서 다음 Secrets를 설정해야 합니다:

### Settings > Secrets and variables > Actions > New repository secret

1. **CAFE24_FTP_SERVER**
   - Value: `your-domain.com` (Cafe24 서버 주소)

2. **CAFE24_FTP_USERNAME**
   - Value: `your-ftp-username`

3. **CAFE24_FTP_PASSWORD**
   - Value: `your-ftp-password`

## 4. 자동 배포 활성화

1. GitHub 저장소에서 "Actions" 탭 클릭
2. "Deploy to Cafe24" 워크플로우 선택
3. "Run workflow" 클릭하여 수동으로 첫 배포 실행

## 5. 배포 확인

배포가 완료되면 다음 URL에서 확인:
- 메인 사이트: `https://your-domain.com/mp/`
- 헬스 체크: `https://your-domain.com/mp/health.php`

## 6. 자동 배포 트리거

이제 `main` 브랜치에 푸시할 때마다 자동으로 배포됩니다:

```bash
# 코드 변경 후
git add .
git commit -m "feat: 새로운 기능 추가"
git push origin main
```

## 문제 해결

### FTP 연결 실패
- FTP 서버 주소, 사용자명, 비밀번호 확인
- Cafe24 FTP 설정에서 패시브 모드 활성화

### 배포 실패
- GitHub Actions 로그 확인
- 테스트 실패 시 코드 수정 후 재푸시

### 권한 오류
- Cafe24 FTP 계정 권한 확인
- 서버 디렉토리 쓰기 권한 확인 