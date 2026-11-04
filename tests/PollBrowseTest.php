<?php

use PHPUnit\Framework\TestCase;

/**
 * 投票浏览模块接口用例
 */
class PollBrowseTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    public function testPollListReturnsActivePolls(): void
    {
        $res = $this->client->get('/api/get_polls.php');

        $this->assertSame(200, $res['code']);
        $this->assertTrue($res['json']['success']);
        $this->assertIsArray($res['json']['data']);
        $this->assertGreaterThan(0, count($res['json']['data']));
        $this->assertArrayHasKey('options', $res['json']['data'][0]);
        $this->assertArrayHasKey('has_voted', $res['json']['data'][0]);
    }

    public function testPollListFiltersByKeyword(): void
    {
        $res = $this->client->get('/api/get_polls.php?keyword=' . urlencode('午餐'));

        $this->assertTrue($res['json']['success']);
        $this->assertGreaterThan(0, count($res['json']['data']));
        $this->assertStringContainsString('午餐', $res['json']['data'][0]['title']);
    }

    public function testPollListFiltersByTopic(): void
    {
        $res = $this->client->get('/api/get_polls.php?topic=' . urlencode('社团活动'));

        $this->assertTrue($res['json']['success']);
        $this->assertSame('社团活动', $res['json']['data'][0]['topic']);
    }

    public function testPollListFiltersByHot(): void
    {
        $res = $this->client->get('/api/get_polls.php?is_hot=1');

        $this->assertTrue($res['json']['success']);
        $this->assertGreaterThan(0, count($res['json']['data']));
        foreach ($res['json']['data'] as $poll) {
            $this->assertSame(1, $poll['is_hot']);
        }
    }

    public function testPollResultsStructure(): void
    {
        $this->client->post('/api/login.php', ['username' => 'alice', 'password' => '123456']);
        $res = $this->client->get('/api/get_poll_results.php?poll_id=1');
        $this->assertTrue($res['json']['success']);
        $this->assertIsArray($res['json']['data']['options']);
        $this->assertIsInt($res['json']['data']['total_votes']);
    }

    public function testMissingPollReturnsPureJson(): void
    {
        $this->client->post('/api/login.php', ['username' => 'alice', 'password' => '123456']);
        $res = $this->client->get('/api/get_poll_results.php?poll_id=999');

        $this->assertIsArray($res['json'], '响应体必须是合法 JSON，不得混入 Warning 文本');
        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('投票不存在', $res['json']['message']);
        $this->assertStringNotContainsString('<b>Warning</b>', $res['raw']);
    }

    public function testInvalidPollIdRejected(): void
    {
        $this->client->post('/api/login.php', ['username' => 'alice', 'password' => '123456']);
        $res = $this->client->get('/api/get_poll_results.php?poll_id=-1');

        $this->assertFalse($res['json']['success']);
        $this->assertStringContainsString('无效', $res['json']['message']);
    }
}
