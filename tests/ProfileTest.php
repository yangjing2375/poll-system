<?php

use PHPUnit\Framework\TestCase;

/**
 * 个人中心模块接口用例
 */
class ProfileTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    public function testProfileRequiresLogin(): void
    {
        $res = $this->client->get('/api/get_profile.php');

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('登录', $res['json']['message']);
    }

    public function testProfileReturnsCurrentUser(): void
    {
        $this->client->post('/api/login.php', ['username' => 'alice', 'password' => '123456']);
        $res = $this->client->get('/api/get_profile.php');

        $this->assertSame('success', $res['json']['status']);
        $this->assertSame('alice', $res['json']['data']['username']);
    }

    public function testUpdateProfileRejectsInvalidGender(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/update_profile.php', ['gender' => 'other']);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('性别', $res['json']['message']);
    }

    public function testUpdateProfileRejectsInvalidEmail(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/update_profile.php', ['email' => 'abc']);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('邮箱', $res['json']['message']);
    }

    public function testUpdateProfileRejectsDuplicateUsername(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/update_profile.php', ['username' => 'alice']);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('已存在', $res['json']['message']);
    }

    public function testAvatarUploadRejectsFakeImage(): void
    {
        $this->client->loginNewUser();

        $tmp = tempnam(sys_get_temp_dir(), 'fake_img_') . '.jpg';
        file_put_contents($tmp, 'this is not a real image');
        $res = $this->client->upload('/api/upload_avatar.php', 'avatar', $tmp, 'image/jpeg');
        unlink($tmp);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('格式', $res['json']['message']);
    }
}
