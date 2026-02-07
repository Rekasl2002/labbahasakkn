<?php

use CodeIgniter\CLI\CLI;

$separator = str_repeat('=', 78);
$subSeparator = str_repeat('-', 78);

CLI::newLine();
CLI::write($separator, 'light_gray');
CLI::write('Unhandled Exception', 'light_gray', 'red');
CLI::write($separator, 'light_gray');
CLI::newLine();

CLI::write('Type    : ' . $exception::class, 'yellow');
if ($exception->getCode()) {
    CLI::write('Code    : ' . $exception->getCode(), 'yellow');
}
CLI::write('Message : ' . $message);
CLI::write('Location: ' . CLI::color(clean_path($exception->getFile()) . ':' . $exception->getLine(), 'green'));
CLI::newLine();

$copySummary = [
    'Type: ' . $exception::class,
    'Code: ' . ($exception->getCode() ? $exception->getCode() : '-'),
    'Message: ' . $message,
    'Location: ' . clean_path($exception->getFile()) . ':' . $exception->getLine(),
];

$last = $exception;

while ($prevException = $last->getPrevious()) {
    $last = $prevException;

    CLI::write('Caused by:', 'red');
    CLI::write('  Type    : ' . $prevException::class, 'yellow');
    if ($prevException->getCode()) {
        CLI::write('  Code    : ' . $prevException->getCode(), 'yellow');
    }
    CLI::write('  Message : ' . $prevException->getMessage());
    CLI::write('  Location: ' . CLI::color(clean_path($prevException->getFile()) . ':' . $prevException->getLine(), 'green'));
    CLI::newLine();

    $copySummary[] = 'Caused by: ' . $prevException::class;
    if ($prevException->getCode()) {
        $copySummary[] = 'Code: ' . $prevException->getCode();
    }
    $copySummary[] = 'Message: ' . $prevException->getMessage();
    $copySummary[] = 'Location: ' . clean_path($prevException->getFile()) . ':' . $prevException->getLine();
}

CLI::write('Copy-friendly:', 'green');
foreach ($copySummary as $line) {
    CLI::write($line);
}
CLI::newLine();

// The backtrace
if (defined('SHOW_DEBUG_BACKTRACE') && SHOW_DEBUG_BACKTRACE) {
    $backtraces = $last->getTrace();

    if ($backtraces) {
        CLI::write($subSeparator, 'light_gray');
        CLI::write('Backtrace', 'green');
        CLI::write($subSeparator, 'light_gray');
    }

    foreach ($backtraces as $i => $error) {
        $padFile  = '    '; // 4 spaces
        $padClass = '       '; // 7 spaces
        $c        = str_pad($i + 1, 3, ' ', STR_PAD_LEFT);

        if (isset($error['file'])) {
            $filepath = clean_path($error['file']) . ':' . $error['line'];

            CLI::write($c . $padFile . CLI::color($filepath, 'yellow'));
        } else {
            CLI::write($c . $padFile . CLI::color('[internal function]', 'yellow'));
        }

        $function = '';

        if (isset($error['class'])) {
            $type = ($error['type'] === '->') ? '()' . $error['type'] : $error['type'];
            $function .= $padClass . $error['class'] . $type . $error['function'];
        } elseif (! isset($error['class']) && isset($error['function'])) {
            $function .= $padClass . $error['function'];
        }

        $args = implode(', ', array_map(static fn ($value): string => match (true) {
            is_object($value) => 'Object(' . $value::class . ')',
            is_array($value)  => $value !== [] ? '[...]' : '[]',
            $value === null   => 'null', // return the lowercased version
            default           => var_export($value, true),
        }, array_values($error['args'] ?? [])));

        $function .= '(' . $args . ')';

        CLI::write($function);
        CLI::newLine();
    }
}
