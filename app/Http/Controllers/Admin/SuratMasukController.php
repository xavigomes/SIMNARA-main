<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Mail\DisposisiSurat;
use Illuminate\Support\Facades\Mail;



class SuratMasukController extends Controller
{

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_surat' => 'required|string|max:255',
            'tanggal' => 'required|date',
            'nama_pengirim' => 'required|string|max:255',
            'perihal' => 'required|string',
            'file_pdf' => 'required|mimes:pdf|max:2048',
            'disposisi' => 'required|string',
        ]);

        try {
            // Upload file
            if ($request->hasFile('file_pdf')) {
                $file = $request->file('file_pdf');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('public/surat_masuk', $filename);
                $validated['file_path'] = $filename;
            }

            // Simpan ke DB
            $surat = SuratMasuk::create($validated);

            // Email tujuan berdasarkan disposisi
            $disposisi = $request->input('disposisi');
            $emailMap = [
                "IPDS" => "ipds3205@bps.go.id",
                "TU" => "sastiprasasti01@gmail.com",
                "Kepala Kantor" => "nevihendri@bps.go.id",
                "Neraca" => "neraca@example.com",
                "Sosial" => "sosial@example.com",
                "Distribusi" => "distribusi@example.com",
                "Produksi" => "produksi@example.com"
            ];

            if ($disposisi !== 'Tidak Disposisi' && isset($emailMap[$disposisi])) {
                Mail::to($emailMap[$disposisi])->send(new DisposisiSurat(
                    strtoupper($disposisi),
                    $request->nomor_surat,
                    $request->nama_pengirim,
                    $request->perihal
                ));
            }


            return redirect()->route('admin.surat-masuk')->with('success', 'Surat masuk berhasil ditambahkan dan email dikirim jika ada disposisi.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menambahkan surat masuk: ' . $e->getMessage());
        }
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'nomor_surat' => 'required|string|max:255',
    //         'tanggal' => 'required|date',
    //         'nama_pengirim' => 'required|string|max:255',
    //         'tujuan' => 'required|string',
    //         'file_pdf' => 'required|mimes:pdf|max:2048'
    //     ]);

    //     $surat = SuratMasuk::create($request->all());

    //     try {
    //         if ($request->hasFile('file_pdf')) {
    //             $file = $request->file('file_pdf');
    //             $filename = time() . '_' . $file->getClientOriginalName();
    //             $file->storeAs('public/surat_masuk', $filename);
    //             $validated['file_path'] = $filename;
    //         }

    //         SuratMasuk::create($validated);


    //         $disposisi = $request->input('disposisi');
    //         $emailMap = [
    //             "IPDS" => "ipds3205@bps.go.id",
    //             "TU" => "sastiprasasti01@gmail.com",
    //             "Kepala Kantor" => "nevihendri@bps.go.id",
    //             "Neraca" => "neraca@example.com",
    //             "Sosial" => "sosial@example.com",
    //             "Distribusi" => "distribusi@example.com",
    //             "Produksi" => "produksi@example.com"
    //         ];

    //         if (!isset($emailMap[$disposisi])) {
    //             return back()->with('error', 'Disposisi tidak valid.');
    //         }

    //         Mail::to($emailMap[$disposisi])->send(new DisposisiSurat(

    //             strtoupper($disposisi),
    //             $request->nomor_surat,
    //             $request->nama_pengirim,
    //             $request->tujuan
    //         ));
    //         return redirect()->route('admin.surat-masuk')
    //             ->with('success', 'Surat masuk berhasil ditambahkan dan email disposisi dikirim');

    //         // return back()->with('success', 'Surat masuk berhasil ditambahkan dan Email disposisi dikirim!');
    //         // return redirect()->route('admin.surat-masuk')
    //         // ->with('success', 'Surat masuk berhasil ditambahkan dan email disposisi dikirim');
    //     } catch (\Exception $e) {
    //         return back()->with('error', 'Gagal menambahkan surat masuk');
    //     }
    // }




    public function download(SuratMasuk $suratMasuk)
    {
        if (!$suratMasuk->file_path || !Storage::exists('public/surat_masuk/' . $suratMasuk->file_path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return Storage::download('public/surat_masuk/' . $suratMasuk->file_path);
    }

    public function view(SuratMasuk $suratMasuk)
    {
        if (!$suratMasuk->file_path || !Storage::exists('public/surat_masuk/' . $suratMasuk->file_path)) {
            return back()->with('error', 'File tidak ditemukan');
        }

        return response()->file(storage_path('app/public/surat_masuk/' . $suratMasuk->file_path));
    }

    public function destroy(SuratMasuk $suratMasuk)
    {
        try {
            if ($suratMasuk->file_path) {
                Storage::delete('public/surat_masuk/' . $suratMasuk->file_path);
            }

            $suratMasuk->delete();
            return back()->with('success', 'Surat masuk berhasil dihapus');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menghapus surat masuk');
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $validated = $request->validate([
                'ids' => 'required|array',
                'ids.*' => 'exists:surat_masuk,id'
            ]);

            $suratMasuk = SuratMasuk::whereIn('id', $validated['ids'])->get();

            foreach ($suratMasuk as $surat) {
                if ($surat->file_path) {
                    Storage::delete('public/surat_masuk/' . $surat->file_path);
                }
                $surat->delete();
            }

            return response()->json(['message' => 'Surat masuk berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Gagal menghapus surat masuk'], 500);
        }
    }

    public function export()
    {
        $suratMasuk = SuratMasuk::all();
        // Implement export logic here if needed
        return back()->with('error', 'Fitur export belum tersedia');
    }
    public function index()
    {
        $suratMasuk = SuratMasuk::latest()->get();

        $totalSurat = SuratMasuk::count();
        $suratBulanIni = SuratMasuk::whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->count();
        $suratBaruMasuk = SuratMasuk::where('created_at', '>=', now()->subDays(7))
            ->count();
        $totalPDF = SuratMasuk::whereNotNull('file_path')->count();

        return view('admin.surat-masuk.index', compact(
            'suratMasuk',
            'totalSurat',
            'suratBulanIni',
            'suratBaruMasuk',
            'totalPDF'
        ));
    }
}
