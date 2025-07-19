<?php

namespace Tests\Integration;

use Tests\TestCase;
use System\Includes\Auth;

/**
 * 인증 기능 통합 테스트
 */
class AuthTest extends TestCase
{
    private Auth $auth;

    protected function setUp(): void
    {
        parent::setUp();
        $this->auth = new Auth();
    }

    /**
     * 회원가입 테스트
     */
    public function testUserRegistration(): void
    {
        $userData = [
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password123',
            'full_name' => 'Test User'
        ];

        $result = $this->auth->register($userData);

        $this->assertTrue($result['success']);
        $this->assertArrayHasKey('user', $result);
        $this->assertEquals($userData['username'], $result['user']['username']);
        $this->assertEquals($userData['email'], $result['user']['email']);
        $this->assertEquals('user', $result['user']['role']);
        $this->assertEquals('active', $result['user']['status']);
    }

    /**
     * 중복 사용자명 회원가입 실패 테스트
     */
    public function testRegistrationWithDuplicateUsername(): void
    {
        $userData = [
            'username' => 'duplicateuser',
            'email' => 'test1@example.com',
            'password' => 'password123'
        ];

        // 첫 번째 회원가입
        $result1 = $this->auth->register($userData);
        $this->assertTrue($result1['success']);

        // 중복 사용자명으로 회원가입 시도
        $userData['email'] = 'test2@example.com';
        $result2 = $this->auth->register($userData);

        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('사용자명', $result2['message']);
    }

    /**
     * 중복 이메일 회원가입 실패 테스트
     */
    public function testRegistrationWithDuplicateEmail(): void
    {
        $userData = [
            'username' => 'user1',
            'email' => 'duplicate@example.com',
            'password' => 'password123'
        ];

        // 첫 번째 회원가입
        $result1 = $this->auth->register($userData);
        $this->assertTrue($result1['success']);

        // 중복 이메일로 회원가입 시도
        $userData['username'] = 'user2';
        $result2 = $this->auth->register($userData);

        $this->assertFalse($result2['success']);
        $this->assertStringContainsString('이메일', $result2['message']);
    }

    /**
     * 로그인 성공 테스트
     */
    public function testSuccessfulLogin(): void
    {
        // 사용자 생성
        $userData = [
            'username' => 'logintest',
            'email' => 'login@example.com',
            'password' => 'password123'
        ];
        $this->auth->register($userData);

        // 로그인 시도
        $result = $this->auth->login('logintest', 'password123');

        $this->assertTrue($result['success']);
        $this->assertTrue($this->auth->isLoggedIn());
        $this->assertEquals('logintest', $this->auth->getCurrentUser()['username']);
    }

