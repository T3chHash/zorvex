<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Panels;

interface PanelInterface
{
    /**
     * Create a new VPN user/subscription on the panel
     *
     * @param string $username Unique username
     * @param int $trafficBytes Total data limit in bytes (0 for unlimited)
     * @param int $expireTimestamp Unix timestamp of expiration (0 for unlimited)
     * @param array $inbounds Optional specific protocol/inbound list
     * @return array ['success' => bool, 'sub_url' => string, 'error' => ?string]
     */
    public function createUser(string $username, int $trafficBytes, int $expireTimestamp, array $inbounds = []): array;

    /**
     * Fetch user status and bandwidth usage
     *
     * @param string $username
     * @return array|null ['username' => string, 'used_traffic' => int, 'data_limit' => int, 'expire' => int, 'status' => string, 'sub_url' => string]
     */
    public function getUser(string $username): ?array;

    /**
     * Reset traffic consumption for renewal
     */
    public function resetTraffic(string $username): bool;

    /**
     * Update user expiration or bandwidth
     */
    public function updateUser(string $username, array $data): bool;

    /**
     * Delete user from panel
     */
    public function deleteUser(string $username): bool;

    /**
     * Ping panel to verify credentials and connectivity
     */
    public function checkHealth(): bool;
}
