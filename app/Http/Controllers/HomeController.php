<?php

namespace App\Http\Controllers;

use App\Http\Requests\Public\StoreContactQueryRequest;
use App\Models\Clinic;
use App\Models\SystemQuery;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, unauthenticated pages: landing page, clinic directory and contact form.
 */
class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $area = $request->string('area')->trim()->toString();

        $areas = Clinic::query()
            ->approved()
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $clinics = Clinic::query()
            ->approved()
            ->inArea($area)
            ->withCount('schedules')
            ->orderBy('clinic_name')
            ->limit(9)
            ->get();

        return view('welcome', [
            'area' => $area,
            'areas' => $areas,
            'clinics' => $clinics,
            'totalClinics' => Clinic::approved()->count(),
            'totalDoctors' => \App\Models\Doctor::count(),
            'totalPatients' => \App\Models\Patient::count(),
            'totalAppointments' => \App\Models\Appointment::count(),
        ]);
    }

    /**
     * Full area-wise clinic directory.
     */
    public function clinics(Request $request): View
    {
        $area = $request->string('area')->trim()->toString();

        $areas = Clinic::query()
            ->approved()
            ->whereNotNull('area')
            ->distinct()
            ->orderBy('area')
            ->pluck('area');

        $clinics = Clinic::query()
            ->approved()
            ->inArea($area)
            ->withCount('schedules')
            ->orderBy('area')
            ->orderBy('clinic_name')
            ->paginate(12)
            ->withQueryString();

        return view('public.clinics', compact('area', 'areas', 'clinics'));
    }

    /**
     * Contact Us submissions are stored in `system_queries`.
     */
    public function storeQuery(StoreContactQueryRequest $request): RedirectResponse
    {
        SystemQuery::create([
            'user_name' => $request->string('user_name')->trim()->toString(),
            'email' => $request->string('email')->trim()->lower()->toString(),
            'contact_number' => $request->string('contact_number')->trim()->toString(),
            'message' => $request->string('message')->trim()->toString(),
        ]);

        return redirect()
            ->route('home')
            ->withFragment('contact')
            ->with('success', 'Thank you, '.$request->string('user_name')->trim().'. Your query has been submitted and our team will contact you soon.');
    }
}
