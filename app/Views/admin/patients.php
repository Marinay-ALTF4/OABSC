<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin · Patients</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?= view('header') ?>

<div class="container py-4">
    <header class="page-header mb-4 pb-3 d-flex flex-column flex-md-row justify-content-between align-items-md-center">
        <div>
            <h1 class="h4 mb-1">Patients</h1>
            <p class="text-muted small mb-0">
                Manage patient records: view list, search, and review appointment history.
            </p>
        </div>
        
    </header>

    <section class="mb-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h6 class="text-uppercase text-muted small mb-3">Manage patient records</h6>

                <div class="row g-3">
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <h6 class="mb-1">View patient list</h6>
                            <p class="small text-muted mb-2">
                                See all patients registered in the clinic.
                            </p>
                            <button class="btn btn-sm btn-outline-primary" disabled>Open (soon)</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <h6 class="mb-1">Search patient</h6>
                            <p class="small text-muted mb-2">
                                Quickly find a patient by name or ID.
                            </p>
                            <button class="btn btn-sm btn-outline-primary" disabled>Search (soon)</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <h6 class="mb-1">View appointment history</h6>
                            <p class="small text-muted mb-2">
                                Review a patient’s visit and booking history.
                            </p>
                            <button class="btn btn-sm btn-outline-primary" disabled>History (soon)</button>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100 bg-white">
                            <h6 class="mb-1">Edit patient info</h6>
                            <p class="small text-muted mb-2">
                                Update contact details and basic information.
                            </p>
                            <button class="btn btn-sm btn-outline-primary" disabled>Edit (soon)</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>