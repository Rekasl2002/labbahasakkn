@echo off
setlocal

:: Require Administrator privileges for Local Machine Root store
net session >nul 2>&1
if %errorlevel% neq 0 (
  echo Meminta izin Administrator...
  powershell -NoProfile -ExecutionPolicy Bypass -Command "Start-Process -FilePath '%~f0' -Verb RunAs"
  exit /b
)

set "CERT_PATH=%~dp0laragon-local-ca.crt"
if not exist "%CERT_PATH%" (
  set "CERT_PATH=C:\laragon\etc\ssl\laragon-local-ca.crt"
)

if not exist "%CERT_PATH%" (
  echo File sertifikat CA tidak ditemukan.
  echo Letakkan laragon-local-ca.crt di folder yang sama dengan file ini
  echo atau cek path Laragon di skrip.
  pause
  exit /b 1
)

certutil -addstore -f "ROOT" "%CERT_PATH%"
if %errorlevel% neq 0 (
  echo Gagal menambahkan sertifikat CA.
  pause
  exit /b 1
)

:: Optional: copy server cert/key for 192.168.101.100 to Laragon SSL folder
set "SSL_DIR=C:\laragon\etc\ssl"
if exist "%SSL_DIR%" (
  if exist "%~dp0labbahasa-192.168.101.100.crt" copy /Y "%~dp0labbahasa-192.168.101.100.crt" "%SSL_DIR%" >nul
  if exist "%~dp0labbahasa-192.168.101.100.key" copy /Y "%~dp0labbahasa-192.168.101.100.key" "%SSL_DIR%" >nul
)

echo Selesai untuk IP 192.168.101.100. Silakan restart Chrome/Edge.
pause
