<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Panels;

class HiddifyDriver implements PanelInterface
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(string $url, string $apiKey)
    {
        $this->baseUrl = rtrim($url, '/');
        $this->apiKey = $apiKey;
    }

    private function request(string $endpoint, string $method = 'GET', ?array $body = null): array
    {
        $url = "{$this->baseUrl}/{$endpoint}";
        $ch = curl_init($url);

        $headers = [
            'Accept: application/json',
            "Hiddify-API-Key: {$this->apiKey}",
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

        $data = json_decode((string)$response, true);
        return [
            'ok' => ($httpCode >= 200 && $httpCode < 300),
            'status' => $httpCode,
            'data' => $data,
        ];
    }

    public function createUser(string $username, int $trafficBytes, int $expireTimestamp, array $inbounds = []): array
    {
        $days = $expireTimestamp > 0 ? max(1, (int)ceil(($expireTimestamp - time()) / 86400)) : 365;
        $usageLimitGB = $trafficBytes > 0 ? round($trafficBytes / (1024 * 1024 * 1024), 2) : 1000;

        $payload = [
            'name' => $username,
            'usage_limit_GB' => $usageLimitGB,
            'package_days' => $days,
            'mode' => 'no_reset',
        ];

        $res = $this->request('api/v2/admin/user/', 'POST', $payload);

        if ($res['ok'] && !empty($res['data']['uuid'])) {
            $uuid = $res['data']['uuid'];
            $subUrl = "{$this->baseUrl}/{$uuid}/";
            return [
                'success' => true,
                'sub_url' => $subUrl,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'sub_url' => '',
            'error' => $res['data']['message'] ?? 'Could not create user on Hiddify',
        ];
    }

    public function getUser(string $username): ?array
    {
        $res = $this->request("api/v2/admin/user/{$username}/");
        if ($res['ok'] && !empty($res['data'])) {
            $d = $res['data'];
            $usedBytes = (int)(($d['current_usage_GB'] ?? 0) * 1024 * 1024 * 1024);
            $totalBytes = (int)(($d['usage_limit_GB'] ?? 0) * 1024 * 1024 * 1024);
            $uuid = $d['uuid'] ?? '';

            return [
                'username' => $d['name'] ?? $username,
                'used_traffic' => $usedBytes,
                'data_limit' => $totalBytes,
                'expire' => 0,
                'status' => ($d['is_active'] ?? true) ? 'active' : 'disabled',
                'sub_url' => !empty($uuid) ? "{$this->baseUrl}/{$uuid}/" : '',
            ];
        }

        return null;
    }

    public function resetTraffic(string $username): bool
    {
        $res = $this->request("api/v2/admin/user/{$username}/reset_usage/", 'POST');
        return $res['ok'];
    }

    public function updateUser(string $username, array $data): bool
    {
        return true;
    }

    public function deleteUser(string $username): bool
    {
        $res = $this->request("api/v2/admin/user/{$username}/", 'DELETE');
        return $res['ok'];
    }

    public function checkHealth(): bool
    {
        $res = $this->request('api/v2/admin/server_status/');
        return $res['ok'];
    }
}
