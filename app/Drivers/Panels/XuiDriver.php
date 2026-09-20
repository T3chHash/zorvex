<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Panels;

class XuiDriver implements PanelInterface
{
    private string $baseUrl;
    private string $username;
    private string $password;
    private string $cookieFile;

    public function __construct(string $url, string $username, string $password)
    {
        $this->baseUrl = rtrim($url, '/');
        $this->username = $username;
        $this->password = $password;
        $this->cookieFile = sys_get_temp_dir() . '/xui_' . md5($this->baseUrl . $this->username) . '.txt';
    }

    private function login(): bool
    {
        $ch = curl_init("{$this->baseUrl}/login");
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => http_build_query([
                'username' => $this->username,
                'password' => $this->password,
            ]),
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        $res = curl_exec($ch);
        curl_close($ch);

        $json = json_decode((string)$res, true);
        return !empty($json['success']);
    }

    private function request(string $path, string $method = 'GET', ?array $data = null): array
    {
        $url = "{$this->baseUrl}/{$path}";
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_COOKIEFILE => $this->cookieFile,
            CURLOPT_COOKIEJAR => $this->cookieFile,
            CURLOPT_TIMEOUT => 20,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($data !== null) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $json = json_decode((string)$response, true);
        if ($httpCode === 401 || (is_array($json) && isset($json['success']) && $json['success'] === false && strpos((string)($json['msg'] ?? ''), 'login') !== false)) {
            if ($this->login()) {
                return $this->request($path, $method, $data);
            }
        }

        return [
            'ok' => ($httpCode === 200 && (!isset($json['success']) || $json['success'] === true)),
            'data' => $json,
        ];
    }

    public function createUser(string $username, int $trafficBytes, int $expireTimestamp, array $inbounds = []): array
    {
        // 3x-ui / x-ui client addition
        $subId = bin2hex(random_bytes(8));
        $clientUuid = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000,
            mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $inboundId = !empty($inbounds['inbound_id']) ? (int)$inbounds['inbound_id'] : 1;

        $clientSettings = [
            'id' => $clientUuid,
            'email' => $username,
            'totalGB' => $trafficBytes,
            'expiryTime' => $expireTimestamp > 0 ? ($expireTimestamp * 1000) : 0,
            'subId' => $subId,
            'enable' => true,
        ];

        $postData = [
            'id' => $inboundId,
            'settings' => json_encode(['clients' => [$clientSettings]]),
        ];

        $res = $this->request('panel/api/inbounds/addClient', 'POST', $postData);

        if ($res['ok']) {
            $subUrl = "{$this->baseUrl}/sub/{$subId}";
            return [
                'success' => true,
                'sub_url' => $subUrl,
                'error' => null,
            ];
        }

        return [
            'success' => false,
            'sub_url' => '',
            'error' => $res['data']['msg'] ?? 'Could not add client to X-UI inbound',
        ];
    }

    public function getUser(string $username): ?array
    {
        $res = $this->request("panel/api/inbounds/getClientTraffics/{$username}");
        if ($res['ok'] && !empty($res['data']['obj'])) {
            $obj = $res['data']['obj'];
            $used = ($obj['up'] ?? 0) + ($obj['down'] ?? 0);
            $total = $obj['total'] ?? 0;
            $expire = !empty($obj['expiryTime']) ? (int)($obj['expiryTime'] / 1000) : 0;
            $subId = $obj['subId'] ?? '';

            return [
                'username' => $username,
                'used_traffic' => $used,
                'data_limit' => $total,
                'expire' => $expire,
                'status' => ($obj['enable'] ?? true) ? 'active' : 'disabled',
                'sub_url' => !empty($subId) ? "{$this->baseUrl}/sub/{$subId}" : '',
            ];
        }

        return null;
    }

    public function resetTraffic(string $username): bool
    {
        $res = $this->request("panel/api/inbounds/resetClientTraffic/{$username}", 'POST');
        return $res['ok'];
    }

    public function updateUser(string $username, array $data): bool
    {
        return true;
    }

    public function deleteUser(string $username): bool
    {
        return true;
    }

    public function checkHealth(): bool
    {
        return $this->login();
    }
}
