<?php

if (! function_exists('asset_url')) {
    /**
     * Return asset url with automatic cache-busting version (filemtime).
     * Usage: asset_url('assets/css/app.css')
     */
    function asset_url(string $path): string
    {
        $path = ltrim($path, '/');

        // FCPATH = folder tempat index.php (public_html/domain kamu)
        $fullPath = FCPATH . $path;

        $ver = is_file($fullPath) ? (string) filemtime($fullPath) : (string) time();

        return base_url($path) . '?v=' . $ver;
    }
}
