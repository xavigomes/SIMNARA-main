<?php

namespace App\Http\Controllers;

use App\Models\Submission;
use App\Models\User; // Pastikan ini di-import jika digunakan (seperti di method index)
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail; // <<< TAMBAHKAN INI
use Exception;
use App\Mail\DispositionMail; // <<< TAMBAHKAN INI (Asumsi Anda sudah membuat Mailable Class ini)

class SubmissionController extends Controller
{
    public function index()
    {
        try {
            // Jika ini untuk halaman USER dashboard yang melihat submission dia sendiri:
            $submissions = Auth::user()->submissions()->latest()->get(); // Pastikan relasi submissions() ada di model User

            // Jika ini digunakan untuk halaman utama user dashboard,
            // $latestSubmission mungkin tidak terlalu relevan atau perlu disesuaikan
            $latestSubmission = $submissions->first();

            // Mengubah view ke 'user.dashboard' atau 'submissions.index' untuk user
            // Jika ini adalah dashboard user, mungkin view-nya 'dashboard' atau 'user.dashboard'
            return view('user.dashboard', compact('submissions', 'latestSubmission'));
            // Catatan: Jika ini *bukan* untuk user dashboard, dan Anda punya SubmissionController (Admin) juga,
            // maka method index() ini mungkin tidak perlu di SubmissionController yang user-facing ini.
            // Sesuaikan dengan struktur routing dan tampilan Anda.

        } catch (Exception $e) {
            Log::error('Error loading user submissions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memuat data pengajuan Anda.');
        }
    }

    public function create()
    {
        return view('submissions.create');
    }

    public function createForm1()
    {
        return view('submissions.form1');
    }

    public function createForm2()
    {
        return view('submissions.form2');
    }

    public function createForm3()
    {
        return view('submissions.form3');
    }

    protected function handleFileUpload($file)
    {
        if (!$file) return null;

        try {
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('documents', $filename, 'public'); // Simpan di public/documents
            return $filename;
        } catch (Exception $e) {
            Log::error('File upload error: ' . $e->getMessage());
            throw new Exception('Gagal mengunggah file');
        }
    }

    protected function deleteFile($path)
    {
        if (!$path) return;

        $fullPath = 'documents/' . $path; // Sesuaikan dengan path 'documents'
        if (Storage::disk('public')->exists($fullPath)) {
            Storage::disk('public')->delete($fullPath);
        }
    }

    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // Validasi dasar
            $dataValid = $request->validate([
                'nama' => 'required|string|max:255',
                'alamat' => 'required|string',
                'tujuan' => 'required|string',
                'jenis_form' => 'required|in:form1,form2,form3',
                'document' => 'nullable|file|mimes:pdf|max:2048',
            ]);

            // Set nilai default
            $dataValid = array_merge($dataValid, [
                'menimbang' => null,
                'kepada' => null,
                'untuk' => null,
                'jangka_waktu' => null,
                'document_path' => null,
                'user_id' => Auth::id(),
                'status' => 'pending'
            ]);

            // Upload file jika ada
            if ($request->hasFile('document')) {
                $dataValid['document_path'] = $this->handleFileUpload($request->file('document'));
            }

            // Validasi khusus form1
            if ($request->jenis_form === 'form1') {
                $form1Data = $request->validate([
                    'menimbang' => 'required|string',
                    'kepada' => 'required|array',
                    'kepada.*.nama' => 'required|string|max:255',
                    'kepada.*.nip_nik' => 'required|string|max:20',
                    'kepada.*.jabatan' => 'required|string|max:255',
                    'untuk' => 'required|string',
                    'jangka_waktu' => 'required|string',
                ]);

                $dataValid = array_merge($dataValid, $form1Data);
                $dataValid['kepada'] = json_encode($dataValid['kepada']);
            }

            $submission = Submission::create($dataValid);
            DB::commit();

