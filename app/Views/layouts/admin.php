<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($pageTitle ?? 'Clinic Appointment System') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        body {
            min-height: 100vh;
            background: #f3f4f6;
            font-family: system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
        }
        main.container { padding-top: 1.5rem; }
        .navbar-brand span {
            font-weight: 700;
        }
        .role-badge {
            text-transform: uppercase;
            font-size: 0.72rem;
            letter-spacing: 0.08em;
        }
        .card {
            border-radius: 1rem;
        }
        .sec-table-card {
            background: #ffffff;
            border-radius: 16px;
            border: 1px solid #e2e8f0;
            box-shadow: 0 1px 6px rgba(15, 23, 42, 0.06);
            overflow: hidden;
        }
        .sec-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.9rem;
        }
        .sec-table thead tr {
            background: #f8fafc;
        }
        .sec-table th,
        .sec-table td {
            padding: 12px 14px;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
            text-align: left;
        }
        .sec-table th {
            font-size: 0.72rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #475569;
            white-space: nowrap;
        }
        .sec-table tbody tr:hover {
            background: #f8fafc;
        }
    </style>
</head>
<body>
<?= view('header') ?>

<main class="container py-4">
    <?= $this->renderSection('content') ?>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
