<?php
declare(strict_types=1);

if (!function_exists('zorvex_config')) {
    function zorvex_config(string $key, mixed $default = null): mixed
    {
        static $configs = [];
        $parts = explode('.', $key);
        $file = $parts[0];

        if (!isset($configs[$file])) {
            $path = dirname(__DIR__, 2) . "/config/{$file}.php";
            if (file_exists($path)) {
                $configs[$file] = require $path;
            } else {
                $configs[$file] = [];
            }
        }

        $current = $configs[$file];
        for ($i = 1; $i < count($parts); $i++) {
            $part = $parts[$i];
            if (is_array($current) && array_key_exists($part, $current)) {
                $current = $current[$part];
            } else {
                return $default;
            }
        }

        return $current;
    }
}

if (!function_exists('format_bytes')) {
    function format_bytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));
        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}

if (!function_exists('format_price')) {
    function format_price(int|float $amount): string
    {
        return number_format((float)$amount, 0, '.', ',') . ' تومان';
    }
}

if (!function_exists('generate_progress_bar')) {
    function generate_progress_bar(int|float $current, int|float $total, int $size = 10): string
    {
        if ($total <= 0) {
            return '▱▱▱▱▱▱▱▱▱▱ 0%';
        }
        $ratio = min(max($current / $total, 0.0), 1.0);
        $filled = (int)round($ratio * $size);
        $empty = $size - $filled;
        $pct = (int)round($ratio * 100);

        return str_repeat('▰', $filled) . str_repeat('▱', $empty) . " {$pct}%";
    }
}

if (!function_exists('random_str')) {
    function random_str(int $length = 12): string
    {
        return substr(bin2hex(random_bytes((int)ceil($length / 2))), 0, $length);
    }
}

if (!function_exists('jalali_now')) {
    function jalali_now(string $format = 'Y/m/d H:i', ?int $timestamp = null): string
    {
        $timestamp = $timestamp ?? time();
        if (function_exists('jdate')) {
            return jdate($format, $timestamp);
        }
        return date('Y-m-d H:i', $timestamp);
    }
}

if (!function_exists('e_html')) {
    function e_html(string $text): string
    {
        return htmlspecialchars($text, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
