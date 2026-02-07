<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class ErrorsPreview extends BaseController
{
    private function ensurePreviewAllowed(): void
    {
        if (defined('ENVIRONMENT') && ENVIRONMENT === 'production') {
            throw PageNotFoundException::forPageNotFound();
        }
    }

    public function index()
    {
        $this->ensurePreviewAllowed();

        $links = [
            ['label' => 'Preview Error 400', 'url' => base_url('errors/preview/400')],
            ['label' => 'Preview Error 404', 'url' => base_url('errors/preview/404')],
            ['label' => 'Preview Error 500 (production)', 'url' => base_url('errors/preview/500')],
            ['label' => 'Preview Exception', 'url' => base_url('errors/preview/exception')],
        ];

        return view('errors/html/preview_index', [
            'links' => $links,
            'envName' => defined('ENVIRONMENT') ? ENVIRONMENT : 'unknown',
        ]);
    }

    public function show(string $type = '400')
    {
        $this->ensurePreviewAllowed();

        $type = strtolower($type);
        $data = [];
        $view = '';

        if ($type === '400') {
            $view = 'errors/html/error_400';
            $data = [
                'heading' => 'Permintaan Tidak Valid (Preview)',
                'message' => 'Ini contoh halaman 400 untuk keperluan preview.',
            ];
        } elseif ($type === '404') {
            $view = 'errors/html/error_404';
            $data = [
                'heading' => 'Halaman Tidak Ditemukan (Preview)',
                'message' => 'Ini contoh halaman 404 untuk keperluan preview.',
            ];
        } elseif ($type === '500' || $type === 'production') {
            $view = 'errors/html/production';
            $data = [
                'title' => 'Terjadi Kesalahan (Preview)',
                'heading' => 'Terjadi Kesalahan (Preview)',
                'message' => 'Ini contoh halaman error umum untuk keperluan preview.',
            ];
        } elseif ($type === 'exception') {
            $exception = new \RuntimeException('Contoh exception untuk preview', 500);
            $view = 'errors/html/error_exception';
            $data = [
                'title' => 'Preview Exception',
                'exception' => $exception,
                'statusCode' => 500,
                'code' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'trace' => $exception->getTraceAsString(),
            ];
        } else {
            throw PageNotFoundException::forPageNotFound();
        }

        return $this->response
            ->setHeader('X-Error-Preview', '1')
            ->setStatusCode(200)
            ->setBody(view($view, $data));
    }
}
