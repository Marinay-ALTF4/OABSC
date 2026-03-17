<?php
$errors = session()->getFlashdata('errors') ?? [];
if (! is_array($errors)) {
    $errors = [];
}
$formError = $errors['_form'] ?? null;
$doctorErr = $errors['doctor_name'] ?? null;
$dateErr = $errors['appointment_date'] ?? null;
$timeErr = $errors['appointment_time'] ?? null;
$reasonErr = $errors['reason'] ?? null;
$doctorOptions = $doctorOptions ?? ['Dr. Santos', 'Dr. Reyes', 'Dr. Cruz', 'Dr. Garcia'];
$bookedSlots = $bookedSlots ?? [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Book Appointment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4 booking-page">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-10 col-xl-9">
            <div class="card border-0 shadow-sm booking-card">
                <div class="card-body p-4 p-md-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
                        <div>
                            <h4 class="mb-1">Book New Appointment</h4>
                            <p class="text-muted small mb-0">Select doctor, date, and time slot, then confirm your booking.</p>
                        </div>
                        <a href="<?= site_url('/appointments/my') ?>" class="btn btn-sm btn-outline-secondary">My Appointments</a>
                    </div>

                    <?php if (session()->getFlashdata('success')): ?>
                        <div class="alert alert-success py-2 mb-3" role="alert" id="serverSuccessAlert">
                            <?= esc(session()->getFlashdata('success')) ?>
                        </div>
                    <?php endif; ?>

                    <div class="alert alert-success py-2 mb-3 d-none" role="alert" id="clientSuccessAlert">
                        Appointment submitted successfully.
                    </div>

                    <?php if ($formError): ?>
                        <div class="alert alert-danger py-2 mb-3" role="alert">
                            <?= esc($formError) ?>
                        </div>
                    <?php endif; ?>

                    <form action="<?= site_url('/appointments') ?>" method="post" novalidate id="appointmentForm">
                        <?= csrf_field() ?>

                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="doctor_name" class="form-label">Doctor</label>
                                <select
                                    class="form-select <?= $doctorErr ? 'is-invalid' : '' ?>"
                                    id="doctor_name"
                                    name="doctor_name"
                                    required
                                >
                                    <option value="">Select doctor</option>
                                    <?php foreach ($doctorOptions as $doctor): ?>
                                        <option value="<?= esc($doctor) ?>" <?= old('doctor_name') === $doctor ? 'selected' : '' ?>>
                                            <?= esc($doctor) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <?php if ($doctorErr): ?>
                                    <div class="invalid-feedback d-block"><?= esc($doctorErr) ?></div>
                                <?php else: ?>
                                    <div class="invalid-feedback" id="doctorClientError">Doctor is required.</div>
                                <?php endif; ?>
                            </div>

                            <div class="col-md-6">
                                <label for="appointment_date" class="form-label">Date</label>
                                <input
                                    type="date"
                                    class="form-control <?= $dateErr ? 'is-invalid' : '' ?>"
                                    id="appointment_date"
                                    name="appointment_date"
                                    value="<?= esc(old('appointment_date')) ?>"
                                    min="<?= esc(date('Y-m-d')) ?>"
                                    required
                                >
                                <?php if ($dateErr): ?>
                                    <div class="invalid-feedback d-block"><?= esc($dateErr) ?></div>
                                <?php else: ?>
                                    <div class="invalid-feedback" id="dateClientError">Pick a valid date (today or later).</div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="form-label d-block">Available Time Slots</label>
                            <input
                                type="hidden"
                                id="appointment_time"
                                name="appointment_time"
                                value="<?= esc(old('appointment_time')) ?>"
                            >
                            <div id="slotGrid" class="slot-grid"></div>
                            <?php if ($timeErr): ?>
                                <div class="invalid-feedback d-block"><?= esc($timeErr) ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback" id="timeClientError">Select an available time slot.</div>
                            <?php endif; ?>
                            <div class="form-text">Booked slots are disabled automatically.</div>
                        </div>

                        <div class="mt-4 mb-4">
                            <label for="reason" class="form-label">Reason for Visit</label>
                            <textarea
                                class="form-control <?= $reasonErr ? 'is-invalid' : '' ?>"
                                id="reason"
                                name="reason"
                                rows="4"
                                maxlength="500"
                                placeholder="Describe your concern or reason for consultation"
                                required
                            ><?= esc(old('reason')) ?></textarea>
                            <?php if ($reasonErr): ?>
                                <div class="invalid-feedback d-block"><?= esc($reasonErr) ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback" id="reasonClientError">Reason is required (minimum 5 characters).</div>
                            <?php endif; ?>
                        </div>

                        <div class="summary-card mb-4" id="appointmentSummary">
                            <h6 class="mb-2">Appointment Summary</h6>
                            <p class="mb-1"><strong>Doctor:</strong> <span data-summary="doctor">-</span></p>
                            <p class="mb-1"><strong>Date:</strong> <span data-summary="date">-</span></p>
                            <p class="mb-0"><strong>Time:</strong> <span data-summary="time">-</span></p>
                        </div>

                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-primary" id="openConfirmModalBtn">Submit Appointment</button>
                            <a href="<?= site_url('/dashboard') ?>" class="btn btn-link text-decoration-none">Back to Dashboard</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmBookingModal" tabindex="-1" aria-labelledby="confirmBookingModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="confirmBookingModalLabel">Confirm Appointment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">Please confirm your appointment details:</p>
                <ul class="mb-0 ps-3">
                    <li><strong>Doctor:</strong> <span data-modal-summary="doctor">-</span></li>
                    <li><strong>Date:</strong> <span data-modal-summary="date">-</span></li>
                    <li><strong>Time:</strong> <span data-modal-summary="time">-</span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="confirmSubmitBtn">Confirm Booking</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
(function () {
    const form = document.getElementById('appointmentForm');
    const doctorInput = document.getElementById('doctor_name');
    const dateInput = document.getElementById('appointment_date');
    const timeInput = document.getElementById('appointment_time');
    const reasonInput = document.getElementById('reason');
    const slotGrid = document.getElementById('slotGrid');
    const openConfirmModalBtn = document.getElementById('openConfirmModalBtn');
    const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
    const clientSuccessAlert = document.getElementById('clientSuccessAlert');

    const summaryDoctor = document.querySelector('[data-summary="doctor"]');
    const summaryDate = document.querySelector('[data-summary="date"]');
    const summaryTime = document.querySelector('[data-summary="time"]');

    const modalSummaryDoctor = document.querySelector('[data-modal-summary="doctor"]');
    const modalSummaryDate = document.querySelector('[data-modal-summary="date"]');
    const modalSummaryTime = document.querySelector('[data-modal-summary="time"]');

    const confirmModalEl = document.getElementById('confirmBookingModal');
    const confirmModal = new bootstrap.Modal(confirmModalEl);

    const oldTime = <?= json_encode((string) old('appointment_time')) ?>;
    const bookedFromServer = <?= json_encode($bookedSlots, JSON_UNESCAPED_UNICODE) ?>;

    const slotTimes = [
        '09:00', '09:30', '10:00', '10:30',
        '11:00', '11:30', '13:00', '13:30',
        '14:00', '14:30', '15:00', '15:30',
        '16:00', '16:30'
    ];

    const localStorageKey = 'oabsc_client_bookings';
    let submitting = false;

    function getLocalBookings() {
        try {
            const raw = localStorage.getItem(localStorageKey);
            if (!raw) {
                return [];
            }
            const parsed = JSON.parse(raw);
            return Array.isArray(parsed) ? parsed : [];
        } catch (e) {
            return [];
        }
    }

    function setLocalBookings(bookings) {
        localStorage.setItem(localStorageKey, JSON.stringify(bookings));
    }

    function normalizeTime(value) {
        if (!value) {
            return '';
        }
        return value.slice(0, 5);
    }

    function getBookingKey(doctor, date, time) {
        return doctor.trim().toLowerCase() + '|' + date + '|' + normalizeTime(time);
    }

    function getBookedKeySet() {
        const keys = new Set();

        bookedFromServer.forEach(function (item) {
            const doctor = String(item.doctor_name || '');
            const date = String(item.appointment_date || '');
            const time = normalizeTime(String(item.appointment_time || ''));
            if (doctor && date && time) {
                keys.add(getBookingKey(doctor, date, time));
            }
        });

        getLocalBookings().forEach(function (item) {
            const doctor = String(item.doctor_name || '');
            const date = String(item.appointment_date || '');
            const time = normalizeTime(String(item.appointment_time || ''));
            if (doctor && date && time) {
                keys.add(getBookingKey(doctor, date, time));
            }
        });

        return keys;
    }

    function isPastDate(value) {
        if (!value) {
            return false;
        }
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        const selected = new Date(value + 'T00:00:00');
        return selected < today;
    }

    function updateSummary() {
        summaryDoctor.textContent = doctorInput.value || '-';
        summaryDate.textContent = dateInput.value || '-';
        summaryTime.textContent = timeInput.value || '-';
    }

    function setFieldState(input, isValid) {
        if (isValid) {
            input.classList.remove('is-invalid');
        } else {
            input.classList.add('is-invalid');
        }
    }

    function validateForm() {
        let valid = true;

        const doctor = doctorInput.value.trim();
        const date = dateInput.value;
        const time = timeInput.value;
        const reason = reasonInput.value.trim();

        if (!doctor) {
            valid = false;
            setFieldState(doctorInput, false);
        } else {
            setFieldState(doctorInput, true);
        }

        if (!date || isPastDate(date)) {
            valid = false;
            setFieldState(dateInput, false);
        } else {
            setFieldState(dateInput, true);
        }

        const selectedSlotBtn = slotGrid.querySelector('.slot-btn.active');
        const timeError = document.getElementById('timeClientError');
        if (!time || !selectedSlotBtn) {
            valid = false;
            timeError.textContent = 'Select an available time slot.';
            timeError.style.display = 'block';
        } else {
            timeError.style.display = 'none';
        }

        if (reason.length < 5) {
            valid = false;
            setFieldState(reasonInput, false);
        } else {
            setFieldState(reasonInput, true);
        }

        if (doctor && date && time) {
            const key = getBookingKey(doctor, date, time);
            if (getBookedKeySet().has(key)) {
                valid = false;
                timeError.textContent = 'This slot is already booked. Please select another time.';
                timeError.style.display = 'block';
            }
        }

        return valid;
    }

    function renderSlots() {
        const doctor = doctorInput.value.trim();
        const date = dateInput.value;
        const selectedTime = normalizeTime(timeInput.value);
        const bookedKeys = getBookedKeySet();

        slotGrid.innerHTML = '';

        slotTimes.forEach(function (time) {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'slot-btn';
            button.textContent = time;

            const bookingKey = getBookingKey(doctor, date, time);
            const isBooked = !!doctor && !!date && bookedKeys.has(bookingKey);

            if (isBooked) {
                button.classList.add('booked');
                button.disabled = true;
                button.title = 'Already booked';
            }

            if (!isBooked && selectedTime === time) {
                button.classList.add('active');
            }

            button.addEventListener('click', function () {
                const timeError = document.getElementById('timeClientError');

                if (!doctorInput.value || !dateInput.value) {
                    timeError.textContent = 'Select doctor and date before choosing a time slot.';
                    timeError.style.display = 'block';
                    return;
                }

                slotGrid.querySelectorAll('.slot-btn.active').forEach(function (activeBtn) {
                    activeBtn.classList.remove('active');
                });

                button.classList.add('active');
                timeInput.value = time;
                timeError.style.display = 'none';
                updateSummary();
            });

            slotGrid.appendChild(button);
        });
    }

    function setModalSummary() {
        modalSummaryDoctor.textContent = doctorInput.value || '-';
        modalSummaryDate.textContent = dateInput.value || '-';
        modalSummaryTime.textContent = timeInput.value || '-';
    }

    function reserveSlotClientSide() {
        const booking = {
            doctor_name: doctorInput.value,
            appointment_date: dateInput.value,
            appointment_time: timeInput.value,
        };
        const bookings = getLocalBookings();
        bookings.push(booking);
        setLocalBookings(bookings);
    }

    doctorInput.addEventListener('change', function () {
        timeInput.value = '';
        renderSlots();
        updateSummary();
    });

    dateInput.addEventListener('change', function () {
        timeInput.value = '';
        renderSlots();
        updateSummary();
    });

    reasonInput.addEventListener('input', function () {
        if (reasonInput.value.trim().length >= 5) {
            reasonInput.classList.remove('is-invalid');
        }
    });

    openConfirmModalBtn.addEventListener('click', function () {
        clientSuccessAlert.classList.add('d-none');
        if (!validateForm()) {
            return;
        }

        setModalSummary();
        confirmModal.show();
    });

    confirmSubmitBtn.addEventListener('click', function () {
        if (submitting) {
            return;
        }

        if (!validateForm()) {
            confirmModal.hide();
            return;
        }

        submitting = true;
        confirmSubmitBtn.disabled = true;
        openConfirmModalBtn.disabled = true;
        openConfirmModalBtn.textContent = 'Submitting...';

        reserveSlotClientSide();
        clientSuccessAlert.classList.remove('d-none');
        confirmModal.hide();
        form.submit();
    });

    if (oldTime) {
        timeInput.value = normalizeTime(oldTime);
    }

    renderSlots();
    updateSummary();
})();
</script>

<style>
    body {
        margin: 0;
        padding: 0;
        background: linear-gradient(180deg, #f5f8fa 0%, #eef4fb 100%);
        min-height: 100vh;
    }

    .booking-page {
        max-width: 1120px;
    }

    .booking-card {
        border: 1px solid #e1e8ed;
        border-left: 4px solid #4a90e2;
        background: white;
        border-radius: 14px;
    }

    .summary-card {
        border: 1px solid #dbe7f6;
        background: #f8fbff;
        border-radius: 10px;
        padding: 14px 16px;
    }

    .slot-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(90px, 1fr));
        gap: 10px;
    }

    .slot-btn {
        border: 1px solid #bfd2ec;
        background: #fff;
        color: #204b83;
        border-radius: 8px;
        padding: 8px 10px;
        font-weight: 600;
        font-size: 0.86rem;
        transition: all 0.15s ease;
    }

    .slot-btn:hover {
        border-color: #4a90e2;
        color: #1f67bc;
    }

    .slot-btn.active {
        background: #4a90e2;
        border-color: #4a90e2;
        color: #fff;
        box-shadow: 0 4px 10px rgba(74, 144, 226, 0.25);
    }

    .slot-btn.booked,
    .slot-btn:disabled {
        background: #f1f5fa;
        border-color: #d2dce7;
        color: #95a3b5;
        cursor: not-allowed;
    }

    .btn-primary {
        background: #4a90e2;
        border: none;
        font-weight: 500;
        color: white;
    }

    .btn-primary:hover {
        background: #357abd;
        color: white;
    }

    @media (max-width: 992px) {
        .slot-grid {
            grid-template-columns: repeat(3, minmax(90px, 1fr));
        }
    }

    @media (max-width: 576px) {
        .slot-grid {
            grid-template-columns: repeat(2, minmax(90px, 1fr));
        }

        .booking-card .card-body {
            padding: 1.15rem !important;
        }
    }
</style>
</body>
</html>
