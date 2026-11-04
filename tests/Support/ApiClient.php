<?php

/**
 * 接口测试用的轻量 HTTP 客户端：保持同一个 Session Cookie，统一解析 JSON。
 */
class ApiClient
{
    private string $baseUrl;
    private string $cookieFile;

    public function __construct(?string $baseUrl = null)
    {
        $this->baseUrl = rtrim($baseUrl ?: (getenv('TEST_BASE_URL') ?: 'http://127.0.0.1:8083'), '/');
        $this->cookieFile = tempnam(sys_get_temp_dir(), 'poll_cookie_');
    }

    public function get(string $path): array
    {
        return $this->request('GET', $path);
    }

    public function post(string $path, array $body = []): array
    {
        return $this->request('POST', $path, $body);
    }

    /**
     * @param string $fieldName 上传字段名
     * @param string $filePath  本地文件路径
     * @param string $mimeType  客户端声明的类型（用于验证服务端是否只信客户端）
     */
    public function upload(string $path, string $fieldName, string $filePath, string $mimeType): array
    {
        $file = new CURLFile($filePath, $mimeType, basename($filePath));
        return $this->request('POST', $path, null, [$fieldName => $file]);
    }

    private function request(string $method, string $path, ?array $body = null, ?array $multipart = null): array
    {
        $ch = curl_init($this->baseUrl . $path);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEJAR      => $this->cookieFile,
            CURLOPT_COOKIEFILE     => $this->cookieFile,
            CURLOPT_TIMEOUT        => 20,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($multipart !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $multipart);
            } else {
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body ?? [], JSON_UNESCAPED_UNICODE));
            }
        }

        $raw  = curl_exec($ch);
        $code = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'code' => $code,
            'raw'  => (string)$raw,
            'json' => json_decode((string)$raw, true),
        ];
    }

    /**
     * 注册并登录一个全新用户，返回用户名。用于避免用例之间互相污染投票状态。
     */
    public function loginNewUser(): string
    {
        $username = 'u' . substr(uniqid('', true), -8);
        $this->post('/api/register.php', [
            'username' => $username,
            'email'    => $username . '@example.com',
            'password' => '123456',
        ]);
        $this->post('/api/login.php', [
            'username' => $username,
            'password' => '123456',
        ]);
        return $username;
    }

    public function loginAdmin(string $username = 'admin', string $password = 'Admin@123'): array
    {
        return $this->post('/api/admin_login.php', ['action' => 'login', 'username' => $username, 'password' => $password]);
    }
}
