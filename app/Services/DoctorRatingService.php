<?php

namespace App\Services;

use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorReview;
use Illuminate\Support\Collection;

class DoctorRatingService
{
    /** Ratings required before the highest badge may be awarded. */
    public const BEST_CHOICE_MIN_RATINGS = 3;

    /** Ratings required before the "highly recommended" badge may be awarded. */
    public const RECOMMENDED_MIN_RATINGS = 1;

    public const BADGE_BEST_CHOICE = 'best_choice';

    public const BADGE_RECOMMENDED = 'highly_recommended';

    /**
     * Badge for one doctor, based on the average and number of public ratings.
     *
     * @return array{key: string, label: string, tone: string}|null
     */
    public function badgeFor(?float $average, int $count): ?array
    {
        if ($average === null || $count === 0) {
            return null;
        }

        if ($count >= self::BEST_CHOICE_MIN_RATINGS && $average >= 4.5) {
            return [
                'key' => self::BADGE_BEST_CHOICE,
                'label' => 'Best choice',
                'tone' => 'gold',
            ];
        }

        if ($count >= self::RECOMMENDED_MIN_RATINGS && $average >= 4.0) {
            return [
                'key' => self::BADGE_RECOMMENDED,
                'label' => 'Highly recommended',
                'tone' => 'indigo',
            ];
        }

        return null;
    }

    /**
     * Doctors rated at a clinic, each decorated with `badge` and `badge_label`.
     *
     * The badge text is personalised with the doctor's specialization so the
     * card reads "Best choice in Orthopedics".
     *
     * @return Collection<int, array{doctor: Doctor, average: float, count: int, badge: ?array, badge_label: ?string}>
     */
    public function leaderboard(?Clinic $clinic = null, int $limit = 12): Collection
    {
        $doctors = Doctor::query()
            ->withRatingSummary($clinic?->clinic_id)
            ->whereNotNull('rating_summary.rating_average')
            ->orderByDesc('rating_summary.rating_average')
            ->orderByDesc('rating_summary.rating_count')
            ->limit($limit)
            ->get();

        return $doctors
            ->map(fn (Doctor $doctor) => $this->present($doctor))
            ->values();
    }

    /**
     * The single highest rated doctor of a clinic, if any patient has rated one.
     *
     * @return Collection<int, array{doctor: Doctor, average: float, count: int, badge: ?array, badge_label: ?string}>
     */
    public function topDoctorFor(Clinic $clinic): Collection
    {
        return $this->leaderboard($clinic, 1);
    }

    /**
     * Public reviews for a clinic, newest first, ready for the testimonial list.
     *
     * @return Collection<int, DoctorReview>
     */
    public function recentReviews(Clinic $clinic, int $limit = 6): Collection
    {
        return DoctorReview::query()
            ->with(['patient', 'doctor'])
            ->public()
            ->forClinic($clinic->clinic_id)
            ->latestFirst()
            ->limit($limit)
            ->get();
    }

    /**
     * Attach the rating summary and badge to a single doctor.
     *
     * @return array{doctor: Doctor, average: float, count: int, badge: ?array, badge_label: ?string}
     */
    public function present(Doctor $doctor): array
    {
        $average = $doctor->rating_average ?? 0.0;
        $count = $doctor->rating_count;
        $badge = $this->badgeFor($doctor->rating_average, $count);

        return [
            'doctor' => $doctor,
            'average' => $average,
            'count' => $count,
            'badge' => $badge,
            'badge_label' => $badge !== null
                ? $badge['label'].' in '.($doctor->specialization ?: 'this department')
                : null,
        ];
    }

    /**
     * The plain rating numbers for one doctor, without touching the badge rules.
     *
     * @return array{average: float, count: int}
     */
    public function summaryFor(int $doctorId): array
    {
        $row = DoctorReview::query()
            ->public()
            ->forDoctor($doctorId)
            ->selectRaw('AVG(rating) as average, COUNT(*) as total')
            ->first();

        return [
            'average' => $row?->average !== null ? round((float) $row->average, 1) : 0.0,
            'count' => (int) ($row?->total ?? 0),
        ];
    }
}
