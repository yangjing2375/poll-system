<?php

use PHPUnit\Framework\TestCase;

/**
 * 管理端接口用例：权限、分页收敛、统计
 */
class AdminTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    public function testAdminLogin(): void
    {
        $res = $this->client->loginAdmin();

        $this->assertSame('success', $res['json']['status']);
        $this->assertSame('admin', $res['json']['admin']['username']);
    }

    public function testAdminSession(): void
    {
        $this->client->loginAdmin();
        $res = $this->client->get('/api/check_session.php');

        $this->assertSame('success', $res['json']['status']);
        $this->assertTrue($res['json']['is_admin']);
    }

    public function testNegativePageIsClamped(): void
    {
        $this->client->loginAdmin();
        $res = $this->client->get('/api/admin_polls.php?page=-1&limit=10');

        $this->assertSame(200, $res['code'], '负数页码不应返回 500');
        $this->assertSame('success', $res['json']['status']);
        $this->assertSame(1, $res['json']['page']);
    }

    public function testHugeLimitIsClamped(): void
    {
        $this->client->loginAdmin();
        $res = $this->client->get('/api/admin_users.php?limit=99999');

        $this->assertSame('success', $res['json']['status']);
        $this->assertLessThanOrEqual(100, $res['json']['limit']);
        $this->assertLessThanOrEqual(100, count($res['json']['data']));
    }

    public function testStatisticsOverview(): void
    {
        $this->client->loginAdmin();
        $res = $this->client->get('/api/admin_statistics.php?type=overview');

        $this->assertSame('success', $res['json']['status']);
        $this->assertIsInt($res['json']['data']['total_users']);
        $this->assertIsInt($res['json']['data']['total_votes']);
    }

    public function testNonAdminForbidden(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->get('/api/admin_polls.php');

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('权限', $res['json']['message']);
        $this->assertArrayNotHasKey('session', $res['json'], '错误响应不应返回 Session 内容');
    }

    public function testAnonymousForbidden(): void
    {
        $res = $this->client->get('/api/admin_users.php');

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('权限', $res['json']['message']);
    }
}
