<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Regency;
use App\Models\SkpArea;
use App\Models\SkpVillage;
use App\Models\UptDocument;
use App\Models\UptFamilyCard;
use App\Models\UptLocation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AnalyticsController extends Controller
{
    /**
     * Tampilkan Pusat Analitik & Pelaporan Multi-Domain Terpadu SIGAP-TRANS
     */
    public function index(Request $request): View
    {
        $domain = $request->input('domain', 'upt');
        $validDomains = ['upt', 'skp', 'kependudukan', 'pertanahan', 'dokumen'];
        if (!in_array($domain, $validDomains, true)) {
            $domain = 'upt';
        }

        $regencyId = null;
        if ($request->filled('regency_id') && $request->input('regency_id') !== 'all') {
            $regencyId = (int) $request->input('regency_id');
        }

        $allRegencies = Regency::query()
            ->when(auth()->user()->role !== 'super_admin', fn ($q) => $q->where('is_visible', true))
            ->orderBy('id')
            ->get();

        $domainData = match ($domain) {
            'skp' => $this->getSkpData($regencyId, $allRegencies),
            'kependudukan' => $this->getKependudukanData($regencyId, $allRegencies),
            'pertanahan' => $this->getPertanahanData($regencyId, $allRegencies),
            'dokumen' => $this->getDokumenData($regencyId, $allRegencies),
            default => $this->getUptData($regencyId, $allRegencies),
        };

        $exportPdfUrl = match ($domain) {
            'skp' => route('admin.skp.pdf', array_filter(['regency_id' => $regencyId])),
            'kependudukan' => route('admin.placements.pdf', array_filter(['regency_id' => $regencyId])),
            'pertanahan' => route('admin.sertifikat-tanah.pdf', array_filter(['regency_id' => $regencyId])),
            'dokumen' => route('admin.documents.pdf', array_filter(['regency_id' => $regencyId])),
            default => route('admin.analitik.pdf', array_filter(['regency_id' => $regencyId])),
        };

        $exportExcelUrl = match ($domain) {
            'kependudukan' => route('admin.placements.export', array_filter(['regency_id' => $regencyId])),
            'pertanahan' => route('admin.sertifikat-tanah.export', array_filter(['regency_id' => $regencyId])),
            'upt' => route('admin.reports.excel'),
            default => null,
        };

        return view('admin.analitik.analitik', array_merge([
            'domain' => $domain,
            'regencyId' => $regencyId,
            'allRegencies' => $allRegencies,
            'exportPdfUrl' => $exportPdfUrl,
            'exportExcelUrl' => $exportExcelUrl,
        ], $domainData));
    }

    /**
     * Domain 1: Data UPT & Perkembangan Permukiman
     */
    private function getUptData(?int $regencyId, $allRegencies): array
    {
        $uptQuery = UptLocation::query();
        if ($regencyId) {
            $uptQuery->where('regency_id', $regencyId);
        }

        $totalUpt = (clone $uptQuery)->count();
        $totalPlacementKk = (int) (clone $uptQuery)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $uptQuery)->sum('placement_population');
        $totalHandoverKk = (int) (clone $uptQuery)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $uptQuery)->sum('handover_population');

        $handoverGrowthKk = $totalHandoverKk - $totalPlacementKk;
        $handoverGrowthPct = $totalPlacementKk > 0 ? round(($handoverGrowthKk / $totalPlacementKk) * 100, 1) : 0;

        $definitifUptCount = (clone $uptQuery)->where(function ($q) {
            $q->where(function ($sub) {
                $sub->whereNotNull('handover_year')->where('handover_year', '!=', '');
            })->orWhere('handover_kk', '>', 0);
        })->count();
        $binaanUptCount = max(0, $totalUpt - $definitifUptCount);

        $cleanCount = (clone $uptQuery)->where('issue_status', 'clean')->count();
        $warningCount = (clone $uptQuery)->where('issue_status', 'warning')->count();
        $criticalCount = (clone $uptQuery)->where('issue_status', 'critical')->count();

        $regenciesData = (clone $allRegencies)->map(function ($reg) use ($regencyId) {
            if ($regencyId && $reg->id !== $regencyId) {
                return null;
            }
            $upts = UptLocation::where('regency_id', $reg->id)->get();
            $uptCount = $upts->count();
            $placement = (int) $upts->sum('placement_kk');
            $handover = (int) $upts->sum('handover_kk');
            $clean = $upts->where('issue_status', 'clean')->count();
            $warning = $upts->where('issue_status', 'warning')->count();
            $critical = $upts->where('issue_status', 'critical')->count();

            return (object) [
                'id' => $reg->id,
                'name' => $reg->name,
                'capital_city' => $reg->capital_city,
                'upt_locations_count' => $uptCount,
                'total_placement_kk' => $placement,
                'total_handover_kk' => $handover,
                'growth_kk' => $handover - $placement,
                'clean_count' => $clean,
                'warning_count' => $warning,
                'critical_count' => $critical,
            ];
        })->filter()->values();

        $decadesData = [
            '1950–1959' => ['kk' => 0, 'upt' => 0],
            '1960–1969' => ['kk' => 0, 'upt' => 0],
            '1970–1979' => ['kk' => 0, 'upt' => 0],
            '1980–1989' => ['kk' => 0, 'upt' => 0],
            '1990–1999' => ['kk' => 0, 'upt' => 0],
            '2000–2009' => ['kk' => 0, 'upt' => 0],
            '2010–2025' => ['kk' => 0, 'upt' => 0],
        ];

        $uptRows = (clone $uptQuery)->select('placement_year', 'placement_kk')->get();
        foreach ($uptRows as $u) {
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

        $patterns = (clone $uptQuery)->selectRaw('business_pattern, count(*) as count')
            ->groupBy('business_pattern')
            ->pluck('count', 'business_pattern')
            ->toArray();

        $criticalCases = (clone $uptQuery)->with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('upt_number')
            ->get();

        return compact(
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
            'regenciesData',
            'decadesData',
            'patterns',
            'criticalCases'
        );
    }

    /**
     * Domain 2: Kawasan & Satuan Pemukiman (SKP)
     */
    private function getSkpData(?int $regencyId, $allRegencies): array
    {
        $skpQuery = SkpArea::with(['regency', 'villages']);
        if ($regencyId) {
            $skpQuery->where('regency_id', $regencyId);
        }

        $skpList = $skpQuery->orderBy('kawasan_name')->get();

        $totalSkp = $skpList->count();
        $totalLuasHa = (float) $skpList->sum('luas_ha');
        $totalVillages = (int) $skpList->sum(fn ($s) => $s->villages->count());
        $totalPopulation = (int) $skpList->sum(fn ($s) => $s->villages->sum('population_total'));
        $totalFamily = (int) $skpList->sum(fn ($s) => $s->villages->sum('family_count'));

        $regencySkpData = (clone $allRegencies)->map(function ($reg) use ($regencyId) {
            if ($regencyId && $reg->id !== $regencyId) {
                return null;
            }
            $skps = SkpArea::where('regency_id', $reg->id)->with('villages')->get();
            return (object) [
                'id' => $reg->id,
                'name' => $reg->name,
                'count' => $skps->count(),
                'luas_ha' => round((float) $skps->sum('luas_ha'), 2),
                'villages_count' => $skps->sum(fn ($s) => $s->villages->count()),
                'total_pop' => $skps->sum(fn ($s) => $s->villages->sum('population_total')),
            ];
        })->filter()->values();

        $topVillages = SkpVillage::with('skpArea.regency')
            ->when($regencyId, fn ($q) => $q->whereHas('skpArea', fn ($sq) => $sq->where('regency_id', $regencyId)))
            ->orderByDesc('population_total')
            ->take(10)
            ->get();

        return compact(
            'totalSkp',
            'totalLuasHa',
            'totalVillages',
            'totalPopulation',
            'totalFamily',
            'skpList',
            'regencySkpData',
            'topVillages'
        );
    }

    /**
     * Domain 3: Kependudukan & Registri Warga
     */
    private function getKependudukanData(?int $regencyId, $allRegencies): array
    {
        $uptQuery = UptLocation::query();
        if ($regencyId) {
            $uptQuery->where('regency_id', $regencyId);
        }

        $totalPlacementKk = (int) (clone $uptQuery)->sum('placement_kk');
        $totalPlacementPop = (int) (clone $uptQuery)->sum('placement_population');
        $totalHandoverKk = (int) (clone $uptQuery)->sum('handover_kk');
        $totalHandoverPop = (int) (clone $uptQuery)->sum('handover_population');

        $avgJiwaPerKk = $totalPlacementKk > 0 ? round($totalPlacementPop / $totalPlacementKk, 1) : 0;
        $growthKk = $totalHandoverKk - $totalPlacementKk;
        $growthPct = $totalPlacementKk > 0 ? round(($growthKk / $totalPlacementKk) * 100, 1) : 0;

        $cardQuery = UptFamilyCard::where('stage', 'placement');
        if ($regencyId) {
            $cardQuery->whereHas('uptLocation', fn ($q) => $q->where('regency_id', $regencyId));
        }
        $tpaCount = (clone $cardQuery)->where('transmigrant_type', 'TPA')->count();
        $tpsCount = (clone $cardQuery)->where('transmigrant_type', 'TPS')->count();
        $otherTypeCount = (clone $cardQuery)->whereNotIn('transmigrant_type', ['TPA', 'TPS'])->count();

        $regencyDemographics = (clone $allRegencies)->map(function ($reg) use ($regencyId) {
            if ($regencyId && $reg->id !== $regencyId) {
                return null;
            }
            $upts = UptLocation::where('regency_id', $reg->id)->get();
            $pKK = (int) $upts->sum('placement_kk');
            $pPop = (int) $upts->sum('placement_population');
            $hKK = (int) $upts->sum('handover_kk');
            $hPop = (int) $upts->sum('handover_population');

            return (object) [
                'id' => $reg->id,
                'name' => $reg->name,
                'upt_count' => $upts->count(),
                'placement_kk' => $pKK,
                'placement_pop' => $pPop,
                'handover_kk' => $hKK,
                'handover_pop' => $hPop,
                'growth_kk' => $hKK - $pKK,
                'avg_pop_per_kk' => $pKK > 0 ? round($pPop / $pKK, 1) : 0,
            ];
        })->filter()->values();

        $topPopulationUpts = (clone $uptQuery)->with('regency')
            ->orderByDesc('placement_population')
            ->take(10)
            ->get();

        return compact(
            'totalPlacementKk',
            'totalPlacementPop',
            'avgJiwaPerKk',
            'totalHandoverKk',
            'totalHandoverPop',
            'growthKk',
            'growthPct',
            'tpaCount',
            'tpsCount',
            'otherTypeCount',
            'regencyDemographics',
            'topPopulationUpts'
        );
    }

    /**
     * Domain 4: Pertanahan & Sertipikasi SHM
     */
    private function getPertanahanData(?int $regencyId, $allRegencies): array
    {
        $uptQuery = UptLocation::query();
        if ($regencyId) {
            $uptQuery->where('regency_id', $regencyId);
        }

        $totalUpt = (clone $uptQuery)->count();

        $shmTuntas = (clone $uptQuery)->where(function ($q) {
            $q->where('shm_status', 'ILIKE', '%100%')
              ->orWhere('shm_status', 'ILIKE', '%tuntas%');
        })->count();

        $shmSebagian = (clone $uptQuery)->where(function ($q) {
            $q->where('shm_status', 'ILIKE', '%sebagian%')
              ->orWhere('shm_status', 'ILIKE', '%proses%');
        })->count();

        $shmBelum = max(0, $totalUpt - ($shmTuntas + $shmSebagian));

        $cleanCount = (clone $uptQuery)->where('issue_status', 'clean')->count();
        $warningCount = (clone $uptQuery)->where('issue_status', 'warning')->count();
        $criticalCount = (clone $uptQuery)->where('issue_status', 'critical')->count();

        $regencyLand = (clone $allRegencies)->map(function ($reg) use ($regencyId) {
            if ($regencyId && $reg->id !== $regencyId) {
                return null;
            }
            $upts = UptLocation::where('regency_id', $reg->id)->get();
            $tCount = $upts->count();
            $tuntas = $upts->filter(fn ($u) => stripos($u->shm_status ?? '', '100%') !== false || stripos($u->shm_status ?? '', 'tuntas') !== false)->count();
            $sebagian = $upts->filter(fn ($u) => stripos($u->shm_status ?? '', 'sebagian') !== false || stripos($u->shm_status ?? '', 'proses') !== false)->count();
            $belum = max(0, $tCount - ($tuntas + $sebagian));

            return (object) [
                'id' => $reg->id,
                'name' => $reg->name,
                'upt_count' => $tCount,
                'shm_tuntas' => $tuntas,
                'shm_sebagian' => $sebagian,
                'shm_belum' => $belum,
                'clean_count' => $upts->where('issue_status', 'clean')->count(),
                'warning_count' => $upts->where('issue_status', 'warning')->count(),
                'critical_count' => $upts->where('issue_status', 'critical')->count(),
            ];
        })->filter()->values();

        $landIssues = (clone $uptQuery)->with('regency')
            ->whereIn('issue_status', ['critical', 'warning'])
            ->orderByRaw("CASE WHEN issue_status = 'critical' THEN 1 ELSE 2 END")
            ->orderBy('upt_number')
            ->get();

        return compact(
            'totalUpt',
            'shmTuntas',
            'shmSebagian',
            'shmBelum',
            'cleanCount',
            'warningCount',
            'criticalCount',
            'regencyLand',
            'landIssues'
        );
    }

    /**
     * Domain 5: Arsip Dokumen & Warkah BAST
     */
    private function getDokumenData(?int $regencyId, $allRegencies): array
    {
        $docQuery = UptDocument::with('uptLocation.regency');
        if ($regencyId) {
            $docQuery->whereHas('uptLocation', fn ($q) => $q->where('regency_id', $regencyId));
        }

        $totalDocs = (clone $docQuery)->count();
        $totalBast = (clone $docQuery)->where('document_type', 'BAST')->count();
        $totalSk = (clone $docQuery)->whereIn('document_type', ['SK_MENTERI', 'SK_GUBERNUR'])->count();
        $totalBukuTanah = (clone $docQuery)->where('document_type', 'BUKU_TANAH')->count();
        $totalSizeKb = (int) (clone $docQuery)->sum('file_size_kb');
        $totalSizeMb = round($totalSizeKb / 1024, 2);

        $regencyDocs = (clone $allRegencies)->map(function ($reg) use ($regencyId) {
            if ($regencyId && $reg->id !== $regencyId) {
                return null;
            }
            $uptIds = UptLocation::where('regency_id', $reg->id)->pluck('id');
            $docs = UptDocument::whereIn('upt_location_id', $uptIds)->get();

            return (object) [
                'id' => $reg->id,
                'name' => $reg->name,
                'upt_count' => $uptIds->count(),
                'total_docs' => $docs->count(),
                'total_bast' => $docs->where('document_type', 'BAST')->count(),
                'total_sk' => $docs->whereIn('document_type', ['SK_MENTERI', 'SK_GUBERNUR'])->count(),
                'total_buku_tanah' => $docs->where('document_type', 'BUKU_TANAH')->count(),
                'size_mb' => round($docs->sum('file_size_kb') / 1024, 2),
            ];
        })->filter()->values();

        $recentDocs = (clone $docQuery)->with('uploader')
            ->latest()
            ->take(10)
            ->get();

        return compact(
            'totalDocs',
            'totalBast',
            'totalSk',
            'totalBukuTanah',
            'totalSizeKb',
            'totalSizeMb',
            'regencyDocs',
            'recentDocs'
        );
    }

    /**
     * Cetak Dokumen Ringkasan Eksekutif (Executive Briefing PDF) format A4 Portrait
     */
    public function exportPdf(Request $request)
    {
        $regencyId = null;
        if ($request->filled('regency_id') && $request->input('regency_id') !== 'all') {
            $regencyId = (int) $request->input('regency_id');
        }

        $visibleRegencies = Regency::query()
            ->when(auth()->user()->role !== 'super_admin', fn ($query) => $query->where('is_visible', true))
            ->when($regencyId, fn ($query) => $query->where('id', $regencyId))
            ->orderBy('id')
            ->get();
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

        $criticalCases = (clone $uptQuery)->with('regency')
            ->where('issue_status', 'critical')
            ->orderBy('upt_number')
            ->get();

        if ($regencyId && $visibleRegencies->isNotEmpty()) {
            $filterRegencyName = 'Wilayah ' . $visibleRegencies->first()->name;
        } elseif (auth()->user()->role === 'super_admin') {
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