            return redirect()->route('submissions.success', $submission->id);

        } catch (Exception $e) {
            DB::rollBack();

            if (isset($dataValid['document_path'])) {
                $this->deleteFile($dataValid['document_path']);
            }

            Log::error('Error creating submission: ' . $e->getMessage());
            return redirect()->back()
                ->withInput()
                ->with('error', 'Terjadi kesalahan saat mengirim pengajuan. Silakan coba lagi.');
        }
    }

    /**
     * Menampilkan halaman konfirmasi setelah pengajuan berhasil dibuat.
     *
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\View\View
     */
    public function success(Submission $submission)
    {
        // Pastikan hanya user pemilik submission atau admin yang bisa melihat halaman ini
        // if (Auth::id() !== $submission->user_id && !Auth::user()->isAdmin()) {
        //     abort(403, 'Unauthorized action.');
        // }
        return view('submissions.success', compact('submission'));
    }

    /**
     * Mengirim email disposisi via SMTP (server-side).
     * Ini dipanggil dari AJAX di halaman success.blade.php.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Submission  $submission
     * @return \Illuminate\Http\JsonResponse
     */
    public function sendDispositionEmail(Request $request, Submission $submission)
    {
        // Anda mungkin ingin menambahkan otorisasi di sini,
        // misalnya, hanya jika user yang login adalah pemilik submission
        // atau jika user memiliki peran tertentu (misal, 'admin')
        // if (Auth::id() !== $submission->user_id) {
        //     return response()->json(['status' => 'error', 'message' => 'Unauthorized.'], 403);
        // }
        // Atau gunakan Policy: $this->authorize('sendEmail', $submission);

        $request->validate([
            'recipient_email' => 'required|email',
            'recipient_name' => 'required|string|max:255',
        ]);

        $recipientEmail = $request->recipient_email;
        $recipientName = $request->recipient_name;

        try {
            // Buat objek Mailable dan kirim
            Mail::to($recipientEmail)->send(new DispositionMail($submission, $recipientName));

            return response()->json([
                'status' => 'success',
                'message' => 'Email disposisi berhasil dikirim ke ' . $recipientName . '!'
            ]);
        } catch (\Exception $e) { // Gunakan \Exception karena ini adalah root namespace Exception
            Log::error('Gagal mengirim email disposisi ke ' . $recipientEmail . ': ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Gagal mengirim email: ' . $e->getMessage()
            ], 500);
        }
    }


    // Metode-metode lain seperti destroy, bulkDestroy, show, update, approve, reject,
    // downloadDocument, viewPdf, dll. yang Anda miliki di SubmissionController ini.
    // Jika ada duplikasi dengan AdminSubmissionController, Anda harus memutuskan
    // mana yang akan digunakan di masing-masing area (user vs admin)
    // dan mungkin memindahkan fungsi yang benar-benar hanya untuk admin ke AdminSubmissionController.


    // Contoh metode show() yang sudah ada di bawah, pastikan tidak duplikasi
    public function show(Submission $submission)
    {
        try {
            $this->authorize('view', $submission);
            return view('submissions.show', compact('submission'));
        } catch (Exception $e) {
            Log::error('Error showing submission: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menampilkan pengajuan.');
        }
    }

    public function destroy(Submission $submission)
    {
        try {
            Log::info('Attempting to delete submission: ' . $submission->id);
            $this->authorize('delete', $submission);
            Log::info('Authorization passed');

            DB::beginTransaction();

            if ($submission->document_path) {
                Log::info('Deleting document: ' . $submission->document_path);
                Storage::disk('public')->delete('documents/' . $submission->document_path);
            }

            if ($submission->admin_document_path) {
                Log::info('Deleting admin document: ' . $submission->admin_document_path);
                Storage::disk('public')->delete('documents/' . $submission->admin_document_path);
            }

            $submission->delete();
            Log::info('Submission deleted successfully');

            DB::commit();

            return redirect()->back()->with('success', 'Pengajuan berhasil dihapus!');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting submission: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus pengajuan: ' . $e->getMessage());
        }
    }

    public function bulkDestroy(Request $request)
    {
        try {
            $request->validate([
                'submission_ids' => 'required|array',
                'submission_ids.*' => 'exists:submissions,id'
            ]);

            DB::beginTransaction();

            $submissions = Submission::whereIn('id', $request->submission_ids)
                ->where('user_id', Auth::id())
                ->get();

            foreach ($submissions as $submission) {
                $this->authorize('delete', $submission);
                $this->deleteFile($submission->document_path);
                $this->deleteFile($submission->admin_document_path);
                $submission->delete();
            }

            DB::commit();

            $count = count($submissions);
            return redirect()->back()
                ->with('success', "{$count} pengajuan berhasil dihapus!");

        } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
            DB::rollBack();
            Log::error('Authorization error in bulk deletion: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki izin untuk menghapus beberapa pengajuan.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error in bulk deletion: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menghapus pengajuan. Silakan coba lagi.');
        }
    }

    public function update(Request $request, Submission $submission)
    {
        try {
            $this->authorize('update', $submission);

            $validated = $request->validate([
                'status' => 'required|in:pending,approved,rejected'
            ]);

            $submission->update($validated);

            return redirect()->back()
                ->with('success', 'Status pengajuan berhasil diperbarui!');
        } catch (Exception $e) {
            Log::error('Error updating submission: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat memperbarui status.');
        }
    }

    public function approve(Submission $submission)
    {
        try {
            $this->authorize('update', $submission);
            $submission->update(['status' => 'approved']);

            return response()->json([
                'message' => 'Pengajuan berhasil disetujui!'
            ]);
        } catch (Exception $e) {
            Log::error('Error approving submission: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat menyetujui pengajuan.'
            ], 500);
        }
    }

    public function reject(Submission $submission)
    {
        try {
            $this->authorize('update', $submission);
            $submission->update(['status' => 'rejected']);

            return response()->json([
                'message' => 'Pengajuan berhasil ditolak!'
            ]);
        } catch (Exception $e) {
            Log::error('Error rejecting submission: ' . $e->getMessage());
            return response()->json([
                'error' => 'Terjadi kesalahan saat menolak pengajuan.'
            ], 500);
        }
    }

    public function downloadDocument(Submission $submission)
    {
        try {
            $this->authorize('view', $submission);

            if (!$submission->document_path) {
                return back()->with('error', 'Dokumen tidak ditemukan');
            }

            $path = storage_path('app/public/documents/' . $submission->document_path);

            if (!file_exists($path)) {
                return back()->with('error', 'File tidak ditemukan di server.');
            }

            return response()->download($path);

        } catch (Exception $e) {
            Log::error('Error downloading document: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat mengunduh dokumen.');
        }
    }

    public function viewPdf(Submission $submission)
    {
        try {
            $this->authorize('view', $submission);

            if ($submission->jenis_form !== 'form1') {
                return back()->with('error', 'Fungsi ini hanya untuk Form 1.');
            }

            if ($submission->document_path) {
                $path = storage_path('app/public/documents/' . $submission->document_path);
                if (file_exists($path)) {
                    return response()->file($path, ['Content-Type' => 'application/pdf']);
                } else {
                    return back()->with('error', 'File PDF Form 1 tidak ditemukan.');
                }
            } else {
                return back()->with('error', 'Dokumen Form 1 tidak tersedia.');
            }

        } catch (Exception $e) {
            Log::error('Error viewing PDF for submission ' . $submission->id . ': ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan saat menampilkan PDF.');
        }
    }
}