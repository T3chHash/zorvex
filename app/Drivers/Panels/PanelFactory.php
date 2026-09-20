<?php
declare(strict_types=1);

namespace Zorvex\Drivers\Panels;

use InvalidArgumentException;

class PanelFactory
{
    public static function create(array $panelData): PanelInterface
    {
        $type = strtolower($panelData['type'] ?? 'marzban');
        $url = (string)($panelData['url'] ?? '');
        $username = (string)($panelData['username'] ?? '');
        $password = (string)($panelData['password'] ?? '');
        $token = (string)($panelData['token'] ?? '');

        return match ($type) {
            'marzban' => new MarzbanDriver($url, $username, $password, $token ?: null),
            'marzneshin' => new MarzneshinDriver($url, $username, $password, $token ?: null),
            'xui' => new XuiDriver($url, $username, $password),
            'hiddify' => new HiddifyDriver($url, $password ?: $token),
            default => throw new InvalidArgumentException("Unsupported panel type: {$type}"),
        };
    }
}
