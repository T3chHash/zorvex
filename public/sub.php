<?php
declare(strict_types=1);

/**
 * Zorvex High-Performance Smart Subscription Endpoint
 */

require_once dirname(__DIR__) . '/app/autoload.php';

use Zorvex\Core\Database;
use Zorvex\Drivers\Panels\PanelFactory;

$token = trim($_GET['token'] ?? '');
if (empty($token)) {
    http_response_code(400);
    die('Subscription token missing.');
}

$db = Database::getInstance();
$service = $db->selectOne("SELECT s.*, p.type as panel_type FROM services s LEFT JOIN panels p ON s.panel_id = p.id WHERE s.sub_id = :token", ['token' => $token]);

if (!$service) {
    http_response_code(404);
    die('Subscription not found.');
}

$userAgent = strtolower($_SERVER['HTTP_USER_AGENT'] ?? '');

// If user opens link in a regular web browser, redirect to the gorgeous Web Subscription Dashboard
$isBrowser = (
    str_contains($userAgent, 'mozilla') ||
    str_contains($userAgent, 'chrome') ||
    str_contains($userAgent, 'safari') ||
    str_contains($userAgent, 'edge')
) && !str_contains($userAgent, 'v2ray')
  && !str_contains($userAgent, 'clash')
  && !str_contains($userAgent, 'sing-box')
  && !str_contains($userAgent, 'shadowrocket')
  && !str_contains($userAgent, 'streisand')
  && !str_contains($userAgent, 'nekobox');

if ($isBrowser) {
    header("Location: sub_page.php?token=" . urlencode($token));
    exit;
}

// Fetch live configs from panel
$panel = $db->selectOne("SELECT * FROM panels WHERE id = :id", ['id' => $service['panel_id']]);
$configs = '';

if ($panel) {
    try {
        $driver = PanelFactory::create($panel);
        $panelUser = $driver->getUser($service['service_username']);

        if ($panelUser && !empty($panelUser['sub_url'])) {
            $ch = curl_init($panelUser['sub_url']);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT => $_SERVER['HTTP_USER_AGENT'] ?? 'v2rayNG',
            ]);
            $rawConfigs = curl_exec($ch);
            curl_close($ch);

            if ($rawConfigs) {
                $configs = $rawConfigs;
            }
        }
    } catch (Throwable $e) {
        error_log("Sub Error: " . $e->getMessage());
    }
}

// Subscription-Userinfo header for V2ray / Clash clients
$upload = 0;
$download = (int)$service['traffic_used_bytes'];
$total = (int)$service['traffic_total_bytes'];
$expire = (int)$service['expire_date'];

header('Content-Type: text/plain; charset=utf-8');
header("subscription-userinfo: upload={$upload}; download={$download}; total={$total}; expire={$expire}");
header('Profile-Update-Interval: 1');
header('Profile-Title: base64:' . base64_encode('Zorvex VIP'));

echo $configs ?: base64_encode("# Zorvex Secure Config\n");
