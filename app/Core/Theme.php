<?php
declare(strict_types=1);

namespace Zorvex\Core;

class Theme
{
    public static function header(string $title, string $icon = '⚡'): string
    {
        return "╭─ {$icon} <b>{$title}</b>\n│\n";
    }

    public static function footer(): string
    {
        return "\n╰────────────────────";
    }

    public static function card(string $title, array $rows, string $icon = '💎'): string
    {
        $out = "╭─── {$icon} <b>{$title}</b> ───╮\n";
        foreach ($rows as $label => $value) {
            $out .= "├ ▫️ <b>{$label}:</b> <code>{$value}</code>\n";
        }
        $out .= "╰──────────────────────╯";
        return $out;
    }

    public static function serviceCard(
        string $serviceName,
        string $status,
        int|float $usedBytes,
        int|float $totalBytes,
        int $expireTimestamp,
        string $subUrl
    ): string {
        $statusIcon = match($status) {
            'active' => '🟢 فعال',
            'expired' => '🔴 منقضی شده',
            'disabled' => '⛔ غیرفعال',
            'on_hold' => '⏳ در انتظار فعال‌سازی',
            default => '⚪ نامشخص'
        };

        $progressBar = generate_progress_bar($usedBytes, $totalBytes, 10);
        $usedFormatted = format_bytes($usedBytes);
        $totalFormatted = format_bytes($totalBytes);

        $daysRemaining = max(0, (int)ceil(($expireTimestamp - time()) / 86400));
        $expireFormatted = $expireTimestamp > 0 ? jalali_now('Y/m/d', $expireTimestamp) : 'نامحدود';

        return "╭─── 🛡️ <b>سرویس اشتراک: {$serviceName}</b> ───╮\n"
             . "├ 🏷️ <b>وضعیت:</b> {$statusIcon}\n"
             . "├ 📊 <b>مصرف ترافیک:</b>\n"
             . "├   <code>{$progressBar}</code>\n"
             . "├   ▫️ <b>{$usedFormatted}</b> از <b>{$totalFormatted}</b>\n"
             . "├ ⏳ <b>اعتبار باقی‌مانده:</b> {$daysRemaining} روز\n"
             . "├ 📅 <b>تاریخ انقضا:</b> {$expireFormatted}\n"
             . "╰──────────────────────╯\n\n"
             . "🔗 <b>لینک اشتراک اختصاصی شما:</b>\n"
             . "<code>{$subUrl}</code>";
    }

    public static function mainKeyboard(bool $isAdmin = false): array
    {
        $keyboard = [
            [
                ['text' => '🛒 خرید سرویس جدید'],
                ['text' => '🛡️ سرویس‌های من'],
            ],
            [
                ['text' => '🎁 دریافت تست رایگان'],
                ['text' => '💳 افزایش موجودی / کیف پول'],
            ],
            [
                ['text' => '👥 کسب درآمد (زیرمجموعه‌گیری)'],
                ['text' => '🎡 گردونه شانس روزانه'],
            ],
            [
                ['text' => '📖 راهنمای اتصال'],
                ['text' => '🎧 پشتیبانی و تیکت'],
            ],
        ];

        if ($isAdmin) {
            $keyboard[] = [
                ['text' => '⚙️ مدیریت پیشرفته (ادمین)'],
            ];
        }

        return [
            'keyboard' => $keyboard,
            'resize_keyboard' => true,
            'persistent' => true,
        ];
    }
}
