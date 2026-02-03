<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Filters extends BaseConfig
{
    public array $aliases = [
        'csrf'          => \CodeIgniter\Filters\CSRF::class,
        'toolbar'       => \CodeIgniter\Filters\DebugToolbar::class,
        'honeypot'      => \CodeIgniter\Filters\Honeypot::class,
        'invalidchars'  => \CodeIgniter\Filters\InvalidChars::class,
        'secureheaders' => \CodeIgniter\Filters\SecureHeaders::class,

        // Custom
        'adminauth'     => \App\Filters\AdminAuth::class,
        'studentauth'   => \App\Filters\StudentAuth::class,
    ];

    public array $globals = [
        'before' => [
            // Aman: cegah karakter URL yang tidak valid
            'invalidchars',

            /**
             * CSRF (OPSIONAL)
             * Jangan aktifkan sebelum semua form HTML biasa menambahkan csrf_field()
             * atau pakai helper form_open().
             *
             * Kalau sudah siap, kamu bisa uncomment ini:
             *
             * 'csrf' => [
             *     'except' => [
             *         'api/*', // API polling/WebRTC/controls/chat tidak pakai token CSRF (fetch)
             *     ],
             * ],
             */

            /**
             * Honeypot (OPSIONAL)
             * Cocok untuk form web tradisional. Kalau dipakai, exclude API.
             *
             * 'honeypot' => [
             *     'except' => [
             *         'api/*',
             *     ],
             * ],
             */
        ],
        'after' => [
            // Aman: header keamanan standar (tidak mengganggu WebRTC)
            'secureheaders',

            // DebugToolbar akan ditambahkan hanya di dev lewat __construct()
        ],
    ];

    public array $methods = [];

    /**
     * Route-specific filters (jarang dibutuhkan karena kamu sudah pasang filter di Routes group).
     * Contoh kalau suatu saat perlu:
     * 'admin/*' => ['before' => ['adminauth']],
     */
    public array $filters = [];

    public function __construct()
    {
        parent::__construct();

        // DebugToolbar hanya untuk development/testing
        if (defined('ENVIRONMENT') && ENVIRONMENT !== 'production') {
            $this->globals['after'][] = 'toolbar';
        }
    }
}
