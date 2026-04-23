${null} = $MyInvocation.MyCommand.Path
Write-Host "Checking backend listener on port 8090..." -ForegroundColor Cyan
$listener = Get-NetTCPConnection -LocalPort 8090 -State Listen -ErrorAction SilentlyContinue
if ($listener) {
  Write-Host "Listener is active:" -ForegroundColor Green
  $listener | Select-Object LocalAddress, LocalPort, OwningProcess
} else {
  Write-Host "No listener found on port 8090" -ForegroundColor Red
}

Write-Host "Testing localhost /api/home ..." -ForegroundColor Cyan
try {
  $res = Invoke-WebRequest -UseBasicParsing "http://127.0.0.1:8090/api/home"
  Write-Host "localhost status: $($res.StatusCode)" -ForegroundColor Green
} catch {
  Write-Host "localhost failed: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host "Testing LAN /api/home ..." -ForegroundColor Cyan
try {
  $res = Invoke-WebRequest -UseBasicParsing "http://192.168.31.31:8090/api/home"
  Write-Host "LAN status: $($res.StatusCode)" -ForegroundColor Green
} catch {
  Write-Host "LAN failed: $($_.Exception.Message)" -ForegroundColor Red
}
