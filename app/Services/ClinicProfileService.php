<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\ClinicAnnouncement;
use App\Models\ClinicOffer;
use App\Models\ClinicSchedule;
use App\Models\DoctorReview;
use Illuminate\Support\Collection;

/**
 * Builds the "why choose this clinic" information that the public profile, the
 * patient portal profile and the Find a Clinic cards all share, so the three
 * can never drift apart.
 */
class ClinicProfileService
{
    public function __construct(private readonly DoctorRatingService $ratings) {}

    /**
     * Everything a single clinic profile page needs.
     *
     * @return array{
     *     clinic: Clinic,
     *     ratedDoctors: Collection<int, array<string, mixed>>,
     *     announcements: Collection<int, ClinicAnnouncement>,
     *     offers: Collection<int, ClinicOffer>,
     *     reviews: Collection<int, DoctorReview>
     * }
     */
    public function forClinic(Clinic $clinic): array
    {
        $doctors = $clinic->doctors()
            ->withRatingSummary($clinic->clinic_id)
            ->orderByDesc('rating_summary.rating_average')
            ->orderBy('first_name')
            ->get();

        return [
            'clinic' => $clinic,
            'ratedDoctors' => $doctors->map(fn ($doctor) => $this->ratings->present($doctor))->values(),
            'announcements' => $clinic->announcements()
                ->with('doctor')
                ->active()
                ->orderByRaw('joining_date IS NULL, joining_date ASC')
                ->orderByDesc('announcement_id')
                ->limit(6)
                ->get(),
            'offers' => $clinic->offers()->current()->latestFirst()->limit(6)->get(),
            'reviews' => $this->ratings->recentReviews($clinic),
        ];
    }

    /**
     * Per-clinic summary used by the "Find a Clinic" cards.
     *
     * Runs a fixed handful of queries no matter how many clinics are on the
     * page, which keeps the directory free of N+1 lookups.
     *
     * @param  Collection<int, Clinic>  $clinics
     * @return Collection<int, array<string, mixed>> Keyed by clinic_id.
     */
    public function summarise(Collection $clinics): Collection
    {
        if ($clinics->isEmpty()) {
            return collect();
        }

        $ids = $clinics->pluck('clinic_id')->all();

        $offers = ClinicOffer::query()
            ->whereIn('clinic_id', $ids)
            ->current()
            ->latestFirst()
            ->get()
            ->groupBy('clinic_id');

        $announcements = ClinicAnnouncement::query()
            ->whereIn('clinic_id', $ids)
            ->active()
            ->orderByRaw('joining_date IS NULL, joining_date ASC')
            ->orderByDesc('announcement_id')
            ->get()
            ->groupBy('clinic_id');

        $reviews = DoctorReview::query()
            ->whereIn('clinic_id', $ids)
            ->public()
            ->selectRaw('clinic_id, AVG(rating) as average, COUNT(*) as total')
            ->groupBy('clinic_id')
            ->get()
            ->keyBy('clinic_id');

        $doctorCounts = ClinicSchedule::query()
            ->whereIn('clinic_id', $ids)
            ->selectRaw('clinic_id, COUNT(DISTINCT doctor_id) as total')
            ->groupBy('clinic_id')
            ->pluck('total', 'clinic_id');

        return $clinics->mapWithKeys(function (Clinic $clinic) use ($offers, $announcements, $reviews, $doctorCounts) {
            $summary = $reviews->get($clinic->clinic_id);

            return [$clinic->clinic_id => [
                'clinic' => $clinic,
                'offers' => $offers->get($clinic->clinic_id, collect()),
                'announcements' => $announcements->get($clinic->clinic_id, collect()),
                'rating_average' => $summary?->average !== null ? round((float) $summary->average, 1) : null,
                'rating_count' => (int) ($summary->total ?? 0),
                'doctor_count' => (int) ($doctorCounts[$clinic->clinic_id] ?? 0),
            ]];
        });
    }
}
