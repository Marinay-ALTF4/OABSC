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
$doctorOptions = $doctorOptions ?? [];
$doctorProfiles = $doctorProfiles ?? [];
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
                            <div class="col-12">
                                <label class="form-label">Select Your Doctor</label>
                                <input type="hidden" id="doctor_name" name="doctor_name" value="<?= esc(old('doctor_name')) ?>" required>
                                <?php if ($doctorErr): ?>
                                    <div class="text-danger small mb-2"><?= esc($doctorErr) ?></div>
                                <?php endif; ?>
                                <div class="invalid-feedback" id="doctorClientError">Please select a doctor.</div>

                                <div class="row g-3 mt-1" id="doctorCardGrid">
                                    <?php foreach ($doctorOptions as $doctor):
                                        $profile = $doctorProfiles[$doctor] ?? [
                                            'avatar' => 'https://i.pravatar.cc/150?img=1',
                                            'spec'   => 'Specialist',
                                            'exp'    => 'N/A',
                                            'degree' => 'MD',
                                            'bio'    => 'Experienced medical professional.',
                                        ];
                                    ?>
                                        <div class="col-6 col-md-3">
                                            <div class="doctor-card <?= old('doctor_name') === $doctor ? 'selected' : '' ?>"
                                                 data-doctor="<?= esc($doctor) ?>"
                                                 onclick="selectDoctor(this)">
                                                <?php if (!empty($profile['avatar'])): ?>
                                                    <img src="<?= esc($profile['avatar']) ?>" alt="<?= esc($doctor) ?>" class="doctor-avatar">
                                                <?php else: ?>
                                                    <div class="doctor-avatar d-flex align-items-center justify-content-center fw-bold text-white" style="background:linear-gradient(135deg,#1d4ed8,#6d28d9);font-size:1.2rem;">
                                                        <?= strtoupper(substr(str_replace('Dr. ', '', $doctor), 0, 2)) ?>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="doctor-name"><?= esc($doctor) ?></div>
                                                <div class="doctor-spec"><?= esc($profile['spec']) ?></div>
                                                <div class="doctor-exp">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" fill="currentColor" viewBox="0 0 16 16">
                                                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                                    </svg>
                                                    <?= esc($profile['exp']) ?> experience
                                                </div>
                                                <button type="button"
                                                    class="btn-view-profile"
                                                    onclick="event.stopPropagation(); showProfile('<?= esc($doctor) ?>')"
                                                >View Profile</button>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
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
                            <h6 class="mb-3">Appointment Summary</h6>
                            <p class="mb-1"><strong>Doctor:</strong> <span data-summary="doctor">-</span></p>
                            <p class="mb-1"><strong>Date:</strong> <span data-summary="date">-</span></p>
                            <p class="mb-1"><strong>Time:</strong> <span data-summary="time">-</span></p>
                            <p class="mb-0 location-line">
                                <i class="bi bi-geo-alt-fill me-1"></i>
                                <strong>Location:</strong> <span data-summary="location">Select a doctor to see location</span>
                            </p>
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

