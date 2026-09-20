<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Panels;

class MarzneshinDriver implements PanelInterface
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private ?string $token = null;

    public function __construct(string $url, string $username, string $password, ?string $token = null)
    {
        $this->baseUrl = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->token = $token;
    }

    private function authenticate(): bool
    {
        $ch = curl_init("{$this->baseUrl}/api/admins/token");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'username' => $this->username,
                'password' => $this->password,
            ]),
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $response = curl_exec($ch);
        curl_close($ch);

        $data = json_decode((string)$response, true);
        if (!empty($data['access_token'])) {
            $this->token = $data['access_token'];
            return true;
        }

        return false;
    }

    private function request(string $endpoint, string $method = 'GET', ?array $body = null): array
    {
        if (empty($this->token) && !$this->authenticate()) {
            return ['ok' => false, 'error' => 'Authentication failed'];
        }

        $url = "{$this->baseUrl}/{$endpoint}";
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json',
            "Authorization: Bearer {$this->token}",
        ];

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        if ($body !== null) {
            $headers[] = 'Content-Type: application/json';
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body));
        }

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode === 401 && $this->authenticate()) {
            return $this->request($endpoint, $method, $body);
        }

        $data = json_decode((string)$response, true);
        return [
            'ok' => ($httpCode >= 200 && $httpCode < 300),
            'status' => $httpCode,
            'data' => $data,
        ];
    }

    public function createUser(string $username, int $trafficBytes, int $expireTimestamp, array $inbounds = []): array
    {
        $payload = [
            'username' => $username,
            'data_limit' => $trafficBytes,
            'expire' => $expireTimestamp > 0 ? $expireTimestamp : null,
            'status' => 'active',
        ];

        $res = $this->request('api/users', 'POST', $payload);

        if ($res['ok']) {
            $data = $res['data'];
            $subUrl = $data['subscription_url'] ?? '';
            if (!empty($subUrl) && !str_starts_with($subUrl, 'http')) {
                $subUrl = $this->baseUrl . '/' . ltrim($subUrl, '/');
            }
            return [
                'success' => true,
                'sub_url' => $subUrl,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'sub_url' => '',
            'error' => $res['data']['detail'] ?? 'Could not create user on Marzneshin',
        ];
    }

    public function getUser(string $username): ?array
    {
        $res = $this->request("api/users/{$username}");
        if ($res['ok'] && !empty($res['data'])) {
            $d = $res['data'];
            $subUrl = $d['subscription_url'] ?? '';
            if (!empty($subUrl) && !str_starts_with($subUrl, 'http')) {
                $subUrl = $this->baseUrl . '/' . ltrim($subUrl, '/');
            }

            return [
                'username' => $d['username'],
                'used_traffic' => (int)($d['used_traffic'] ?? 0),
                'data_limit' => (int)($d['data_limit'] ?? 0),
                'expire' => (int)($d['expire'] ?? 0),
                'status' => (string)($d['status'] ?? 'active'),
                'sub_url' => $subUrl,
            ];
        }

        return null;
    }

    public function resetTraffic(string $username): bool
    {
        $res = $this->request("api/users/{$username}/reset", 'POST');
        return $res['ok'];
    }

    public function updateUser(string $username, array $data): bool
    {
        $res = $this->request("api/users/{$username}", 'PUT', $data);
        return $res['ok'];
    }

    public function deleteUser(string $username): bool
    {
        $res = $this->request("api/users/{$username}", 'DELETE');
        return $res['ok'];
    }

    public function checkHealth(): bool
    {
        $res = $this->request('api/system');
        return $res['ok'];
    }
}