    /**
     * 잘못된 비밀번호로 로그인 실패 테스트
     */
    public function testLoginWithWrongPassword(): void
    {
        // 사용자 생성
        $userData = [
            'username' => 'wrongpass',
            'email' => 'wrongpass@example.com',
            'password' => 'password123'
        ];
        $this->auth->register($userData);

        // 잘못된 비밀번호로 로그인 시도
        $result = $this->auth->login('wrongpass', 'wrongpassword');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->auth->isLoggedIn());
        $this->assertStringContainsString('비밀번호', $result['message']);
    }

    /**
     * 존재하지 않는 사용자로 로그인 실패 테스트
     */
    public function testLoginWithNonExistentUser(): void
    {
        $result = $this->auth->login('nonexistent', 'password123');

        $this->assertFalse($result['success']);
        $this->assertFalse($this->auth->isLoggedIn());
        $this->assertStringContainsString('사용자', $result['message']);
    }

    /**
     * 로그아웃 테스트
     */
    public function testLogout(): void
    {
        // 사용자 생성 및 로그인
        $userData = [
            'username' => 'logouttest',
            'email' => 'logout@example.com',
            'password' => 'password123'
        ];
        $this->auth->register($userData);
        $this->auth->login('logouttest', 'password123');

        $this->assertTrue($this->auth->isLoggedIn());

        // 로그아웃
        $this->auth->logout();

        $this->assertFalse($this->auth->isLoggedIn());
        $this->assertNull($this->auth->getCurrentUser());
    }

    /**
     * 비밀번호 변경 테스트
     */
    public function testPasswordChange(): void
    {
        // 사용자 생성 및 로그인
        $userData = [
            'username' => 'passwordtest',
            'email' => 'password@example.com',
            'password' => 'oldpassword'
        ];
        $this->auth->register($userData);
        $this->auth->login('passwordtest', 'oldpassword');

        // 비밀번호 변경
        $result = $this->auth->changePassword(
            $this->auth->getCurrentUser()['id'],
            'oldpassword',
            'newpassword123'
        );

        $this->assertTrue($result['success']);

        // 새 비밀번호로 로그인 확인
        $this->auth->logout();
        $loginResult = $this->auth->login('passwordtest', 'newpassword123');

        $this->assertTrue($loginResult['success']);
    }

    /**
     * 잘못된 현재 비밀번호로 변경 실패 테스트
     */
    public function testPasswordChangeWithWrongCurrentPassword(): void
    {
        // 사용자 생성 및 로그인
        $userData = [
            'username' => 'wrongpasschange',
            'email' => 'wrongpasschange@example.com',
            'password' => 'correctpassword'
        ];
        $this->auth->register($userData);
        $this->auth->login('wrongpasschange', 'correctpassword');

        // 잘못된 현재 비밀번호로 변경 시도
        $result = $this->auth->changePassword(
            $this->auth->getCurrentUser()['id'],
            'wrongpassword',
            'newpassword123'
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('현재 비밀번호', $result['message']);
    }

    /**
     * 프로필 업데이트 테스트
     */
    public function testProfileUpdate(): void
    {
        // 사용자 생성 및 로그인
        $userData = [
            'username' => 'profiletest',
            'email' => 'profile@example.com',
            'password' => 'password123',
            'full_name' => 'Original Name'
        ];
        $this->auth->register($userData);
        $this->auth->login('profiletest', 'password123');

        // 프로필 업데이트
        $updateData = [
            'email' => 'updated@example.com',
            'full_name' => 'Updated Name'
        ];

        $result = $this->auth->updateProfile(
            $this->auth->getCurrentUser()['id'],
            $updateData
        );

        $this->assertTrue($result['success']);
        $this->assertEquals('updated@example.com', $result['user']['email']);
        $this->assertEquals('Updated Name', $result['user']['full_name']);
    }

    /**
     * 사용자 상태 변경 테스트
     */
    public function testUserStatusChange(): void
    {
        // 사용자 생성
        $userData = [
            'username' => 'statustest',
            'email' => 'status@example.com',
            'password' => 'password123'
        ];
        $this->auth->register($userData);

        $user = $this->auth->getUserByUsername('statustest');
        $this->assertEquals('active', $user['status']);

        // 상태를 비활성화로 변경
        $result = $this->auth->updateUserStatus($user['id'], 'inactive');
        $this->assertTrue($result['success']);

        $updatedUser = $this->auth->getUserByUsername('statustest');
        $this->assertEquals('inactive', $updatedUser['status']);

        // 비활성화된 사용자로 로그인 시도
        $loginResult = $this->auth->login('statustest', 'password123');
        $this->assertFalse($loginResult['success']);
    }

    /**
     * 사용자 역할 변경 테스트
     */
    public function testUserRoleChange(): void
    {
        // 사용자 생성
        $userData = [
            'username' => 'roletest',
            'email' => 'role@example.com',
            'password' => 'password123'
        ];
        $this->auth->register($userData);

        $user = $this->auth->getUserByUsername('roletest');
        $this->assertEquals('user', $user['role']);

        // 역할을 관리자로 변경
        $result = $this->auth->updateUserRole($user['id'], 'admin');
        $this->assertTrue($result['success']);

        $updatedUser = $this->auth->getUserByUsername('roletest');
        $this->assertEquals('admin', $updatedUser['role']);
    }

    /**
     * 사용자 목록 조회 테스트
     */
    public function testGetUsersList(): void
    {
        // 여러 사용자 생성
        $users = [
            ['username' => 'user1', 'email' => 'user1@example.com', 'password' => 'password123'],
            ['username' => 'user2', 'email' => 'user2@example.com', 'password' => 'password123'],
            ['username' => 'user3', 'email' => 'user3@example.com', 'password' => 'password123']
        ];

        foreach ($users as $userData) {
            $this->auth->register($userData);
        }

        // 사용자 목록 조회
        $result = $this->auth->getUsers(['page' => 1, 'limit' => 10]);

        $this->assertTrue($result['success']);
        $this->assertGreaterThanOrEqual(3, count($result['users']));
        $this->assertArrayHasKey('pagination', $result);
    }

    /**
     * 사용자 검색 테스트
     */
    public function testSearchUsers(): void
    {
        // 사용자 생성
        $userData = [
            'username' => 'searchtest',
            'email' => 'search@example.com',
            'password' => 'password123',
            'full_name' => 'Search Test User'
        ];
        $this->auth->register($userData);

        // 사용자명으로 검색
        $result = $this->auth->searchUsers('searchtest');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['users']);
        $this->assertEquals('searchtest', $result['users'][0]['username']);

        // 이메일로 검색
        $result = $this->auth->searchUsers('search@example.com');

        $this->assertTrue($result['success']);
        $this->assertCount(1, $result['users']);
        $this->assertEquals('search@example.com', $result['users'][0]['email']);
    }
} 