<?php
use App\Libraries\PermissionManager;
use App\Models\AccessRequestModel;

// Get the original URI from query string (passed by PermissionFilter)
$fromUri = '/' . ltrim((string) service('request')->getGet('from'), '/');
if (empty($fromUri) || $fromUri === '/') {
    $fromUri = '/' . ltrim(service('request')->getUri()->getPath(), '/');
    $baseUrl  = rtrim(config('App')->baseURL, '/');
    $basePath = parse_url($baseUrl, PHP_URL_PATH) ?? '';
    if ($basePath && str_starts_with($fromUri, $basePath)) {
        $fromUri = substr($fromUri, strlen($basePath));
    }
    $fromUri = '/' . ltrim($fromUri, '/');
}

// Find which permission this URI maps to.
// Prefer the most specific route so "/appointments/my" does not get captured by "/appointments".
$deniedPermCode  = null;
$deniedPermLabel = null;
$bestMatchLength  = -1;
foreach (PermissionManager::$definitions as $code => $def) {
    foreach ($def['routes'] as $route) {
        if (str_starts_with($fromUri, $route) && strlen($route) > $bestMatchLength) {
            $deniedPermCode   = $code;
            $deniedPermLabel  = $def['label'];
            $bestMatchLength  = strlen($route);
        }
    }
}

$userId = (int) session('user_id');

// Check if user no longer has a deny override — if so, redirect to the original page
if ($deniedPermCode && $userId) {
    $db = \Config\Database::connect();
    $hasDeny = $db->query(
        "SELECT 1 FROM user_permission_overrides WHERE user_id = ? AND permission_code = ? AND type = 'deny'",
        [$userId, $deniedPermCode]
    )->getRowArray();

    if (! $hasDeny) {
        // No deny override — redirect to dashboard so user can navigate normally
        header('Location: ' . site_url('/dashboard'));
        exit;
    }
}

// Check pending request status
$requestStatus = null;
if ($deniedPermCode && $userId) {
    $arModel       = new AccessRequestModel();
    $requestStatus = $arModel->getStatusByPermission($userId, $deniedPermCode);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="denied-page">
    <div class="denied-card">
        <div class="denied-icon">
            <i class="bi bi-shield-lock-fill"></i>
        </div>
        <h2 class="denied-title">Access Denied</h2>

        <?php if ($deniedPermLabel): ?>
            <p class="denied-feature">
                <i class="bi bi-lock me-1"></i><?= esc($deniedPermLabel) ?>
            </p>
        <?php endif; ?>

        <p class="denied-msg">
            You don't have permission to access this page.<br>
            You can request access from your administrator.
        </p>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="denied-alert denied-alert-success">
                <i class="bi bi-check-circle me-2"></i><?= esc(session()->getFlashdata('success')) ?>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="denied-alert denied-alert-error">
                <i class="bi bi-exclamation-circle me-2"></i><?= esc(session()->getFlashdata('error')) ?>
            </div>
        <?php endif; ?>

        <div class="denied-actions">
            <?php if ($deniedPermCode && $userId): ?>
                <?php if ($requestStatus === 'pending'): ?>
                    <div class="denied-pending-badge">
                        <i class="bi bi-hourglass-split me-2"></i>Request Pending — waiting for admin approval
                    </div>
                <?php else: ?>
                    <form action="<?= site_url('/access-request/send') ?>" method="post">
                        <?= csrf_field() ?>
                        <input type="hidden" name="permission_code" value="<?= esc($deniedPermCode) ?>">
                        <button type="submit" class="denied-btn-request">
                            <i class="bi bi-send me-2"></i>Request Access
                        </button>
                    </form>
                <?php endif; ?>
            <?php endif; ?>

            <a href="<?= site_url('/dashboard') ?>" class="denied-btn">
                <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
            </a>
        </div>
    </div>
</div>

<style>
    @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap');
    body { background: #edf2f7; font-family: 'Inter', sans-serif; margin: 0; }
    .denied-page {
        min-height: calc(100vh - 60px);
        display: flex; align-items: center; justify-content: center;
        padding: 2rem 1rem;
    }
    .denied-card {
        background: white; border-radius: 24px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 24px rgba(15,23,42,0.08);
        padding: 3rem 2.5rem;
        text-align: center;
        max-width: 460px; width: 100%;
    }
    .denied-icon {
        width: 80px; height: 80px; border-radius: 50%;
        background: #fee2e2; color: #dc2626;
        display: flex; align-items: center; justify-content: center;
        font-size: 2.2rem; margin: 0 auto 1.5rem;
    }
    .denied-title { font-size: 1.5rem; font-weight: 800; color: #0f172a; margin-bottom: 0.5rem; }
    .denied-feature {
        display: inline-flex; align-items: center;
        background: #f1f5f9; color: #475569;
        font-size: 0.82rem; font-weight: 600;
        padding: 4px 14px; border-radius: 999px; margin-bottom: 1rem;
    }
    .denied-msg { font-size: 0.88rem; color: #64748b; line-height: 1.7; margin-bottom: 1.5rem; }
    .denied-alert {
        font-size: 0.82rem; font-weight: 500;
        padding: 0.6rem 1rem; border-radius: 10px;
        margin-bottom: 1rem; display: flex; align-items: center;
    }
    .denied-alert-success { background: #d1fae5; color: #065f46; }
    .denied-alert-error   { background: #fee2e2; color: #991b1b; }
    .denied-actions { display: flex; flex-direction: column; gap: 10px; align-items: center; }
    .denied-btn-request {
        display: inline-flex; align-items: center;
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white; font-weight: 600; font-size: 0.88rem;
        padding: 0.65rem 1.5rem; border-radius: 12px;
        border: none; cursor: pointer; transition: opacity 0.15s;
        box-shadow: 0 2px 10px rgba(245,158,11,0.3);
        width: 100%; justify-content: center;
    }
    .denied-btn-request:hover { opacity: 0.9; }
    .denied-btn {
        display: inline-flex; align-items: center;
        background: #f1f5f9; color: #475569;
        font-weight: 600; font-size: 0.88rem;
        padding: 0.65rem 1.5rem; border-radius: 12px;
        text-decoration: none; transition: background 0.15s;
        width: 100%; justify-content: center;
    }
    .denied-btn:hover { background: #e2e8f0; color: #1e40af; }
    .denied-pending-badge {
        background: #fef3c7; color: #92400e;
        border: 1px solid #fde68a;
        font-size: 0.82rem; font-weight: 600;
        padding: 0.6rem 1rem; border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        width: 100%;
    }
</style>
</body>
</html>
