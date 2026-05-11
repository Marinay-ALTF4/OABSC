<?php
$stats        = $stats ?? ['patients' => 0, 'appointments' => 0, 'today' => 0];
$patients     = $patients ?? [];
$appointments = $appointments ?? [];
$search       = $search ?? '';
?>
<?= view('doctor/_layout_top', ['pageTitle' => 'Patient Records', 'active' => 'records']) ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success py-2"><?= esc(session()->getFlashdata('success')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger py-2"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($patient)): ?>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h5 class="doc-page-title"><i class="bi bi-folder2-open me-2"></i>Patient Records</h5>
                <p class="doc-page-sub">View patients and the appointment history linked to your account.</p>
            </div>
        </div>

        <!-- Stat Cards -->
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="doc-stat-card">
                    <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-people"></i></div>
                    <div class="doc-stat-label">Patients</div>
                    <div class="doc-stat-value"><?= esc((string) $stats['patients']) ?></div>
                    <div class="doc-stat-sub">Unique patients in your records</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="doc-stat-card">
                    <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-journal-medical"></i></div>
                    <div class="doc-stat-label">Appointments</div>
                    <div class="doc-stat-value"><?= esc((string) $stats['appointments']) ?></div>
                    <div class="doc-stat-sub">Total linked appointments</div>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="doc-stat-card">
                    <div class="doc-stat-icon" style="background:#eaf6ea;color:#2e5c32;"><i class="bi bi-calendar-day"></i></div>
                    <div class="doc-stat-label">Today</div>
                    <div class="doc-stat-value"><?= esc((string) $stats['today']) ?></div>
                    <div class="doc-stat-sub">Records scheduled for today</div>
                </div>
            </div>
        </div>

        <!-- Patient List -->
        <div class="doc-table-card">
            <div class="doc-table-card-head d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                        <i class="bi bi-folder2-open me-2" style="color:#2e5c32;"></i>Patient List
                    </div>
                    <div class="doc-page-sub">Select a patient to open their appointment history.</div>
                </div>
                <form method="get" class="d-flex gap-2 flex-wrap">
                    <input type="text" name="search" class="doc-search-input" placeholder="Search name, email, or phone..." value="<?= esc($search) ?>">
                    <button class="doc-save-btn">Search</button>
                </form>
            </div>
            <div class="table-responsive">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Patient</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Appointments</th>
                            <th>Latest Visit</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($patients)): ?>
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">No patient records found.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($patients as $i => $p): ?>
                                <?php
                                    $status = strtolower((string) ($p['latest_status'] ?? ''));
                                    $statusClass = match ($status) {
                                        'approved'  => 'doc-badge-approved',
                                        'pending'   => 'doc-badge-pending',
                                        'completed' => 'doc-badge-completed',
                                        'cancelled' => 'doc-badge-cancelled',
                                        default     => 'doc-badge-default',
                                    };
                                ?>
                                <tr>
                                    <td class="text-muted fw-semibold"><?= $i + 1 ?></td>
                                    <td><div class="fw-semibold"><?= esc($p['name'] ?? 'Unknown') ?></div></td>
                                    <td><?= esc($p['email'] ?? '—') ?></td>
                                    <td><?= esc($p['phone'] ?? '—') ?></td>
                                    <td><span class="doc-count-badge"><?= esc((string) ($p['appointment_count'] ?? 0)) ?></span></td>
                                    <td>
                                        <div class="small fw-semibold"><?= esc(($p['latest_date'] ?? '-') !== '' ? date('M j, Y', strtotime((string) $p['latest_date'])) : '-') ?></div>
                                        <div class="small text-muted">
                                            <?= esc(substr((string) ($p['latest_time'] ?? ''), 0, 5) ?: '-') ?>
                                            <?php if ($status !== ''): ?>
                                                <span class="doc-badge <?= $statusClass ?> ms-1"><?= esc(ucfirst($status)) ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= site_url('/doctor/records/' . (int) ($p['id'] ?? 0)) ?>" class="doc-action-btn doc-action-view">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    <?php else: ?>

        <!-- Patient Profile Header -->
        <div class="doc-profile-card mb-4">
            <div class="doc-profile-avatar"><?= strtoupper(substr((string) ($patient['name'] ?? 'PT'), 0, 2)) ?></div>
            <div>
                <div class="doc-profile-name"><?= esc($patient['name'] ?? 'Unknown') ?></div>
                <div class="doc-profile-meta"><i class="bi bi-envelope me-1"></i><?= esc($patient['email'] ?? '—') ?></div>
                <?php if (! empty($patient['phone'])): ?>
                    <div class="doc-profile-meta"><i class="bi bi-telephone me-1"></i><?= esc($patient['phone']) ?></div>
                <?php endif; ?>
            </div>
            <div class="ms-auto">
                <a href="<?= site_url('/doctor/records') ?>" class="doc-action-btn doc-action-view">All Records</a>
            </div>
        </div>

        <!-- Appointment History -->
        <div class="doc-table-card">
            <div class="doc-table-card-head d-flex justify-content-between align-items-center gap-2">
                <div>
                    <div class="fw-semibold" style="font-size:0.88rem;color:#1b3a1e;">
                        <i class="bi bi-calendar2-check me-2" style="color:#2e5c32;"></i>Appointment History
                    </div>
                    <div class="doc-page-sub">Appointments connected to this patient.</div>
                </div>
                <span class="doc-count-badge"><?= count($appointments) ?> record<?= count($appointments) === 1 ? '' : 's' ?></span>
            </div>
            <?php if (empty($appointments)): ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-calendar-x d-block mb-2" style="font-size:1.5rem;color:#6aaa70;"></i>
                    No appointment records found for this patient.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Reason</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($appointments as $appt): ?>
                                <?php
                                    $status = strtolower((string) ($appt['status'] ?? 'pending'));
                                    $statusClass = match ($status) {
                                        'approved'  => 'doc-badge-approved',
                                        'pending'   => 'doc-badge-pending',
                                        'completed' => 'doc-badge-completed',
                                        'cancelled' => 'doc-badge-cancelled',
                                        default     => 'doc-badge-default',
                                    };
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc((string) ($appt['appointment_date'] ?? '-')) ?></td>
                                    <td><?= esc(substr((string) ($appt['appointment_time'] ?? ''), 0, 5) ?: '-') ?></td>
                                    <td style="max-width:320px;">
                                        <span class="d-block text-truncate" title="<?= esc((string) ($appt['reason'] ?? '')) ?>"><?= esc((string) ($appt['reason'] ?? '-')) ?></span>
                                    </td>
                                    <td><span class="doc-badge <?= $statusClass ?>"><?= esc(ucfirst($status)) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

    <?php endif; ?>

<?= view('doctor/_layout_bottom') ?>
