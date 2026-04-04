<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verification Code</title>
</head>
<body style="margin:0;padding:0;background:#f8fafc;font-family:Arial,Helvetica,sans-serif;color:#0f172a;">
    <div style="max-width:600px;margin:0 auto;padding:32px 16px;">
        <div style="background:#ffffff;border:1px solid #e2e8f0;border-radius:20px;padding:28px;box-shadow:0 18px 40px rgba(15,23,42,0.08);">
            <p style="margin:0 0 12px;font-size:14px;color:#475569;">Clinic Appointment Portal</p>
            <h1 style="margin:0 0 16px;font-size:24px;line-height:1.2;">Your verification code</h1>
            <p style="margin:0 0 18px;font-size:16px;line-height:1.6;">Hello <?= esc($name) ?>, use the code below to finish creating your account for <?= esc($email) ?>.</p>
            <div style="display:inline-block;background:#eff6ff;border:1px solid #bfdbfe;border-radius:16px;padding:16px 24px;font-size:30px;font-weight:700;letter-spacing:6px;color:#1d4ed8;">
                <?= esc($verificationCode) ?>
            </div>
            <p style="margin:18px 0 0;font-size:14px;line-height:1.6;color:#64748b;">This code expires in <?= esc((string) $expiresMinutes) ?> minute<?= (int) $expiresMinutes === 1 ? '' : 's' ?>. If you did not request this, ignore this email.</p>
        </div>
    </div>
</body>
</html>