<!-- Doctor Profile Modal -->
<div class="modal fade" id="doctorProfileModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 overflow-hidden">
            <div class="doctor-modal-header">
                <img id="modalDoctorAvatar" src="" alt="" class="modal-doctor-avatar">
                <h5 id="modalDoctorName" class="mb-0 fw-bold"></h5>
                <div id="modalDoctorSpec" class="small opacity-75 mt-1"></div>
            </div>
            <div class="modal-body p-4">
                <div class="profile-row">
                    <span class="profile-label">Degree</span>
                    <span id="modalDoctorDegree" class="profile-value"></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Experience</span>
                    <span id="modalDoctorExp" class="profile-value"></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">Specialization</span>
                    <span id="modalDoctorSpecFull" class="profile-value"></span>
                </div>
                <div class="profile-row">
                    <span class="profile-label">About</span>
                    <span id="modalDoctorBio" class="profile-value"></span>
                </div>
                <!-- Location Row -->
                <div class="profile-row border-0 pb-0">
                    <span class="profile-label"><i class="bi bi-geo-alt-fill me-1" style="color:#ef4444;"></i>Location</span>
                    <span id="modalDoctorLocation" class="profile-value" style="color:#1e40af;text-align:right;"></span>
                </div>

                <!-- Map -->
                <div class="mt-3">
                    <div class="map-label mb-2">
                        <i class="bi bi-map me-1 text-primary"></i>
                        <strong>Clinic Direction</strong>
                        <span class="map-sublabel">— tap the map to get directions from your location</span>
                    </div>
                    <div class="map-wrapper">
                        <iframe
                            id="modalDoctorMap"
                            class="clinic-map"
                            src=""
                            allowfullscreen=""
                            loading="lazy"
                            referrerpolicy="no-referrer-when-downgrade">
                        </iframe>
                        <a id="modalDoctorDirections" href="#" target="_blank" class="map-directions-btn">
                            <i class="bi bi-signpost-2-fill me-1"></i> Get Directions
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary btn-sm" id="modalSelectBtn" onclick="selectDoctorFromModal()">Select This Doctor</button>
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
                    <li><strong><i class="bi bi-geo-alt-fill me-1" style="color:#ef4444;"></i>Location:</strong> <span id="modal-location" style="color:#1e40af;">-</span></li>
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
    const doctorProfiles = <?= json_encode(array_combine(
        $doctorOptions,
        array_map(fn($d) => $doctorProfiles[$d] ?? ['avatar'=>'https://i.pravatar.cc/150?img=1','spec'=>'Specialist','exp'=>'N/A','degree'=>'MD','bio'=>'Experienced medical professional.'], $doctorOptions)
    ), JSON_UNESCAPED_UNICODE) ?>;

    // Override with doctor's own saved profile from localStorage if available
    const savedDoctorProfile = JSON.parse(localStorage.getItem('oabsc_profile') || '{}');
    Object.keys(doctorProfiles).forEach(function(name) {
        if (savedDoctorProfile.spec)   doctorProfiles[name].spec   = savedDoctorProfile.spec;
        if (savedDoctorProfile.exp)    doctorProfiles[name].exp    = savedDoctorProfile.exp;
        if (savedDoctorProfile.degree) doctorProfiles[name].degree = savedDoctorProfile.degree;
        if (savedDoctorProfile.bio)    doctorProfiles[name].bio    = savedDoctorProfile.bio;
        if (savedDoctorProfile.avatar) doctorProfiles[name].avatar = savedDoctorProfile.avatar;
    });

    // Doctor → Clinic location map (dynamic, no hardcoded names)
    const doctorLocations = {};

    function getDoctorLocation(name) {
        return doctorLocations[name] || 'General Santos City Medical Center';
    }

    // Doctor → Google Maps embed + directions (default only)
    const doctorMapData = {
        'default': {
            embed:      'https://www.google.com/maps?q=General+Santos+City&output=embed',
            directions: 'https://www.google.com/maps/dir/?api=1&destination=General+Santos+City',
        },
    };

    let currentProfileDoctor = null;

    window.selectDoctor = function(card) {
        document.querySelectorAll('.doctor-card').forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        document.getElementById('doctor_name').value = card.dataset.doctor;
        document.getElementById('doctorClientError').style.display = 'none';
        doctorInput.classList.remove('is-invalid');
        timeInput.value = '';
        renderSlots();
        updateSummary();
    };

    window.showProfile = function(doctorName) {
        const p = doctorProfiles[doctorName];
        if (!p) return;
        currentProfileDoctor = doctorName;

        const location = getDoctorLocation(doctorName);
        const mapData  = doctorMapData[doctorName] || doctorMapData['default'];

        document.getElementById('modalDoctorAvatar').src          = p.avatar;
        document.getElementById('modalDoctorName').textContent    = doctorName;
        document.getElementById('modalDoctorSpec').textContent    = p.spec;
        document.getElementById('modalDoctorDegree').textContent  = p.degree;
        document.getElementById('modalDoctorExp').textContent     = p.exp;
        document.getElementById('modalDoctorSpecFull').textContent= p.spec;
        document.getElementById('modalDoctorBio').textContent     = p.bio;
        document.getElementById('modalDoctorLocation').textContent= location;

        // Embed map
        document.getElementById('modalDoctorMap').src = mapData.embed;

        // Directions link — uses Google Maps directions with destination pre-filled
        document.getElementById('modalDoctorDirections').href = mapData.directions;

        new bootstrap.Modal(document.getElementById('doctorProfileModal')).show();
    };

    window.selectDoctorFromModal = function() {
        if (!currentProfileDoctor) return;
        const card = document.querySelector(`.doctor-card[data-doctor="${currentProfileDoctor}"]`);
        if (card) selectDoctor(card);
        bootstrap.Modal.getInstance(document.getElementById('doctorProfileModal')).hide();
    };

    // Override doctorInput reference for slot rendering
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
        const doctor = doctorInput.value || '-';
        summaryDoctor.textContent = doctor;
        summaryDate.textContent   = dateInput.value || '-';
        summaryTime.textContent   = timeInput.value || '-';
        const locEl = document.querySelector('[data-summary="location"]');
        if (locEl) {
            locEl.textContent = doctor !== '-' ? getDoctorLocation(doctor) : 'Select a doctor to see location';
            locEl.style.color = doctor !== '-' ? '#1e40af' : '#94a3b8';
        }
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
        const doctor = doctorInput.value || '-';
        modalSummaryDoctor.textContent = doctor;
        modalSummaryDate.textContent   = dateInput.value || '-';
        modalSummaryTime.textContent   = timeInput.value || '-';
        const modalLoc = document.getElementById('modal-location');
        if (modalLoc) {
            modalLoc.textContent = doctor !== '-' ? getDoctorLocation(doctor) : '-';
        }
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
    .location-line {
        color: #1e40af;
        font-size: 0.875rem;
    }
    .location-line i { color: #ef4444; }

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

    /* Doctor Cards */
    .doctor-card {
        border: 2px solid #e1e8ed;
        border-radius: 12px;
        padding: 1rem 0.75rem;
        text-align: center;
        cursor: pointer;
        transition: all 0.2s;
        background: white;
        height: 100%;
    }
    .doctor-card:hover {
        border-color: #4a90e2;
        box-shadow: 0 4px 12px rgba(74,144,226,0.15);
        transform: translateY(-2px);
    }
    .doctor-card.selected {
        border-color: #4a90e2;
        background: #f0f7ff;
        box-shadow: 0 4px 14px rgba(74,144,226,0.2);
    }
    .doctor-avatar {
        width: 72px;
        height: 72px;
        border-radius: 50%;
        object-fit: cover;
        border: 3px solid #e1e8ed;
        margin-bottom: 0.6rem;
    }
    .doctor-card.selected .doctor-avatar {
        border-color: #4a90e2;
    }
    .doctor-name {
        font-weight: 600;
        font-size: 0.9rem;
        color: #222;
        margin-bottom: 0.2rem;
    }
    .doctor-spec {
        font-size: 0.78rem;
        color: #4a90e2;
        font-weight: 500;
        margin-bottom: 0.35rem;
    }
    .doctor-exp {
        font-size: 0.75rem;
        color: #888;
        margin-bottom: 0.5rem;
    }
    .btn-view-profile {
        background: none;
        border: 1px solid #4a90e2;
        color: #4a90e2;
        border-radius: 6px;
        font-size: 0.75rem;
        padding: 0.25rem 0.6rem;
        cursor: pointer;
        transition: all 0.15s;
    }
    .btn-view-profile:hover {
        background: #4a90e2;
        color: white;
    }
    /* Doctor Profile Modal */
    .doctor-modal-header {
        background: linear-gradient(135deg, #4a90e2, #357abd);
        color: white;
        padding: 1.75rem 1rem 1.25rem;
        text-align: center;
    }
    .modal-doctor-avatar {
        width: 90px;
        height: 90px;
        border-radius: 50%;
        border: 3px solid rgba(255,255,255,0.7);
        object-fit: cover;
        margin-bottom: 0.75rem;
    }
    .profile-row {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        padding: 0.6rem 0;
        border-bottom: 1px solid #f0f0f0;
        gap: 1rem;
    }
    .profile-label {
        font-weight: 600;
        font-size: 0.82rem;
        color: #555;
        min-width: 110px;
    }
    .profile-value {
        font-size: 0.85rem;
        color: #333;
        text-align: right;
    }
    /* Map */
    .map-label {
        font-size: 0.82rem;
        color: #334155;
    }
    .map-sublabel {
        font-size: 0.75rem;
        color: #94a3b8;
        font-weight: 400;
    }
    .map-wrapper {
        position: relative;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid #e2e8f0;
    }
    .clinic-map {
        width: 100%;
        height: 220px;
        border: none;
        display: block;
    }
    .map-directions-btn {
        position: absolute;
        bottom: 10px;
        right: 10px;
        background: #1e40af;
        color: white;
        font-size: 0.78rem;
        font-weight: 600;
        padding: 0.4rem 0.85rem;
        border-radius: 8px;
        text-decoration: none;
        box-shadow: 0 2px 8px rgba(30,64,175,0.35);
        transition: background 0.15s;
    }
    .map-directions-btn:hover {
        background: #1d4ed8;
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
