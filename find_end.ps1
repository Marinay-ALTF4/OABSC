$file = 'app/Views/auth/dashboard.php'
$lines = Get-Content $file -Encoding UTF8
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match '480px' -or $lines[$i] -match 'chat-fab.*right.*bottom') {
        Write-Host "Line $($i+1): $($lines[$i])"
    }
}
