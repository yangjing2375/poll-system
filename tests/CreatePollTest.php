<?php

use PHPUnit\Framework\TestCase;

/**
 * 创建投票模块接口用例
 */
class CreatePollTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    public function testCreatePollSuccess(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', [
            'title'   => '自动化测试投票-' . time(),
            'topic'   => '校园专题',
            'options' => ['选项A', '选项B'],
        ]);

        $this->assertTrue($res['json']['success']);
        $this->assertGreaterThan(0, (int)$res['json']['poll_id']);
    }

    public function testCreatePollRequiresLogin(): void
    {
        $res = $this->client->post('/api/create_poll.php', ['title' => '未登录', 'options' => ['A', 'B']]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('登录', $res['json']['message']);
    }

    public function testCreatePollRejectsEmptyTitle(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', ['title' => '', 'options' => ['A', 'B']]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('标题', $res['json']['message']);
    }

    public function testCreatePollRejectsSingleOption(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', ['title' => '只有一个选项', 'options' => ['A']]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('两个', $res['json']['message']);
    }

    public function testCreatePollRejectsBlankOptions(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', ['title' => '空选项', 'options' => ['', '  ']]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('有效', $res['json']['message']);
    }

    public function testCreatePollRejectsMaxOptionsOverflow(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', [
            'title'        => '多选配置越界',
            'options'      => ['A', 'B'],
            'is_multiple'  => 1,
            'max_options'  => 5,
        ]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('不能超过选项数量', $res['json']['message']);
    }

    public function testCreatePollRejectsPastEndTime(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/create_poll.php', [
            'title'    => '过期投票',
            'options'  => ['A', 'B'],
            'end_time' => '2020-01-01 00:00:00',
        ]);

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('结束时间', $res['json']['message']);
    }
}
