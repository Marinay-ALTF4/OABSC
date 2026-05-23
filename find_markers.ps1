$content = [System.IO.File]::ReadAllText('app/Views/auth/dashboard.php')
$idx1 = $content.IndexOf('Chat Widget')
$idx2 = $content.IndexOf('480px)')
Write-Host "Chat Widget at: $idx1"
Write-Host "480px) at: $idx2"
if ($idx1 -gt 0) {
    Write-Host "Context around Chat Widget:"
    Write-Host $content.Substring([Math]::Max(0,$idx1-10), 60)
}
if ($idx2 -gt 0) {
    Write-Host "Context around 480px:"
    Write-Host $content.Substring([Math]::Max(0,$idx2-30), 120)
}
