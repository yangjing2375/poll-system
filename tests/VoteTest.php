<?php

use PHPUnit\Framework\TestCase;

/**
 * 投票提交模块接口用例（BUG-01 ~ BUG-06 的回归点都在这里）
 */
class VoteTest extends TestCase
{
    private ApiClient $client;

    protected function setUp(): void
    {
        $this->client = new ApiClient();
    }

    public function testVoteRequiresLogin(): void
    {
        $res = $this->client->post('/api/vote.php', ['poll_id' => 1, 'option_ids' => [1]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('登录', $res['json']['message']);
    }

    public function testMultiSelectVoteSuccess(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 2, 'option_ids' => [5, 6]]);

        $this->assertSame('success', $res['json']['status']);
        $this->assertStringContainsString('成功', $res['json']['message']);
    }

    public function testDuplicateVoteRejected(): void
    {
        $this->client->loginNewUser();
        $this->client->post('/api/vote.php', ['poll_id' => 2, 'option_ids' => [5]]);
        $again = $this->client->post('/api/vote.php', ['poll_id' => 2, 'option_ids' => [6]]);

        $this->assertSame('error', $again['json']['status']);
        $this->assertStringContainsString('已参与', $again['json']['message']);
    }

    public function testRepeatedOptionIdCountsOnce(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 1, 'option_ids' => [1, 1]]);

        $this->assertSame('success', $res['json']['status'], '重复选项去重后按 1 项处理，不应超限');
    }

    public function testExceedMaxOptionsRejected(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 2, 'option_ids' => [4, 5, 6]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('最多只能选择 2 项', $res['json']['message']);
    }

    public function testSinglePollRejectsMultipleOptions(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 1, 'option_ids' => [1, 2]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('最多只能选择 1 项', $res['json']['message']);
    }

    public function testInactivePollRejected(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 3, 'option_ids' => [8]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('禁用', $res['json']['message']);
    }

    public function testOptionFromAnotherPollRejected(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 1, 'option_ids' => [5]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('选项不存在', $res['json']['message']);
    }

    public function testMissingPollRejected(): void
    {
        $this->client->loginNewUser();
        $res = $this->client->post('/api/vote.php', ['poll_id' => 9999, 'option_ids' => [1]]);

        $this->assertSame('error', $res['json']['status']);
        $this->assertStringContainsString('投票不存在', $res['json']['message']);
    }

    public function testVoteRecordVisibleInMyVotes(): void
    {
        $this->client->loginNewUser();
        $this->client->post('/api/vote.php', ['poll_id' => 2, 'option_ids' => [5, 6]]);

        $res = $this->client->get('/api/get_user_votes.php');
        $this->assertTrue($res['json']['success']);
        $this->assertGreaterThan(0, count($res['json']['data']));

        $pollIds = array_column($res['json']['data'], 'poll_id');
        $this->assertContains(2, $pollIds);
    }
}
