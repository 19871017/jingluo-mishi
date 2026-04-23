$targets = Get-CimInstance Win32_Process | Where-Object {
  $_.Name -eq "php.exe" -and $_.CommandLine -like "*-S 0.0.0.0:8090*router.php*"
}

if (-not $targets) {
  Write-Host "No backend process found on port 8090" -ForegroundColor Yellow
  exit 0
}

$targets | ForEach-Object {
  Stop-Process -Id $_.ProcessId -Force
  Write-Host "Stopped backend process: $($_.ProcessId)" -ForegroundColor Green
}
