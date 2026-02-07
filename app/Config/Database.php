<?php

namespace Config;

use CodeIgniter\Database\Config;

/**
 * Database Configuration
 */
class Database extends Config
{
    /**
     * The directory that holds the Migrations and Seeds directories.
     */
    public string $filesPath = APPPATH . 'Database' . DIRECTORY_SEPARATOR;

    /**
     * Lets you choose which connection group to use if no other is specified.
     */
    public string $defaultGroup = 'default';

    /**
     * The default database connection.
     *
     * Catatan penting:
     * - Di ENVIRONMENT=development (lokal), kita izinkan fallback root/labbahasa.
     * - Di ENVIRONMENT=production (hosting), fallback dibuat kosong agar tidak pernah nyasar ke root.
     *
     * @var array<string, mixed>
     */
    public array $default = [];

    /**
     * This database connection is used when running PHPUnit database tests.
     *
     * @var array<string, mixed>
     */
    public array $tests = [
        'DSN'         => '',
        'hostname'    => '127.0.0.1',
        'username'    => '',
        'password'    => '',
        'database'    => ':memory:',
        'DBDriver'    => 'SQLite3',
        'DBPrefix'    => 'db_',
        'pConnect'    => false,
        'DBDebug'     => true,
        'charset'     => 'utf8',
        'DBCollat'    => '',
        'swapPre'     => '',
        'encrypt'     => false,
        'compress'    => false,
        'strictOn'    => false,
        'failover'    => [],
        'port'        => 3306,
        'foreignKeys' => true,
        'busyTimeout' => 1000,
        'synchronous' => null,
        'dateFormat'  => [
            'date'     => 'Y-m-d',
            'datetime' => 'Y-m-d H:i:s',
            'time'     => 'H:i:s',
        ],
    ];

    public function __construct()
    {
        parent::__construct();

        if (ENVIRONMENT === 'testing') {
            $this->defaultGroup = 'tests';
        }

        // Fallback berbeda untuk development vs production
        $isDev = (ENVIRONMENT === 'development');

        $hostname = env('database.default.hostname', $isDev ? 'localhost' : '');
        $database = env('database.default.database', $isDev ? 'labbahasa' : '');
        $username = env('database.default.username', $isDev ? 'root' : '');
        $password = env('database.default.password', $isDev ? '' : '');
        $driver   = env('database.default.DBDriver', 'MySQLi');
        $prefix   = env('database.default.DBPrefix', '');
        $charset  = env('database.default.charset', 'utf8mb4');
        $collat   = env('database.default.DBCollat', 'utf8mb4_general_ci');

        // Port dari env (kalau ada), kalau tidak fallback 3306
        $port = env('database.default.port', 3306);
        $port = is_numeric($port) ? (int) $port : 3306;

        // DBDebug: true di dev, false di production (kecuali kamu set di env)
        $dbDebug = env('database.default.DBDebug', $isDev);
        // Normalisasi jika nilainya string "true"/"false"
        if (is_string($dbDebug)) {
            $dbDebug = filter_var($dbDebug, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            $dbDebug = ($dbDebug === null) ? $isDev : $dbDebug;
        }

        $this->default = [
            'DSN'          => env('database.default.DSN', ''),
            'hostname'     => $hostname,
            'username'     => $username,
            'password'     => $password,
            'database'     => $database,
            'DBDriver'     => $driver,
            'DBPrefix'     => $prefix,
            'pConnect'     => false,
            'DBDebug'      => (bool) $dbDebug,
            'charset'      => $charset,
            'DBCollat'     => $collat,
            'swapPre'      => '',
            'encrypt'      => false,
            'compress'     => false,
            'strictOn'     => false,
            'failover'     => [],
            'port'         => $port,
            'numberNative' => false,
            'foundRows'    => false,
            'dateFormat'   => [
                'date'     => 'Y-m-d',
                'datetime' => 'Y-m-d H:i:s',
                'time'     => 'H:i:s',
            ],
        ];
    }
}
