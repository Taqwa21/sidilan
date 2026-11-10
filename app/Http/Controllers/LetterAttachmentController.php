<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\LetterAttachment;
use App\Models\LetterFollowUp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class LetterAttachmentController extends Controller
{
    private function isAdmin(): bool
    {
        return Auth::user()?->role?->slug === 'admin';
    }

    private function visibleLetter($id): Letter
    {
        $letter = Letter::findOrFail($id);

        if ($this->isAdmin()) {
            return $letter;
        }

        $user = Auth::user();

        abort_unless(
            $user && $user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        $isSender = (int) $letter->sender_unit_id === (int) $user->unit_id;

        $isReceiver = (int) $letter->receiver_unit_id === (int) $user->unit_id
            && $letter->status !== 'draft';

        abort_unless(
            $isSender || $isReceiver,
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
        $user = Auth::user();

        abort_if(
            $this->isAdmin(),
            403,
            'Administrator hanya memiliki akses pemantauan.'
        );

        abort_unless(
            $user && $user->unit_id,
            403,
            'Akun Anda belum memiliki unit kerja.'
        );

        abort_if(
            in_array($letter->status, ['selesai', 'dibatalkan'], true),
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
            'files.array' => 'Format unggahan dokumen tidak valid.',
            'files.min' => 'Pilih minimal satu dokumen.',
            'files.max' => 'Maksimal 10 file dalam satu kali unggah.',
            'files.*.required' => 'Setiap dokumen wajib dipilih.',
            'files.*.file' => 'File yang diunggah tidak valid.',
            'files.*.max' => 'Ukuran setiap file maksimal 10 MB.',
            'files.*.mimes' => 'Dokumen harus menggunakan format PDF.',
            'document_type.required' => 'Jenis dokumen wajib dipilih.',
            'document_type.in' => 'Jenis dokumen tidak valid.',
        ]);

        $type = $validated['document_type'];
        $userId = $user->id;
        $unitId = (int) $user->unit_id;
        $files = $validated['files'];

        if ($type === 'surat_asli') {
            abort_unless(
                $unitId === (int) $letter->sender_unit_id,
                403,
                'Hanya unit pengirim yang dapat mengunggah surat asli.'
            );

            abort_unless(
                $letter->status === 'draft',
                403,
                'Surat asli hanya dapat diunggah saat surat masih draft.'
            );
        }

        if (in_array($type, [
            'bukti_penerimaan',
            'bukti_tindak_lanjut',
        ], true)) {
            abort_unless(
                $unitId === (int) $letter->receiver_unit_id,
                403,
                'Hanya unit penerima yang dapat mengunggah dokumen ini.'
            );
        }

        $followUpId = null;

        if ($type === 'bukti_tindak_lanjut') {
            $followUpData = $request->validate([
                'letter_follow_up_id' => [
                    'required',
                    'integer',
                    'exists:letter_follow_ups,id',
                ],
            ]);

            $followUpId = (int) $followUpData['letter_follow_up_id'];

            LetterFollowUp::query()
                ->whereKey($followUpId)
                ->where('letter_id', $letter->id)
                ->firstOrFail();
        }

        $storedPaths = [];

        try {
            DB::transaction(function () use (
                $letter,
                $type,
                $files,
                $followUpId,
                $userId,
                $unitId,
                &$storedPaths
            ) {
                $lockedLetter = Letter::query()
                    ->whereKey($letter->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                // Periksa ulang hak akses dan status setelah surat dikunci.
                abort_unless(
                    (int) $lockedLetter->sender_unit_id === $unitId
                        || (
                            (int) $lockedLetter->receiver_unit_id === $unitId
                            && $lockedLetter->status !== 'draft'
                        ),
                    403,
                    'Anda tidak memiliki akses ke surat ini.'
                );

                abort_if(
                    in_array($lockedLetter->status, ['selesai', 'dibatalkan'], true),
                    403,
                    'Surat yang sudah selesai atau dibatalkan tidak dapat menerima lampiran baru.'
                );

                if ($type === 'surat_asli') {
                    abort_unless(
                        (int) $lockedLetter->sender_unit_id === $unitId
                            && $lockedLetter->status === 'draft',
                        403,
                        'Surat asli hanya dapat diunggah unit pengirim saat masih draft.'
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

                if (in_array($type, [
                    'bukti_penerimaan',
                    'bukti_tindak_lanjut',
                ], true)) {
                    abort_unless(
                        (int) $lockedLetter->receiver_unit_id === $unitId,
                        403,
                        'Hanya unit penerima yang dapat mengunggah dokumen ini.'
                    );
                }

                $followUp = null;

                if ($type === 'bukti_tindak_lanjut') {
                    $followUp = LetterFollowUp::query()
                        ->whereKey($followUpId)
                        ->where('letter_id', $lockedLetter->id)
                        ->lockForUpdate()
                        ->firstOrFail();
                }

                // Maksimal 10 lampiran untuk satu surat.
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
                    $storedName = Str::uuid()->toString() . '.pdf';
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
                        'uploaded_by' => $userId,
                        'document_type' => $type,
                        'original_name' => $originalName,
                        'file_path' => $path,
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $lockedLetter->histories()->create([
                        'old_status' => $lockedLetter->status,
                        'new_status' => $lockedLetter->status,
                        'description' => 'Dokumen diunggah: ' . $attachment->original_name,
                        'changed_by' => $userId,
                    ]);
                }
            });
        } catch (Throwable $e) {
            // Bersihkan file fisik jika transaksi database gagal.
            if ($storedPaths !== []) {
                Storage::disk('letter_documents')->delete($storedPaths);
            }

            throw $e;
        }

        return back()->with('success', 'Dokumen berhasil diunggah.');
    }

    public function show($id)
    {
        $attachment = LetterAttachment::query()
            ->with('letter')
            ->findOrFail($id);

        $this->visibleLetter($attachment->letter_id);

        $disk = Storage::disk('letter_documents');

        abort_unless(
            $disk->exists($attachment->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        abort_unless(
            $attachment->mime_type === 'application/pdf',
            415,
            'Pratinjau hanya tersedia untuk dokumen PDF.'
        );

        $originalName = str_replace(
            ["\r", "\n", '"'],
            '',
            $attachment->original_name
        );

        return response($disk->get($attachment->file_path), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="document.pdf"; filename*=UTF-8\'\''
                . rawurlencode($originalName),
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function download($id)
    {
        $attachment = LetterAttachment::query()
            ->findOrFail($id);

        $this->visibleLetter($attachment->letter_id);

        $disk = Storage::disk('letter_documents');

        abort_unless(
            $disk->exists($attachment->file_path),
            404,
            'File dokumen tidak ditemukan.'
        );

        return $disk->download(
            $attachment->file_path,
            $attachment->original_name
        );
    }

    public function destroy($id)
    {
        $attachment = LetterAttachment::query()
            ->findOrFail($id);

        $letter = $this->visibleLetter($attachment->letter_id);
        $user = Auth::user();

        abort_if(
            in_array($letter->status, ['selesai', 'dibatalkan'], true),
            403,
            'Lampiran surat yang sudah selesai atau dibatalkan tidak dapat dihapus.'
        );

        abort_unless(
            $this->isAdmin()
                || (int) $attachment->uploaded_by === (int) $user->id,
            403,
            'Anda hanya dapat menghapus dokumen yang Anda unggah.'
        );

        $filePath = null;

        DB::transaction(function () use (
            $attachment,
            $letter,
            $user,
            &$filePath
        ) {
            $lockedLetter = Letter::query()
                ->whereKey($letter->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Periksa ulang akses surat.
            if (!$this->isAdmin()) {
                abort_unless(
                    (int) $lockedLetter->sender_unit_id === (int) $user->unit_id
                        || (
                            (int) $lockedLetter->receiver_unit_id === (int) $user->unit_id
                            && $lockedLetter->status !== 'draft'
                        ),
                    403,
                    'Anda tidak memiliki akses ke surat ini.'
                );
            }

            abort_if(
                in_array($lockedLetter->status, ['selesai', 'dibatalkan'], true),
                403,
                'Lampiran surat yang sudah selesai atau dibatalkan tidak dapat dihapus.'
            );

            $lockedAttachment = LetterAttachment::query()
                ->whereKey($attachment->id)
                ->where('letter_id', $lockedLetter->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless(
                $this->isAdmin()
                    || (int) $lockedAttachment->uploaded_by === (int) $user->id,
                403,
                'Anda hanya dapat menghapus dokumen yang Anda unggah.'
            );

            $filePath = $lockedAttachment->file_path;

            $lockedLetter->histories()->create([
                'old_status' => $lockedLetter->status,
                'new_status' => $lockedLetter->status,
                'description' => 'Dokumen dihapus: ' . $lockedAttachment->original_name,
                'changed_by' => $user->id,
            ]);

            $lockedAttachment->delete();
        });

        // File fisik dihapus setelah transaksi database berhasil.
        if ($filePath) {
            Storage::disk('letter_documents')->delete($filePath);
        }

        return back()->with('success', 'Dokumen berhasil dihapus.');
    }
}
