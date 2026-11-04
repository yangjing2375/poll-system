<?php

use PHPUnit\Framework\TestCase;

/**
 * 认证模块接口用例
 */
class AuthTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    private function uniqueName(): string
    {
        return 'u' . substr(uniqid('', true), -8);
    }

    public function testRegisterSuccess(): void
    {
        $name = $this->uniqueName();
        $res  = $this->client->post('/api/register.php', [
            'username' => $name,
            'email'    => $name . '@example.com',
            'password' => '123456',
        ]);

        $this->assertSame(200, $res['code']);
        $this->assertIsArray($res['json'], '响应必须是纯 JSON');
        $this->assertSame('success', $res['json']['status']);
        $this->assertSame($name, $res['json']['data']['username']);
    }

    public function testRegisterRejectsEmptyUsername(): void
    {
        $res = $this->client->post('/api/register.php', [
            'username' => '',
            'email'    => 'empty@example.com',
            'password' => '123456',
        ]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('不能为空', $res['json']['message']);
    }

    public function testRegisterRejectsInvalidEmail(): void
    {
        $res = $this->client->post('/api/register.php', [
            'username' => $this->uniqueName(),
            'email'    => 'not-an-email',
            'password' => '123456',
        ]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('邮箱格式', $res['json']['message']);
    }

    public function testRegisterRejectsShortPassword(): void
    {
        $res = $this->client->post('/api/register.php', [
            'username' => $this->uniqueName(),
            'email'    => $this->uniqueName() . '@example.com',
            'password' => '123',
        ]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('6', $res['json']['message']);
    }

    public function testRegisterRejectsDuplicateUsername(): void
    {
        $res = $this->client->post('/api/register.php', [
            'username' => 'alice',
            'email'    => $this->uniqueName() . '@example.com',
            'password' => '123456',
        ]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('已存在', $res['json']['message']);
    }

    public function testLoginRejectsWrongPassword(): void
    {
        $res = $this->client->post('/api/login.php', ['username' => 'alice', 'password' => 'wrong-pass']);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('密码错误', $res['json']['message']);
    }

    public function testLoginSuccessAndSession(): void
    {
        $res = $this->client->post('/api/login.php', ['username' => 'alice', 'password' => '123456']);
        $this->assertSame('success', $res['json']['status']);
        $this->assertFalse($res['json']['is_admin']);

        $session = $this->client->get('/api/check_session.php');
        $this->assertSame('success', $session['json']['status']);
        $this->assertSame('alice', $session['json']['data']['username']);
    }
}
