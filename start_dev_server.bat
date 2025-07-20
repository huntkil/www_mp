@echo off
echo 🚀 MP Learning Platform 개발 서버를 시작합니다...
echo.

REM PHP 경로 확인
where php >nul 2>&1
if %errorlevel% neq 0 (
    echo ❌ PHP가 설치되어 있지 않습니다.
    echo.
    echo 📥 PHP 설치 방법:
    echo    1. XAMPP 설치: https://www.apachefriends.org/download.html
    echo    2. 또는 PHP standalone: https://windows.php.net/download/
    echo.
    echo 💡 XAMPP 사용 시 PHP 경로: C:\xampp\php\php.exe
    echo.
    pause
    exit /b 1
)

REM PHP 버전 확인
php -v
echo.

REM 개발 환경 설정 확인
if not exist "config\credentials\development.php" (
    echo ❌ 개발 환경 설정 파일이 없습니다.
    echo.
    echo 🔧 설정 방법:
    echo    config\credentials\sample.php를 development.php로 복사하세요.
    echo.
    pause
    exit /b 1
)

REM 데이터베이스 파일 확인
if not exist "config\database.sqlite" (
    echo 📁 SQLite 데이터베이스 파일을 생성합니다...
    echo. > config\database.sqlite
)

REM 개발 서버 시작
echo 🌐 개발 서버를 시작합니다...
echo 📍 접속 주소: http://localhost:8080
echo 📍 관리자 계정: admin / admin123
echo.
echo ⚠️  서버를 중지하려면 Ctrl+C를 누르세요.
echo.

php -S localhost:8080

pause 