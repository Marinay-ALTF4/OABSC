$file = 'app/Views/auth/dashboard.php'
$lines = Get-Content $file -Encoding UTF8

# Lines are 0-indexed in array; line 629 = index 628, line 956 = index 955
$before = $lines[0..627]   # lines 1-628
$after  = $lines[956..]    # lines 957 onward

$newChat = @'
<!-- Messenger Chat Widget -->
<div id="chat-widget" class="msng-widget d-none">

    <!-- Header -->
    <div class="msng-header">
        <button class="msng-back-btn d-none" id="msng-back-btn" onclick="showContactList()" title="Back"><i class="bi bi-arrow-left"></i></button>
        <div class="msng-header-info" id="msng-header-info">
            <div class="msng-header-avatar" id="msng-header-avatar" style="background:#eff6ff;color:#3b82f6;">
                <i class="bi bi-chat-dots-fill"></i>
            </div>
            <div>
                <div class="msng-header-name" id="msng-header-name">Messages</div>
                <div class="ms