<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Prescription · {{ $appointment->patient?->full_name ?? 'Patient' }} &middot; {{ config('app.name') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: "Inter", "Segoe UI", system-ui, sans-serif;
            background: #f4f6fb;
            color: #0f172a;
        }

        .rx-sheet {
            max-width: 860px;
            background: #fff;
            border: 1px solid #e5e9f0;
            border-radius: 18px;
            box-shadow: 0 24px 60px -30px rgba(15, 23, 42, .35);
            overflow: hidden;
        }

        .rx-head {
            background: linear-gradient(135deg, #101828, #312e81);
            color: #fff;
            padding: 1.6rem 2rem;
        }

        .rx-head__mark {
            width: 46px;
            height: 46px;
            border-radius: 14px;
            display: grid;
            place-items: center;
            background: linear-gradient(135deg, #6366f1, #06b6d4);
            font-size: 1.35rem;
            flex: 0 0 auto;
        }

        .rx-body { padding: 1.75rem 2rem 2rem; }

        .rx-label {
            font-size: .68rem;
            letter-spacing: .12em;
            text-transform: uppercase;
            font-weight: 700;
            color: #64748b;
            margin-bottom: .15rem;
        }

        .rx-value { font-size: .98rem; white-space: pre-wrap; }

        .rx-rule { border: 0; border-top: 2px dashed #e5e9f0; margin: 1.4rem 0; }

        .rx-sign { min-height: 70px; border-bottom: 2px solid #0f172a; max-width: 260px; }

        @media print {
            body { background: #fff; }
            .no-print { display: none !important; }
            .rx-sheet { border: 0; box-shadow: none; border-radius: 0; max-width: 100%; }
            @page { margin: 14mm; }
        }
    </style>
</head>
<body>
<main class="py-4 py-lg-5">
    <div class="container">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 no-print">
            <a href="{{ route('patient.dashboard') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Back to my appointments
            </a>
            <button type="button" class="btn btn-sm btn-hms" onclick="window.print()">
                <i class="bi bi-printer me-1"></i>Print prescription
            </button>
        </div>

        <div class="rx-sheet">
            <header class="rx-head d-flex align-items-center gap-3">
                <span class="rx-head__mark"><i class="bi bi-heart-pulse-fill"></i></span>
                <div class="flex-grow-1">
                    <div class="fw-bold fs-5">{{ config('app.name') }}</div>
                    <div class="small" style="opacity: .75">Clinic &middot; Doctor &middot; Patient prescription record</div>
                </div>
                <div class="text-end small" style="opacity: .85">
                    <div>Appointment #{{ $appointment->appointment_id }}</div>
                    <div>{{ $appointment->appointment_date?->format('d M Y') }} &middot; {{ $appointment->formatted_time }}</div>
                </div>
            </header>

            <div class="rx-body">
                <div class="row g-4 mb-4">
                    <div class="col-sm-6">
                        <div class="rx-label">Patient</div>
                        <div class="rx-value fw-semibold">{{ $appointment->patient?->full_name ?? '—' }}</div>
                        <div class="small text-muted">
                            {{ $appointment->patient?->gender ?? '' }}
                            @if ($appointment->patient?->contact)
                                &middot; {{ $appointment->patient->contact }}
                            @endif
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rx-label">Treating doctor</div>
                        <div class="rx-value fw-semibold">Dr. {{ $appointment->doctor?->full_name ?? '—' }}</div>
                        <div class="small text-muted">{{ $appointment->doctor?->specialization }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rx-label">Clinic</div>
                        <div class="rx-value fw-semibold">{{ $appointment->clinic?->clinic_name ?? '—' }}</div>
                        <div class="small text-muted">{{ $appointment->clinic?->area }}</div>
                    </div>
                    <div class="col-sm-6">
                        <div class="rx-label">Visit date</div>
                        <div class="rx-value fw-semibold">
                            {{ $appointment->appointment_date?->format('l, d F Y') }} &middot; {{ $appointment->formatted_time }}
                        </div>
                        <div class="small text-muted">Status: {{ $appointment->status_label }}</div>
                    </div>
                </div>

                <hr class="rx-rule">

                <div class="row g-4">
                    <div class="col-12">
                        <div class="rx-label">Diagnosis / disease</div>
                        <div class="rx-value">{{ $appointment->disease ?: 'Not recorded' }}</div>
                    </div>
                    <div class="col-12">
                        <div class="rx-label">Allergies</div>
                        <div class="rx-value">
                            @if (filled($appointment->allergies))
                                <span class="badge text-bg-danger mb-1">{{ $appointment->allergies }}</span>
                            @else
                                <span class="text-muted">None recorded</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="rx-label">Prescription</div>
                        <div class="rx-value border rounded-3 p-3 bg-light">
                            {{ $appointment->prescription_details ?: 'No prescription has been recorded for this visit yet.' }}
                        </div>
                    </div>
                </div>

                <hr class="rx-rule">

                <div class="d-flex flex-wrap justify-content-between align-items-end gap-3">
                    <div class="small text-muted" style="max-width: 420px">
                        <i class="bi bi-info-circle me-1"></i>
                        This document was generated by {{ config('app.name') }} and is valid for this appointment only.
                        Bring it with you when you visit the clinic.
                    </div>
                    <div class="text-end">
                        <div class="rx-sign"></div>
                        <div class="small text-muted mt-1">Doctor's signature</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>