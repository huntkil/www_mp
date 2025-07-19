<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>로그인 - MP Learning</title>
    <link href="/resources/css/tailwind.css" rel="stylesheet">
    <style>
        .auth-container {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        .auth-card {
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
        }
        .form-input:focus {
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
    </style>
</head>
<body class="auth-container">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="auth-card max-w-md w-full space-y-8 rounded-xl shadow-2xl p-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    로그인
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    또는
                    <a href="/auth/register" class="font-medium text-indigo-600 hover:text-indigo-500">
                        새 계정 만들기
                    </a>
                </p>
            </div>
            
            <form class="mt-8 space-y-6" id="loginForm" method="POST" action="/auth/login">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            사용자명
                        </label>
                        <input id="username" name="username" type="text" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="사용자명을 입력하세요">
                        <div id="username-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="비밀번호를 입력하세요">
                        <div id="password-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember_me" type="checkbox" 
                               class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-900">
                            로그인 상태 유지
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="/auth/forgot-password" class="font-medium text-indigo-600 hover:text-indigo-500">
                            비밀번호를 잊으셨나요?
                        </a>
                    </div>
                </div>

                <div>
                    <button type="submit" id="loginBtn"
                            class="btn-primary group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        로그인
                    </button>
                </div>

                <div id="form-error" class="hidden bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded relative" role="alert">
                    <span id="form-error-message"></span>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('loginForm');
            const loginBtn = document.getElementById('loginBtn');
            const formError = document.getElementById('form-error');
            const formErrorMessage = document.getElementById('form-error-message');

            // 폼 제출 처리
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // 기존 에러 메시지 초기화
                clearErrors();
                
                // 버튼 상태 변경
                setLoading(true);
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch('/auth/login', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // 성공 시 리다이렉트
                        window.location.href = result.redirect || '/';
                    } else {
                        // 에러 처리
                        showError(result.message || '로그인에 실패했습니다.');
                        
                        // 필드별 에러 표시
                        if (result.errors) {
                            Object.keys(result.errors).forEach(field => {
                                showFieldError(field, result.errors[field]);
                            });
                        }
                    }
                } catch (error) {
                    console.error('Login error:', error);
                    showError('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                } finally {
                    setLoading(false);
                }
            });

            // 에러 메시지 초기화
            function clearErrors() {
                formError.classList.add('hidden');
                document.querySelectorAll('[id$="-error"]').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
            }

            // 전체 폼 에러 표시
            function showError(message) {
                formErrorMessage.textContent = message;
                formError.classList.remove('hidden');
            }

            // 필드별 에러 표시
            function showFieldError(field, message) {
                const errorEl = document.getElementById(field + '-error');
                if (errorEl) {
                    errorEl.textContent = message;
                    errorEl.classList.remove('hidden');
                }
            }

            // 로딩 상태 설정
            function setLoading(loading) {
                if (loading) {
                    loginBtn.disabled = true;
                    loginBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        로그인 중...
                    `;
                } else {
                    loginBtn.disabled = false;
                    loginBtn.innerHTML = `
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        로그인
                    `;
                }
            }
        });
    </script>
</body>
</html> 