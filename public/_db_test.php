<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$user = 'nagw8149_userlab';     // samakan persis dengan cPanel
$pass = 'labbahasa123';      // tempel
$db   = 'nagw8149_labbahasa';
$port = 3306;

try {
  $mysqli = new mysqli($host, $user, $pass, $db, $port);
  $mysqli->set_charset('utf8mb4');
  echo "OK: connected as " . $mysqli->host_info;
} catch (Throwable $e) {
  echo "FAIL: " . $e->getMessage();
}
