<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\LetterFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class LetterAttachmentController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()->role?->slug === 'admin';
    }

    private function visibleLetter($id): Letter
    {
        $letter = Letter::findOrFail($id);

        if ($this->isAdmin()) {
            return $letter;
        }

        $unitId = Auth::user()->unit_id;

        $isSender = (int) $letter->sender_unit_id === (int) $unitId;

        $isReceiver = (int) $letter->receiver_unit_id === (int) $unitId
            && $letter->status !== 'draft';

        abort_unless(
            $unitId && ($isSender || $isReceiver),
            403,
            'Anda tidak memiliki akses ke surat ini.'
        );

        return $letter;
    }

    private function allowedDocumentTypes(): array
    {
        return [
            'surat_asli',
            'bukti_penerimaan',
            'bukti_tindak_lanjut',
        ];
    }

    private function uploadRules(): array
    {
        return [
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => [
                'required',
                'file',
                'max:10240',
                'mimes:pdf',
            ],
        ];
    }

    public function store(Request $request, $letterId)
    {
        $letter = $this->visibleLetter($letterId);

        abort_if(
            in_array($letter->status, ['selesai', 'dibatalkan']),
            403,
            'Surat yang sudah selesai atau dibatalkan tidak dapat menerima lampiran baru.'
        );

        $validated = $request->validate(array_merge(
            $this->uploadRules(),
            [
                'document_type' => [
                    'required',
                    'string',
                    'in:' . implode(',', $this->allowedDocumentTypes()),
                ],
            ]
        ), [
            'files.required' => 'Pilih minimal satu dokumen.',
            'files.max' => 'Maksimal 10 file dalam satu kali unggah.',
            'files.*.max' => 'Ukuran setiap file maksimal 10 MB.',
            'files.*.mimes' => 'Dokumen harus menggunakan format PDF.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
        ]);

        $type = $validated['document_type'];
        $user = Auth::user();
        $followUp = null;

        if ($type === 'surat_asli') {
            abort_unless(
                (int) $user->unit_id === (int) $letter->sender_unit_id,
                403,
                'Hanya unit pengirim yang dapat mengunggah surat asli.'
            );

            abort_unless(
                $letter->status === 'draft',
                403,
                'Surat asli hanya dapat diunggah saat surat masih draft.'
            );
        }

        if ($type === 'bukti_penerimaan') {
            abort_unless(
                (int) $user->unit_id === (int) $letter->receiver_unit_id,
                403,
                'Hanya unit penerima yang dapat mengunggah bukti penerimaan.'
            );
        }

        if ($type === 'bukti_tindak_lanjut') {
            abort_unless(
                (int) $user->unit_id === (int) $letter->receiver_unit_id,
                403,
                'Hanya unit penerima yang dapat mengunggah bukti tindak lanjut.'
            );

            $followUpData = $request->validate([
                'letter_follow_up_id' => [
                    'required',
                    'integer',
                    'exists:letter_follow_ups,id',
                ],
            ]);

            $followUp = LetterFollowUp::where('id', $followUpData['letter_follow_up_id'])
                ->where('letter_id', $letter->id)
                ->firstOrFail();
        }

        $storedPaths = [];
        $files = $validated['files'];

        try {
            DB::transaction(function () use (
                $letter,
                $type,
                $files,
                $followUp,
                &$storedPaths
            ) {
                $lockedLetter = Letter::whereKey($letter->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                abort_if(
                    in_array($lockedLetter->status, ['selesai', 'dibatalkan']),
                    403,
                    'Surat yang sudah selesai atau dibatalkan tidak dapat menerima lampiran baru.'
                );

                if ($type === 'surat_asli') {
                    abort_unless(
                        $lockedLetter->status === 'draft',
                        403,
                        'Surat asli hanya dapat diunggah saat surat masih draft.'
                    );

                    if ($lockedLetter->attachments()
                        ->where('document_type', 'surat_asli')
                        ->exists()
                    ) {
                        throw ValidationException::withMessages([
                            'files' => 'Surat asli sudah diunggah. Hapus lampiran lama terlebih dahulu jika ingin menggantinya.',
                        ]);
                    }
                }

                // Batas maksimal 10 lampiran untuk satu surat.
                $existingCount = $lockedLetter->attachments()->count();
                $newCount = count($files);

                if (($existingCount + $newCount) > 10) {
                    throw ValidationException::withMessages([
                        'files' => 'Maksimal 10 dokumen untuk satu surat. Saat ini sudah ada '
                            . $existingCount . ' dokumen.',
                    ]);
                }

                foreach ($files as $file) {
                    $originalName = $file->getClientOriginalName();

                    $storedName = Str::uuid()->toString()
                        . '.'
                        . $file->getClientOriginalExtension();

                    $directory = $lockedLetter->id . '/' . $type;

                    $path = $file->storeAs(
                        $directory,
                        $storedName,
                        'letter_documents'
                    );

                    if (!$path) {
                        throw new RuntimeException(
                            'Gagal menyimpan file: ' . $originalName
                        );
                    }

                    $storedPaths[] = $path;

                    $attachment = $lockedLetter->attachments()->create([
                        'letter_follow_up_id' => $followUp?->id,
                        'uploaded_by' => Auth::id(),
                        'document_type' => $type,
                        'original_name' => $originalName,
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $lockedLetter->histories()->create([
                        'old_status' => $lockedLetter->status,
                        'new_status' => $lockedLetter->status,
                        'description' => 'Dokumen diunggah: '
                            . $attachment->original_name,
                        'changed_by' => Auth::id(),
                    ]);
                }
            });
        } catch (Throwable $e) {
            // Hapus file fisik jika transaksi database gagal.
            if (!empty($storedPaths)) {
                Storage::disk('letter_documents')->delete($storedPaths);
            }

            throw $e;
        }

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function show($id)
    {
        $attachment = LetterAttachment::with('letter')
            ->findOrFail($id);

        $this->visibleLetter($attachment->letter_id);

        $disk = Storage::disk('letter_documents');

        abort_unless(
            $disk->exists($attachment->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        $file = $disk->get($attachment->file_path);

        $mimeType = $attachment->mime_type;

        // Pastikan MIME type gambar sesuai ekstensi file.
        $extension = strtolower(
            pathinfo($attachment->original_name, PATHINFO_EXTENSION)
        );

        $imageMimeTypes = [
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
        ];

        if (isset($imageMimeTypes[$extension])) {
            $mimeType = $imageMimeTypes[$extension];
        }

        return response($file, 200, [
            'Content-Type' => $mimeType,
            'Content-Disposition' => 'inline; filename="'
                . addslashes($attachment->original_name) . '"',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download($id)
    {
        $attachment = LetterAttachment::findOrFail($id);

        $this->visibleLetter($attachment->letter_id);

        $disk = Storage::disk('letter_documents');

        abort_unless(
            $disk->exists($attachment->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        return response()->download(
            $disk->path($attachment->file_path),
            $attachment->original_name
        );
    }

    public function destroy($id)
    {
        $attachment = LetterAttachment::with('letter')
            ->findOrFail($id);

        $letter = $this->visibleLetter($attachment->letter_id);

        abort_if(
            in_array($letter->status, ['selesai', 'dibatalkan']),
            403,
            'Lampiran surat yang sudah selesai atau dibatalkan tidak dapat dihapus.'
        );

        $user = Auth::user();

        abort_unless(
            $this->isAdmin()
                || (int) $attachment->uploaded_by === (int) $user->id,
            403,
            'Anda hanya dapat menghapus dokumen yang Anda unggah.'
        );

        Storage::disk('letter_documents')->delete($attachment->file_path);

        $letter->histories()->create([
            'old_status' => $letter->status,
            'new_status' => $letter->status,
            'description' => 'Dokumen dihapus: '
                . $attachment->original_name,
            'changed_by' => Auth::id(),
        ]);

        $attachment->delete();

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
