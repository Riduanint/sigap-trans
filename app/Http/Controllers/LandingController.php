<?php

namespace App\Http\Controllers;

use App\Models\Regency;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptLocation;
use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Tampilkan landing page publik SIGAP-TRANS.
     * Angka statistik bersifat dinamis sesuai kabupaten yang diizinkan tampil oleh Super Admin.
     */
    public function index(): View
    {
        $visibleScope = fn ($query) => $query->where('is_visible', true);

        $totalUpt = UptLocation::whereHas('regency', $visibleScope)->count();
        $totalKabupaten = Regency::where('is_visible', true)->count();
        $totalKK = (int) UptLocation::whereHas('regency', $visibleScope)->sum('placement_kk');

        $totalSkp = SkpArea::where('is_active', true)->whereHas('regency', $visibleScope)->count();
        $totalDesaSkp = SkpVillage::whereHas('skpArea', fn ($q) => $q->where('is_active', true)->whereHas('regency', $visibleScope))->count();
        $totalLuasHa = (float) SkpArea::where('is_active', true)->whereHas('regency', $visibleScope)->sum('luas_ha');
        $totalPendudukSkp = (int) SkpVillage::whereHas('skpArea', fn ($q) => $q->where('is_active', true)->whereHas('regency', $visibleScope))->sum('population_total');

        $regencies = Regency::where('is_visible', true)
            ->withCount('uptLocations')
            ->orderByDesc('upt_locations_count')
            ->get();

        return view('landing', compact(
            'totalUpt',
            'totalKabupaten',
            'totalKK',
            'regencies',
            'totalSkp',
            'totalDesaSkp',
            'totalLuasHa',
            'totalPendudukSkp'
        ));
    }
}
