<?php

if (!function_exists('lab_settings_path')) {
    function lab_settings_path(): string
    {
        return rtrim(WRITEPATH, '\\/') . '/lab_settings.json';
    }
}

if (!function_exists('lab_default_settings')) {
    function lab_default_settings(): array
    {
        return [
            'ip_range_start' => '192.168.100.101',
            'ip_range_end' => '192.168.100.140',
            'label_format' => 'Komputer {n}',
            'label_list' => '',
        ];
    }
}

if (!function_exists('lab_load_settings')) {
    function lab_load_settings(): array
    {
        $defaults = lab_default_settings();
        $path = lab_settings_path();
        if (!is_file($path)) {
            return $defaults;
        }

        $raw = @file_get_contents($path);
        if ($raw === false) {
            return $defaults;
        }

        $data = json_decode($raw, true);
        if (!is_array($data)) {
            return $defaults;
        }

        $allowed = array_intersect_key($data, $defaults);
        $merged = array_merge($defaults, $allowed);
        foreach ($merged as $key => $value) {
            if (is_string($value)) {
                $merged[$key] = trim($value);
            }
        }

        return $merged;
    }
}

if (!function_exists('lab_save_settings')) {
    function lab_save_settings(array $data): bool
    {
        $defaults = lab_default_settings();
        $clean = array_merge($defaults, array_intersect_key($data, $defaults));
        foreach ($clean as $key => $value) {
            if (is_string($value)) {
                $clean[$key] = trim($value);
            }
        }

        $json = json_encode($clean, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
        if ($json === false) {
            return false;
        }

        $path = lab_settings_path();
        $dir = dirname($path);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }

        return file_put_contents($path, $json . PHP_EOL, LOCK_EX) !== false;
    }
}

if (!function_exists('lab_ip_to_long')) {
    function lab_ip_to_long(string $ip): ?int
    {
        $long = ip2long($ip);
        if ($long === false) {
            return null;
        }

        return (int) sprintf('%u', $long);
    }
}

if (!function_exists('lab_device_label_for_ip')) {
    function lab_device_label_for_ip(string $ip, ?array $settings = null): string
    {
        $ip = trim($ip);
        if ($ip === '' || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return '';
        }

        $settings = $settings ?? lab_load_settings();
        $start = trim((string) ($settings['ip_range_start'] ?? ''));
        $end = trim((string) ($settings['ip_range_end'] ?? ''));

        if ($start === '' || $end === '') {
            return '';
        }

        if (filter_var($start, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return '';
        }

        if (filter_var($end, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4) === false) {
            return '';
        }

        $ipLong = lab_ip_to_long($ip);
        $startLong = lab_ip_to_long($start);
        $endLong = lab_ip_to_long($end);
        if ($ipLong === null || $startLong === null || $endLong === null) {
            return '';
        }

        if ($startLong > $endLong) {
            $tmp = $startLong;
            $startLong = $endLong;
            $endLong = $tmp;
        }

        if ($ipLong < $startLong || $ipLong > $endLong) {
            return '';
        }

        $index = (int) ($ipLong - $startLong);
        $n = $index + 1;

        $labelList = trim((string) ($settings['label_list'] ?? ''));
        if ($labelList !== '') {
            $lines = preg_split('/\r\n|\r|\n/', $labelList);
            if (isset($lines[$index])) {
                $line = trim((string) $lines[$index]);
                if ($line !== '') {
                    return $line;
                }
            }
        }

        $format = trim((string) ($settings['label_format'] ?? ''));
        if ($format === '') {
            $format = 'Komputer {n}';
        }

        return str_replace('{n}', (string) $n, $format);
    }
}
