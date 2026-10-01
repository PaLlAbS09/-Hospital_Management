<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinic\StoreScheduleRequest;
use App\Models\Clinic;
use App\Models\ClinicSchedule;
use App\Models\Doctor;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Clinic schedule management (doctor allocation per date / capacity).
 */
class ScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $clinic = $this->clinic();

        $scope = $request->string('scope')->trim()->toString() ?: 'upcoming';
        $today = today()->toDateString();

        $schedules = $clinic->schedules()
            ->with('doctor')
            ->when($scope === 'past', fn ($q) => $q->whereDate('schedule_date', '<', $today))
            ->when($scope !== 'past', fn ($q) => $q->whereDate('schedule_date', '>=', $today))
            ->ordered()
            ->get();

        return view('clinic.schedules.index', [
            'clinic' => $clinic,
            'schedules' => $schedules,
            'scope' => $scope,
            'doctors' => Doctor::orderBy('first_name')->orderBy('last_name')->get(),
        ]);
    }

    public function store(StoreScheduleRequest $request): RedirectResponse
    {
        $clinic = $this->clinic();

        $doctorId = (int) $request->integer('doctor_id');
        $date = $request->string('schedule_date')->toString();
        $start = $request->string('start_time')->toString();
        $end = $request->string('end_time')->toString();

        // Guard against overlapping schedules for the same doctor on the same day
        // inside this clinic.
        $overlaps = $clinic->schedules()
            ->forDoctor($doctorId)
            ->onDate($date)
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->exists();

        if ($overlaps) {
            return back()
                ->withInput()
                ->with('error', 'This doctor already has an overlapping schedule on the selected date.');
        }

        $schedule = ClinicSchedule::create([
            'clinic_id' => $clinic->clinic_id,
            'doctor_id' => $doctorId,
            'schedule_date' => $date,
            'start_time' => $start,
            'end_time' => $end,
            'patient_capacity' => (int) $request->integer('patient_capacity'),
        ]);

        return back()->with(
            'success',
            'Schedule created for '.$schedule->doctor->full_name.' on '.$schedule->schedule_date.' ('.$schedule->time_range.').'
        );
    }

    public function destroy(ClinicSchedule $schedule): RedirectResponse
    {
        $clinic = $this->clinic();

        abort_unless($schedule->clinic_id === $clinic->clinic_id, 403);

        if ($schedule->slotConsumingAppointments()->exists()) {
            return back()->with('error', 'This schedule already has appointments booked against it and cannot be removed.');
        }

        $schedule->delete();

        return back()->with('success', 'Schedule removed successfully.');
    }

    protected function clinic(): Clinic
    {
        /** @var Clinic $clinic */
        $clinic = Auth::guard('clinic')->user();

        abort_unless($clinic instanceof Clinic, 403);

        return $clinic;
    }
}
