$php = "D:\phpstudy_pro\Extensions\php\php8.0.2nts\php.exe"
$root = Split-Path -Parent $MyInvocation.MyCommand.Path
$workdir = Join-Path $root "backend\public"

if (-not (Test-Path $php)) {
  Write-Host "PHP not found: $php" -ForegroundColor Red
  exit 1
}

$existing = Get-CimInstance Win32_Process | Where-Object {
  $_.Name -eq "php.exe" -and $_.CommandLine -like "*-S 0.0.0.0:8090*router.php*"
}

if ($existing) {
  Write-Host "Backend is already running on 0.0.0.0:8090" -ForegroundColor Yellow
  $existing | Select-Object ProcessId, CommandLine
  exit 0
}

Start-Process -FilePath $php -ArgumentList '-S 0.0.0.0:8090 router.php' -WorkingDirectory $workdir -WindowStyle Hidden
Start-Sleep -Seconds 2

$listener = Get-NetTCPConnection -LocalPort 8090 -State Listen -ErrorAction SilentlyContinue
if ($listener) {
  Write-Host "Backend started successfully on port 8090" -ForegroundColor Green
  $listener | Select-Object LocalAddress, LocalPort, OwningProcess
} else {
  Write-Host "Backend failed to start" -ForegroundColor Red
  exit 1
}
