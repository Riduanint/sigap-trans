<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Regency;
use App\Models\UptDocument;
use App\Models\UptLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DocumentRepositoryController extends Controller
{
    /**
     * Halaman utama Repositori E-Arsip Digital BAST & SK
     */
    public function index(Request $request): View
    {
        $query = UptDocument::with(['uptLocation.regency', 'uploader']);

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('document_type', $request->type);
        }

        if ($request->filled('regency_id') && $request->regency_id !== 'all') {
            $query->whereHas('uptLocation', function ($q) use ($request) {
                $q->where('regency_id', $request->regency_id);
            });
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('document_number', 'ILIKE', "%{$search}%")
                  ->orWhere('file_name', 'ILIKE', "%{$search}%")
                  ->orWhereHas('uptLocation', function ($uq) use ($search) {
                      $uq->where('upt_name', 'ILIKE', "%{$search}%")
                         ->orWhere('current_village_name', 'ILIKE', "%{$search}%");
                  });
            });
        }

        $documents = $query->latest()->paginate(12)->withQueryString();
        $uptLocations = UptLocation::orderBy('upt_number')->get(['id', 'upt_number', 'upt_name', 'regency_id']);
        $regencies = Regency::orderBy('id')->get();

        $stats = [
            'total_docs' => UptDocument::count(),
            'total_bast' => UptDocument::where('document_type', 'BAST')->count(),
            'total_sk' => UptDocument::whereIn('document_type', ['SK_GUBERNUR', 'SK_MENTERI'])->count(),
            'total_buku_tanah' => UptDocument::where('document_type', 'BUKU_TANAH')->count(),
        ];

        return view('admin.documents.documents', compact('documents', 'uptLocations', 'regencies', 'stats'));
    }

    /**
     * Unggah berkas digital BAST / SK baru
     */
    public function upload(Request $request): RedirectResponse
    {
        $request->validate([
            'upt_location_id' => ['required', 'exists:upt_locations,id'],
            'document_type' => ['required', 'in:BAST,SK_GUBERNUR,SK_MENTERI,BUKU_TANAH'],
            'document_number' => ['nullable', 'string', 'max:100'],
            'file' => ['required', 'file', 'mimes:pdf', 'max:20480'], // Maksimal 20 MB
        ]);

        $file = $request->file('file');
        $originalName = $file->getClientOriginalName();
        $fileSizeKb = (int) round($file->getSize() / 1024);

        $upt = UptLocation::findOrFail($request->upt_location_id);
        $fileName = time() . '_' . preg_replace('/[^a-zA-Z0-9_\-\.]/', '_', $originalName);
        $path = $file->storeAs('documents/bast', $fileName, 'public');

        $doc = UptDocument::create([
            'upt_location_id' => $upt->id,
            'document_type' => $request->document_type,
            'document_number' => $request->document_number,
            'file_name' => $originalName,
            'file_path' => $path,
            'file_size_kb' => $fileSizeKb,
            'uploaded_by' => Auth::id(),
        ]);

        // Audit Trail Forensik
        try {
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'UPLOAD_BAST_DOCUMENT',
                'target_table' => 'upt_documents',
                'target_id' => (string) $doc->id,
                'details' => [
                    'upt_number' => $upt->upt_number,
                    'upt_name' => $upt->upt_name,
                    'document_type' => $doc->document_type,
                    'document_number' => $doc->document_number,
                    'file_name' => $doc->file_name,
                    'file_size_kb' => $fileSizeKb,
                ],
                'ip_address' => $request->ip() ?: '127.0.0.1',
                'user_agent' => $request->userAgent() ?: 'Internal Browser',
            ]);
        } catch (\Throwable $e) {
            //
        }

        return redirect()->route('admin.documents.index')
            ->with('success', "Berkas BAST untuk UPT {$upt->upt_name} berhasil diarsipkan ke repositori digital.");
    }

    /**
     * Unduh berkas fisik digital
     */
    public function download(int $id): BinaryFileResponse|RedirectResponse
    {
        $doc = UptDocument::findOrFail($id);

        if (!Storage::disk('public')->exists($doc->file_path)) {
            return back()->with('error', 'Berkas fisik digital tidak ditemukan di vault storage.');
        }

        return response()->download(
            Storage::disk('public')->path($doc->file_path),
            $doc->file_name
        );
    }

    /**
     * Hapus arsip dokumen
     */
    public function destroy(int $id): RedirectResponse
    {
        $doc = UptDocument::findOrFail($id);

        if (Storage::disk('public')->exists($doc->file_path)) {
            Storage::disk('public')->delete($doc->file_path);
        }

        $doc->delete();

        return redirect()->route('admin.documents.index')
            ->with('success', 'Berkas arsip digital berhasil dihapus.');
    }
}
