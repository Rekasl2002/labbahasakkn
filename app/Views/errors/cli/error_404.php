<?php

use CodeIgniter\CLI\CLI;

$separator = str_repeat('=', 70);

CLI::newLine();
CLI::write($separator, 'light_gray');
CLI::write('HTTP ' . $code . ' - ' . $message, 'light_gray', 'red');
CLI::write($separator, 'light_gray');
CLI::newLine();

CLI::write('Message:', 'yellow');
CLI::write($message);
CLI::newLine();

CLI::write('Copy-friendly:', 'green');
CLI::write('Status: ' . $code);
CLI::write('Message: ' . $message);
CLI::newLine();
