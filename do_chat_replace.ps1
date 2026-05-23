$file = 'app/Views/auth/dashboard.php'
$content = [System.IO.File]::ReadAllText($file, [System.Text.Encoding]::UTF8)

# Find start: the chat widget comment
$startMarker = '<!-- ' + [char]0x2500 + [char]0x2500 + ' Chat Widget ' + [char]0x2500 + [char]0x2500 + ' -->'
$startIdx = $content.IndexOf($startMarker)
Write-Host "startMarker search result: $startIdx"

# Try simpler search
$startIdx2 = $content.IndexOf('Chat Widget')
Write-Host "Simple 'Chat Widget' search: $startIdx2"

# Find the actual comment line
$lines = $content -split "`n"
for ($i = 0; $i -lt $lines.Count; $i++) {
    if ($lines[$i] -match 'Chat Widget') {
        Write-Host "Line $i : $($lines[$i].Substring(0, [Math]::Min(60, $lines[$i].Length)))"
    }
}
