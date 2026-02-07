<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class App extends BaseConfig
{
    /**
     * --------------------------------------------------------------------------
     * Base Site URL
     * --------------------------------------------------------------------------
     *
     * URL to your CodeIgniter root. Typically, this will be your base URL,
     * WITH a trailing slash:
     *
     * E.g., http://example.com/
     */
    public string $baseURL = 'http://localhost/';

    /**
     * Allowed Hostnames in the Site URL other than the hostname in the baseURL.
     *
     * @var list<string>
     */
    public array $allowedHostnames = [];

    /**
     * --------------------------------------------------------------------------
     * Index File
     * --------------------------------------------------------------------------
     *
     * If you have configured your web server to remove this file from your site URIs,
     * set this variable to an empty string.
     */
    public string $indexPage = 'index.php';

    /**
     * --------------------------------------------------------------------------
     * URI PROTOCOL
     * --------------------------------------------------------------------------
     */
    public string $uriProtocol = 'REQUEST_URI';

    /**
     * --------------------------------------------------------------------------
     * Allowed URL Characters
     * --------------------------------------------------------------------------
     */
    public string $permittedURIChars = 'a-z 0-9~%.:_\-';

    /**
     * --------------------------------------------------------------------------
     * Default Locale
     * --------------------------------------------------------------------------
     */
    public string $defaultLocale = 'id';

    /**
     * --------------------------------------------------------------------------
     * Negotiate Locale
     * --------------------------------------------------------------------------
     */
    public bool $negotiateLocale = false;

    /**
     * --------------------------------------------------------------------------
     * Supported Locales
     * --------------------------------------------------------------------------
     *
     * @var list<string>
     */
    public array $supportedLocales = ['id', 'en'];

    /**
     * --------------------------------------------------------------------------
     * Application Timezone
     * --------------------------------------------------------------------------
     */
    public string $appTimezone = 'Asia/Jakarta';

    /**
     * --------------------------------------------------------------------------
     * Default Character Set
     * --------------------------------------------------------------------------
     */
    public string $charset = 'UTF-8';

    /**
     * --------------------------------------------------------------------------
     * Force Global Secure Requests
     * --------------------------------------------------------------------------
     */
    public bool $forceGlobalSecureRequests = true;

    /**
     * Allow mic/speaker features to attempt running on insecure origins.
     * Note: browsers may still block media APIs on non-secure origins.
     */
    public bool $allowInsecureMedia = false;

    /**
     * --------------------------------------------------------------------------
     * Reverse Proxy IPs
     * --------------------------------------------------------------------------
     *
     * @var array<string, string>
     */
    public array $proxyIPs = [];

    /**
     * --------------------------------------------------------------------------
     * Content Security Policy
     * --------------------------------------------------------------------------
     */
    public bool $CSPEnabled = false;

    public function __construct()
    {
        parent::__construct();

        // baseURL: prioritas .env, kalau kosong coba deteksi otomatis
        $envBaseURL = env('app.baseURL');
        if (is_string($envBaseURL) && trim($envBaseURL) !== '') {
            $this->baseURL = $this->normalizeBaseURL($envBaseURL);
        } else {
            $detectedBaseURL = $this->detectBaseURL();
            if ($detectedBaseURL !== null) {
                $this->baseURL = $this->normalizeBaseURL($detectedBaseURL);
            }
        }

        // indexPage dari .env (kalau ada)
        $envIndexPage = env('app.indexPage');
        if (is_string($envIndexPage)) {
            $this->indexPage = trim($envIndexPage);
        }

        // locale & timezone dari .env (kalau ada)
        $envLocale = env('app.defaultLocale');
        if (is_string($envLocale) && trim($envLocale) !== '') {
            $this->defaultLocale = trim($envLocale);
        }

        $envTimezone = env('app.appTimezone');
        if (is_string($envTimezone) && trim($envTimezone) !== '') {
            $this->appTimezone = trim($envTimezone);
        }

        // flags dari .env (kalau ada)
        $this->forceGlobalSecureRequests = $this->envBool('app.forceGlobalSecureRequests', $this->forceGlobalSecureRequests);
        $this->allowInsecureMedia        = $this->envBool('app.allowInsecureMedia', $this->allowInsecureMedia);
        $this->CSPEnabled                = $this->envBool('app.CSPEnabled', $this->CSPEnabled);
    }

    private function envBool(string $key, bool $default): bool
    {
        $val = env($key);
        if (is_bool($val)) {
            return $val;
        }
        if (is_string($val)) {
            $parsed = filter_var($val, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            return $parsed === null ? $default : $parsed;
        }
        if (is_int($val)) {
            return $val === 1;
        }
        return $default;
    }

    private function normalizeBaseURL(string $url): string
    {
        $url = trim($url);
        if ($url === '') {
            return $url;
        }

        // Pastikan ada trailing slash
        if (substr($url, -1) !== '/') {
            $url .= '/';
        }

        return $url;
    }

    private function detectBaseURL(): ?string
    {
        if (PHP_SAPI === 'cli') {
            return null;
        }

        $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
        if ($scriptName === '') {
            return null;
        }

        $https  = $_SERVER['HTTPS'] ?? '';
        $scheme = (! empty($https) && $https !== 'off') ? 'https' : 'http';

        $host = $_SERVER['HTTP_HOST'] ?? $_SERVER['SERVER_NAME'] ?? 'localhost';

        $scriptName = str_replace('\\', '/', $scriptName);
        $path       = rtrim(str_replace(basename($scriptName), '', $scriptName), '/');
        $path       = $path === '' ? '/' : $path . '/';

        $baseURL = $scheme . '://' . $host . $path;

        return filter_var($baseURL, FILTER_VALIDATE_URL) !== false ? $baseURL : null;
    }
}
