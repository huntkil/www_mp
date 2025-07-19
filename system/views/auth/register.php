<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>회원가입 - MP Learning</title>
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
        .password-strength {
            height: 4px;
            border-radius: 2px;
            transition: all 0.3s ease;
        }
        .strength-weak { background: #ef4444; }
        .strength-medium { background: #f59e0b; }
        .strength-strong { background: #10b981; }
    </style>
</head>
<body class="auth-container">
    <div class="min-h-screen flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
        <div class="auth-card max-w-md w-full space-y-8 rounded-xl shadow-2xl p-8">
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    회원가입
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    또는
                    <a href="/auth/login" class="font-medium text-indigo-600 hover:text-indigo-500">
                        기존 계정으로 로그인
                    </a>
                </p>
            </div>
            
            <form class="mt-8 space-y-6" id="registerForm" method="POST" action="/auth/register">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'] ?? ''); ?>">
                
                <div class="space-y-4">
                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">
                            사용자명 *
                        </label>
                        <input id="username" name="username" type="text" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="사용자명을 입력하세요">
                        <div id="username-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            이메일 *
                        </label>
                        <input id="email" name="email" type="email" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="이메일을 입력하세요">
                        <div id="email-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호 *
                        </label>
                        <input id="password" name="password" type="password" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="비밀번호를 입력하세요">
                        <div class="mt-1">
                            <div class="password-strength" id="passwordStrength"></div>
                            <div class="text-xs text-gray-500 mt-1" id="passwordHint">
                                비밀번호는 최소 8자 이상이어야 합니다
                            </div>
                        </div>
                        <div id="password-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="confirm_password" class="block text-sm font-medium text-gray-700">
                            비밀번호 확인 *
                        </label>
                        <input id="confirm_password" name="confirm_password" type="password" required 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="비밀번호를 다시 입력하세요">
                        <div id="confirm_password-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                    
                    <div>
                        <label for="full_name" class="block text-sm font-medium text-gray-700">
                            실명
                        </label>
                        <input id="full_name" name="full_name" type="text" 
                               class="form-input mt-1 block w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
                               placeholder="실명을 입력하세요 (선택사항)">
                        <div id="full_name-error" class="hidden text-red-600 text-sm mt-1"></div>
                    </div>
                </div>

                <div class="flex items-center">
                    <input id="agree_terms" name="agree_terms" type="checkbox" required
                           class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 rounded">
                    <label for="agree_terms" class="ml-2 block text-sm text-gray-900">
                        <a href="/terms" class="text-indigo-600 hover:text-indigo-500">이용약관</a>과 
                        <a href="/privacy" class="text-indigo-600 hover:text-indigo-500">개인정보처리방침</a>에 동의합니다 *
                    </label>
                </div>

                <div>
                    <button type="submit" id="registerBtn"
                            class="btn-primary group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                            </svg>
                        </span>
                        회원가입
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
            const form = document.getElementById('registerForm');
            const registerBtn = document.getElementById('registerBtn');
            const formError = document.getElementById('form-error');
            const formErrorMessage = document.getElementById('form-error-message');
            const passwordInput = document.getElementById('password');
            const confirmPasswordInput = document.getElementById('confirm_password');
            const passwordStrength = document.getElementById('passwordStrength');
            const passwordHint = document.getElementById('passwordHint');

            // 비밀번호 강도 체크
            passwordInput.addEventListener('input', function() {
                const password = this.value;
                const strength = checkPasswordStrength(password);
                updatePasswordStrength(strength);
            });

            // 비밀번호 확인 체크
            confirmPasswordInput.addEventListener('input', function() {
                const password = passwordInput.value;
                const confirmPassword = this.value;
                
                if (confirmPassword && password !== confirmPassword) {
                    showFieldError('confirm_password', '비밀번호가 일치하지 않습니다.');
                } else {
                    clearFieldError('confirm_password');
                }
            });

            // 폼 제출 처리
            form.addEventListener('submit', async function(e) {
                e.preventDefault();
                
                // 기존 에러 메시지 초기화
                clearErrors();
                
                // 클라이언트 측 유효성 검사
                if (!validateForm()) {
                    return;
                }
                
                // 버튼 상태 변경
                setLoading(true);
                
                try {
                    const formData = new FormData(form);
                    const response = await fetch('/auth/register', {
                        method: 'POST',
                        body: formData
                    });
                    
                    const result = await response.json();
                    
                    if (result.success) {
                        // 성공 시 리다이렉트
                        window.location.href = result.redirect || '/auth/login?registered=1';
                    } else {
                        // 에러 처리
                        showError(result.message || '회원가입에 실패했습니다.');
                        
                        // 필드별 에러 표시
                        if (result.errors) {
                            Object.keys(result.errors).forEach(field => {
                                showFieldError(field, result.errors[field]);
                            });
                        }
                    }
                } catch (error) {
                    console.error('Registration error:', error);
                    showError('네트워크 오류가 발생했습니다. 다시 시도해주세요.');
                } finally {
                    setLoading(false);
                }
            });

            // 비밀번호 강도 체크 함수
            function checkPasswordStrength(password) {
                let score = 0;
                
                if (password.length >= 8) score++;
                if (password.match(/[a-z]/)) score++;
                if (password.match(/[A-Z]/)) score++;
                if (password.match(/[0-9]/)) score++;
                if (password.match(/[^a-zA-Z0-9]/)) score++;
                
                if (score < 3) return 'weak';
                if (score < 5) return 'medium';
                return 'strong';
            }

            // 비밀번호 강도 표시 업데이트
            function updatePasswordStrength(strength) {
                const hints = {
                    weak: '약함 - 더 강한 비밀번호를 사용하세요',
                    medium: '보통 - 더 강한 비밀번호를 권장합니다',
                    strong: '강함 - 좋은 비밀번호입니다!'
                };
                
                passwordStrength.className = `password-strength strength-${strength}`;
                passwordHint.textContent = hints[strength];
            }

            // 폼 유효성 검사
            function validateForm() {
                let isValid = true;
                
                // 사용자명 검사
                const username = document.getElementById('username').value.trim();
                if (username.length < 3) {
                    showFieldError('username', '사용자명은 최소 3자 이상이어야 합니다.');
                    isValid = false;
                }
                
                // 이메일 검사
                const email = document.getElementById('email').value.trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(email)) {
                    showFieldError('email', '유효한 이메일 주소를 입력하세요.');
                    isValid = false;
                }
                
                // 비밀번호 검사
                const password = passwordInput.value;
                if (password.length < 8) {
                    showFieldError('password', '비밀번호는 최소 8자 이상이어야 합니다.');
                    isValid = false;
                }
                
                // 비밀번호 확인 검사
                const confirmPassword = confirmPasswordInput.value;
                if (password !== confirmPassword) {
                    showFieldError('confirm_password', '비밀번호가 일치하지 않습니다.');
                    isValid = false;
                }
                
                return isValid;
            }

            // 에러 메시지 초기화
            function clearErrors() {
                formError.classList.add('hidden');
                document.querySelectorAll('[id$="-error"]').forEach(el => {
                    el.classList.add('hidden');
                    el.textContent = '';
                });
            }

            // 필드별 에러 초기화
            function clearFieldError(field) {
                const errorEl = document.getElementById(field + '-error');
                if (errorEl) {
                    errorEl.classList.add('hidden');
                    errorEl.textContent = '';
                }
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
                    registerBtn.disabled = true;
                    registerBtn.innerHTML = `
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        회원가입 중...
                    `;
                } else {
                    registerBtn.disabled = false;
                    registerBtn.innerHTML = `
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-indigo-500 group-hover:text-indigo-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                            </svg>
                        </span>
                        회원가입
                    `;
                }
            }
        });
    </script>
</body>
</html> 