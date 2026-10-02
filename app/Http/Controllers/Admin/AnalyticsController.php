<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\UptFamilyCard;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Tampilkan Ruang Kendali Visual & Analisis Arah Kebijakan Transmigrasi Kalsel
     */
    public function index(): View
    {
        // 1. KPI Makro Provinsi
        $totalUpt = UptLocation::count();
        $totalPlacementKk = (int) UptLocation::sum('placement_kk');
        $totalPlacementPop = (int) UptLocation::sum('placement_population');
        $totalHandoverKk = (int) UptLocation::sum('handover_kk');
        $totalHandoverPop = (int) UptLocation::sum('handover_population');

        $handoverGrowthKk = $totalHandoverKk - $totalPlacementKk;
        $handoverGrowthPct = $totalPlacementKk > 0 ? round(($handoverGrowthKk / $totalPlacementKk) * 100, 1) : 0;

        // Status Penyerahan (Definitif Pemda vs Binaan Provinsi)
        $definitifUptCount = UptLocation::whereNotNull('handover_year')->orWhere('handover_kk', '>', 0)->count();
        $binaanUptCount = max(0, $totalUpt - $definitifUptCount);

        // Status Agraria & Legalitas
        $cleanCount = UptLocation::where('issue_status', 'clean')->count();
        $warningCount = UptLocation::where('issue_status', 'warning')->count();
        $criticalCount = UptLocation::where('issue_status', 'critical')->count();

        // 2. Matriks Komparasi Performa 9 Kabupaten
        $regencies = Regency::withCount('uptLocations')
            ->withSum('uptLocations as total_placement_kk', 'placement_kk')
            ->withSum('uptLocations as total_placement_pop', 'placement_population')
            ->withSum('uptLocations as total_handover_kk', 'handover_kk')
            ->withSum('uptLocations as total_handover_pop', 'handover_population')
            ->orderBy('id')
            ->get()
            ->map(function ($reg) {
                $uptCount = $reg->upt_locations_count ?? 0;
                $placement = (int) ($reg->total_placement_kk ?? 0);
                $handover = (int) ($reg->total_handover_kk ?? 0);

                // Hitung UPT clean vs berisiko per kabupaten
                $clean = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'clean')->count();
                $warning = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'warning')->count();
                $critical = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'critical')->count();

                $reg->clean_count = $clean;
                $reg->warning_count = $warning;
                $reg->critical_count = $critical;
                $reg->growth_kk = $handover - $placement;
                $reg->handover_rate = $placement > 0 ? round(($handover / $placement) * 100, 1) : 100;

                return $reg;
            });

        // 3. Tren Transmigrasi per Dekade (1950 - 2025)
        $decadesData = [
            '1950–1959' => ['kk' => 0, 'upt' => 0],
            '1960–1969' => ['kk' => 0, 'upt' => 0],
            '1970–1979' => ['kk' => 0, 'upt' => 0],
            '1980–1989' => ['kk' => 0, 'upt' => 0],
            '1990–1999' => ['kk' => 0, 'upt' => 0],
            '2000–2009' => ['kk' => 0, 'upt' => 0],
            '2010–2025' => ['kk' => 0, 'upt' => 0],
        ];

        $allUpts = UptLocation::select('placement_year', 'placement_kk')->get();
        foreach ($allUpts as $u) {
            $year = (int) $u->placement_year;
            $kk = (int) $u->placement_kk;

            if ($year >= 1950 && $year <= 1959) {
                $decadesData['1950–1959']['kk'] += $kk;
                $decadesData['1950–1959']['upt']++;
            } elseif ($year >= 1960 && $year <= 1969) {
                $decadesData['1960–1969']['kk'] += $kk;
                $decadesData['1960–1969']['upt']++;
            } elseif ($year >= 1970 && $year <= 1979) {
                $decadesData['1970–1979']['kk'] += $kk;
                $decadesData['1970–1979']['upt']++;
            } elseif ($year >= 1980 && $year <= 1989) {
                $decadesData['1980–1989']['kk'] += $kk;
                $decadesData['1980–1989']['upt']++;
            } elseif ($year >= 1990 && $year <= 1999) {
                $decadesData['1990–1999']['kk'] += $kk;
                $decadesData['1990–1999']['upt']++;
            } elseif ($year >= 2000 && $year <= 2009) {
                $decadesData['2000–2009']['kk'] += $kk;
                $decadesData['2000–2009']['upt']++;
            } elseif ($year >= 2010) {
                $decadesData['2010–2025']['kk'] += $kk;
                $decadesData['2010–2025']['upt']++;
            }
        }

        // 4. Komposisi Transmigran (TPA vs TPS)
        $tpaKk = UptFamilyCard::where('stage', 'placement')->where('transmigrant_type', 'TPA')->count();
        $tpsKk = UptFamilyCard::where('stage', 'placement')->where('transmigrant_type', 'TPS')->count();
        $totalRegKk = $tpaKk + $tpsKk;

        // 5. Kasus Kritis Prioritas Mediasi
        $criticalCases = UptLocation::with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('upt_number')
            ->get();

        return view('admin.analitik.analitik', compact(
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'handoverGrowthKk',
            'handoverGrowthPct',
            'definitifUptCount',
            'binaanUptCount',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'regencies',
            'decadesData',
            'tpaKk',
            'tpsKk',
            'criticalCases'
        ));
    }

    /**
     * Cetak Dokumen Ringkasan Eksekutif (Executive Briefing PDF) format A4 Portrait
     */
    public function exportPdf()
    {
        $visibleRegencies = Regency::query()->when(auth()->user()->role !== 'super_admin', fn ($query) => $query->where('is_visible', true))->orderBy('id')->get();
        $visibleRegencyIds = $visibleRegencies->pluck('id')->toArray();

        $uptQuery = UptLocation::whereIn('regency_id', $visibleRegencyIds);

        $totalUpt = (clone $uptQuery)->count();
        $totalPlacementKk = (int) (clone $uptQuery)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $uptQuery)->sum('placement_population');
        $totalHandoverKk = (int) (clone $uptQuery)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $uptQuery)->sum('handover_population');

        $cleanCount = (clone $uptQuery)->where('issue_status', 'clean')->count();
        $warningCount = (clone $uptQuery)->where('issue_status', 'warning')->count();
        $criticalCount = (clone $uptQuery)->where('issue_status', 'critical')->count();

        // 2. Matriks Komparasi Kabupaten Terpublikasi
        $regencyMatrix = Regency::whereIn('id', $visibleRegencyIds)
            ->withCount('uptLocations')
            ->withSum('uptLocations as total_placement_kk', 'placement_kk')
            ->withSum('uptLocations as total_handover_kk', 'handover_kk')
            ->orderBy('id')
            ->get()
            ->map(function ($reg) {
                $clean = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'clean')->count();
                $warning = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'warning')->count();
                $critical = UptLocation::where('regency_id', $reg->id)->where('issue_status', 'critical')->count();
                $placement = (int) ($reg->total_placement_kk ?? 0);
                $handover = (int) ($reg->total_handover_kk ?? 0);

                return [
                    'name' => $reg->name,
                    'total_upt' => $reg->upt_locations_count ?? 0,
                    'total_placement_kk' => $placement,
                    'total_handover_kk' => $handover,
                    'growth_kk' => $handover - $placement,
                    'clean_count' => $clean,
                    'warning_count' => $warning,
                    'critical_count' => $critical,
                ];
            });

        // 3. Data Dekade Historis
        $decadesMap = [
            '1950-an' => ['decade' => '1950-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '1960-an' => ['decade' => '1960-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '1970-an' => ['decade' => '1970-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '1980-an' => ['decade' => '1980-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '1990-an' => ['decade' => '1990-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '2000-an' => ['decade' => '2000-an', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
            '2010–2025' => ['decade' => '2010–2025', 'total_kk' => 0, 'total_pop' => 0, 'count' => 0],
        ];

        $upts = (clone $uptQuery)->select('placement_year', 'placement_kk', 'placement_population')->get();
        foreach ($upts as $u) {
            $year = (int) $u->placement_year;
            $kk = (int) $u->placement_kk;
            $pop = (int) $u->placement_population;

            if ($year >= 1950 && $year <= 1959) {
                $decadesMap['1950-an']['total_kk'] += $kk;
                $decadesMap['1950-an']['total_pop'] += $pop;
                $decadesMap['1950-an']['count']++;
            } elseif ($year >= 1960 && $year <= 1969) {
                $decadesMap['1960-an']['total_kk'] += $kk;
                $decadesMap['1960-an']['total_pop'] += $pop;
                $decadesMap['1960-an']['count']++;
            } elseif ($year >= 1970 && $year <= 1979) {
                $decadesMap['1970-an']['total_kk'] += $kk;
                $decadesMap['1970-an']['total_pop'] += $pop;
                $decadesMap['1970-an']['count']++;
            } elseif ($year >= 1980 && $year <= 1989) {
                $decadesMap['1980-an']['total_kk'] += $kk;
                $decadesMap['1980-an']['total_pop'] += $pop;
                $decadesMap['1980-an']['count']++;
            } elseif ($year >= 1990 && $year <= 1999) {
                $decadesMap['1990-an']['total_kk'] += $kk;
                $decadesMap['1990-an']['total_pop'] += $pop;
                $decadesMap['1990-an']['count']++;
            } elseif ($year >= 2000 && $year <= 2009) {
                $decadesMap['2000-an']['total_kk'] += $kk;
                $decadesMap['2000-an']['total_pop'] += $pop;
                $decadesMap['2000-an']['count']++;
            } elseif ($year >= 2010) {
                $decadesMap['2010–2025']['total_kk'] += $kk;
                $decadesMap['2010–2025']['total_pop'] += $pop;
                $decadesMap['2010–2025']['count']++;
            }
        }
        $decades = array_values($decadesMap);

        // 4. Kasus Kritis Prioritas Mediasi di Kabupaten Terpublikasi
        $criticalCases = (clone $uptQuery)->with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('upt_number')
            ->get();

        if (auth()->user()->role === 'super_admin') {
            $filterRegencyName = 'Seluruh wilayah administrasi';
        } elseif ($visibleRegencies->count() === 9) {
            $filterRegencyName = 'Seluruh Wilayah (9 Kabupaten Binaan Kalsel)';
        } else {
            $filterRegencyName = "{$visibleRegencies->count()} Kabupaten Terpublikasi (" . $visibleRegencies->pluck('name')->implode(', ') . ')';
        }

        $signCity = 'Banjarbaru';
        $signDate = date('d F Y');
        $orientation = 'portrait';

        $pdf = Pdf::loadView('admin.reports.pdf.analytics', compact(
            'totalUpt',
            'totalPlacementKk',
            'totalPlacementPop',
            'totalHandoverKk',
            'totalHandoverPop',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'regencyMatrix',
            'decades',
            'criticalCases',
            'filterRegencyName',
            'signCity',
            'signDate',
            'orientation'
        ))->setPaper('a4', 'portrait');

        $filename = 'Executive_Briefing_Kebijakan_Transmigrasi_Kalsel_' . date('Ymd_His') . '.pdf';
        return $pdf->stream($filename);
    }
}
