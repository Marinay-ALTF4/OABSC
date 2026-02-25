<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: #f5f8fa;
            min-height: 100vh;
        }
        .card {
            border: 1px solid #e1e8ed;
            border-left: 4px solid #4a90e2;
            background: white;
            border-radius: 12px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 0.9rem;
        }
        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #e1e8ed;
            padding: 0.65rem 0.9rem;
        }
        .form-control:focus, .form-select:focus {
            border-color: #4a90e2;
            box-shadow: 0 0 0 0.2rem rgba(74, 144, 226, 0.15);
        }
        .btn-primary {
            background: #4a90e2;
            border: none;
            font-weight: 500;
            padding: 0.65rem 1.5rem;
            border-radius: 8px;
        }
        .btn-primary:hover {
            background: #357abd;
        }
        .btn-secondary {
            border-radius: 8px;
            padding: 0.65rem 1.5rem;
        }
        .doctor-card {
            padding: 0.75rem;
            border: 2px solid #e1e8ed;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }
        .doctor-card:hover {
            border-color: #4a90e2;
            background: #f8fbff;
        }
        .doctor-card.selected {
            border-color: #4a90e2;
            background: #e8f4f8;
        }
        .doctor-card input[type="radio"] {
            cursor: pointer;
        }
    </style>
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h4 class="card-title mb-1">Book New Appointment</h4>
                    <p class="text-muted small mb-4">Fill in the details below to schedule your appointment</p>

                    <?php if (session()->getFlashdata('error')): ?>
                        <div class="alert alert-danger">
                            <?= esc(session()->getFlashdata('error')) ?>
                        </div>
                    <?php endif; ?>

                    <?php if (session()->getFlashdata('errors')): ?>
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                <?php foreach (session()->getFlashdata('errors') as $error): ?>
                                    <li><?= esc($error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('/appointments/store') ?>" method="post">
                        <?= csrf_field() ?>

                        <div class="mb-4">
                            <label class="form-label">Select Doctor</label>
                            <?php if (empty($doctors)): ?>
                                <div class="alert alert-warning">
                                    No doctors available at the moment. Please try again later.
                                </div>
                            <?php else: ?>
                                <div class="row g-3">
                                    <?php foreach ($doctors as $doctor): ?>
                                        <div class="col-md-6">
                                            <label class="doctor-card">
                                                <div class="d-flex align-items-start">
                                                    <input 
                                                        type="radio" 
                                                        name="doctor_id" 
                                                        value="<?= $doctor['id'] ?>" 
                                                        class="me-3 mt-1"
                                                        required
                                                        <?= old('doctor_id') == $doctor['id'] ? 'checked' : '' ?>
                                                    >
                                                    <div>
                                                        <div class="fw-bold"><?= esc($doctor['name']) ?></div>
                                                        <div class="text-muted small"><?= esc($doctor['specialization']) ?></div>
                                                    </div>
                                                </div>
                                            </label>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Appointment Date</label>
                                <input 
                                    type="date" 
                                    class="form-control" 
                                    id="appointment_date" 
                                    name="appointment_date"
                                    min="<?= date('Y-m-d') ?>"
                                    value="<?= old('appointment_date') ?>"
                                    required
                                >
                            </div>
                            <div class="col-md-6">
                                <label for="appointment_time" class="form-label">Appointment Time</label>
                                <select class="form-select" id="appointment_time" name="appointment_time" required>
                                    <option value="">Select time</option>
                                    <option value="09:00:00" <?= old('appointment_time') == '09:00:00' ? 'selected' : '' ?>>09:00 AM</option>
                                    <option value="10:00:00" <?= old('appointment_time') == '10:00:00' ? 'selected' : '' ?>>10:00 AM</option>
                                    <option value="11:00:00" <?= old('appointment_time') == '11:00:00' ? 'selected' : '' ?>>11:00 AM</option>
                                    <option value="14:00:00" <?= old('appointment_time') == '14:00:00' ? 'selected' : '' ?>>02:00 PM</option>
                                    <option value="15:00:00" <?= old('appointment_time') == '15:00:00' ? 'selected' : '' ?>>03:00 PM</option>
                                    <option value="16:00:00" <?= old('appointment_time') == '16:00:00' ? 'selected' : '' ?>>04:00 PM</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="reason" class="form-label">Reason for Visit</label>
                            <textarea 
                                class="form-control" 
                                id="reason" 
                                name="reason" 
                                rows="3" 
                                placeholder="Please describe your symptoms or reason for visit"
                                required
                            ><?= old('reason') ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label for="notes" class="form-label">Additional Notes (Optional)</label>
                            <textarea 
                                class="form-control" 
                                id="notes" 
                                name="notes" 
                                rows="2" 
                                placeholder="Any additional information you'd like to share"
                            ><?= old('notes') ?></textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Book Appointment</button>
                            <a href="<?= site_url('/dashboard') ?>" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Add selected class to doctor card when radio is clicked
    document.querySelectorAll('.doctor-card input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.doctor-card').forEach(card => {
                card.classList.remove('selected');
            });
            if (this.checked) {
                this.closest('.doctor-card').classList.add('selected');
            }
        });
        
        // Set initial selected state
        if (radio.checked) {
            radio.closest('.doctor-card').classList.add('selected');
        }
    });
</script>
</body>
</html>
