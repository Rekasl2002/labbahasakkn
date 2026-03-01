<?php

if (!function_exists('lab_settings_path')) {
    function lab_settings_path(): string
    {
        return rtrim(WRITEPATH, '\\/') . '/lab_settings.json';
    }
}

if (!function_exists('lab_default_logo_path')) {
    function lab_default_logo_path(): string
    {
        $defaultPath = '/assets/img/logo_tanpa_tulisan.png';
        if (lab_public_asset_exists($defaultPath)) {
            return $defaultPath;
        }

        return '/favicon.ico';
    }
}

if (!function_exists('lab_default_favicon_path')) {
    function lab_default_favicon_path(): string
    {
        return lab_default_logo_path();
    }
}

if (!function_exists('lab_default_settings')) {
    function lab_default_settings(): array
    {
        return [
            'app_name' => 'Lab Bahasa',
            'logo_path' => '',
            'favicon_path' => '',
            'warning_sound_path' => '',
            'tutorial_teacher_path' => '',
            'tutorial_student_path' => '',
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
        $current = lab_load_settings();
        $clean = array_merge($current, array_intersect_key($data, $defaults));
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

if (!function_exists('lab_asset_public_url')) {
    function lab_asset_public_url(string $path): string
    {
        $path = trim($path);
        if ($path === '') {
            return base_url('favicon.ico');
        }

        if (preg_match('~^(https?:)?//~i', $path) === 1 || str_starts_with($path, 'data:')) {
            return $path;
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $full = ROOTPATH . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $normalized);

        if (is_file($full)) {
            return base_url(ltrim($normalized, '/')) . '?v=' . filemtime($full);
        }

        return base_url(ltrim($normalized, '/'));
    }
}

if (!function_exists('lab_public_asset_exists')) {
    function lab_public_asset_exists(string $path): bool
    {
        $path = trim($path);
        if ($path === '') {
            return false;
        }

        if (preg_match('~^(https?:)?//~i', $path) === 1 || str_starts_with($path, 'data:')) {
            return true;
        }

        $normalized = '/' . ltrim(str_replace('\\', '/', $path), '/');
        $full = ROOTPATH . 'public' . str_replace('/', DIRECTORY_SEPARATOR, $normalized);

        return is_file($full);
    }
}

if (!function_exists('lab_tutorial_catalog')) {
    function lab_tutorial_catalog(?array $settings = null): array
    {
        $settings = $settings ?? lab_load_settings();

        $defs = [
            'teacher' => [
                'setting_key' => 'tutorial_teacher_path',
                'default_path' => '/assets/tutorial/Tutorial_Guru_Admin.pdf',
                'label' => 'Panduan untuk Guru',
            ],
            'student' => [
                'setting_key' => 'tutorial_student_path',
                'default_path' => '/assets/tutorial/Tutorial_Siswa.pdf',
                'label' => 'Panduan untuk Siswa',
            ],
        ];

        $out = [];
        foreach ($defs as $key => $def) {
            $configuredPath = trim((string) ($settings[$def['setting_key']] ?? ''));
            $defaultPath = trim((string) ($def['default_path'] ?? ''));

            $activePath = '';
            if ($configuredPath !== '' && lab_public_asset_exists($configuredPath)) {
                $activePath = $configuredPath;
            } elseif ($defaultPath !== '' && lab_public_asset_exists($defaultPath)) {
                $activePath = $defaultPath;
            }

            $out[$key] = [
                'key' => $key,
                'label' => (string) ($def['label'] ?? ''),
                'path' => $activePath,
                'url' => $activePath !== '' ? lab_asset_public_url($activePath) : '',
                'default_path' => $defaultPath,
                'configured_path' => $configuredPath,
                'is_default' => $activePath !== '' && $activePath === $defaultPath,
            ];
        }

        return $out;
    }
}

if (!function_exists('lab_tutorial_items_for_role')) {
    function lab_tutorial_items_for_role(string $role, ?array $settings = null): array
    {
        $role = strtolower(trim($role));
        $catalog = lab_tutorial_catalog($settings);
        $keys = $role === 'admin' ? ['teacher', 'student'] : ['student'];

        $items = [];
        foreach ($keys as $key) {
            if (!isset($catalog[$key])) {
                continue;
            }

            $item = $catalog[$key];
            $url = trim((string) ($item['url'] ?? ''));
            if ($url === '') {
                continue;
            }

            $items[] = $item;
        }

        return $items;
    }
}

if (!function_exists('lab_app_branding')) {
    function lab_app_branding(?array $settings = null): array
    {
        $settings = $settings ?? lab_load_settings();
        $defaultLogoPath = lab_default_logo_path();
        $defaultFaviconPath = lab_default_favicon_path();

        $appName = trim((string) ($settings['app_name'] ?? ''));
        if ($appName === '') {
            $appName = 'Lab Bahasa';
        }

        $logoPath = trim((string) ($settings['logo_path'] ?? ''));
        if (
            $logoPath === ''
            || $logoPath === '/favicon.ico'
            || !lab_public_asset_exists($logoPath)
        ) {
            $logoPath = $defaultLogoPath;
        }

        $faviconPath = trim((string) ($settings['favicon_path'] ?? ''));
        if (
            $faviconPath === ''
            || $faviconPath === '/favicon.ico'
            || !lab_public_asset_exists($faviconPath)
        ) {
            $faviconPath = $defaultFaviconPath;
        }

        if (!lab_public_asset_exists($faviconPath)) {
            $faviconPath = $logoPath;
        }

        return [
            'app_name' => $appName,
            'logo_path' => $logoPath,
            'favicon_path' => $faviconPath,
            'logo_url' => lab_asset_public_url($logoPath),
            'favicon_url' => lab_asset_public_url($faviconPath),
        ];
    }
}
