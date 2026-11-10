@php
    $fileSize = $attachment->size ?? ($attachment->file_size ?? 0);
    $isPdf = $attachment->mime_type === 'application/pdf';

    $canDelete = !$isFinished && ($isAdmin || (int) $attachment->uploaded_by === (int) auth()->id());
@endphp

<div class="document-item">
    <div class="d-flex align-items-center gap-3 flex-grow-1">
        <i class="bi bi-file-earmark-pdf document-icon"></i>

        <div class="min-w-0">
            <div class="document-name">
                {{ $attachment->original_name }}
            </div>

            <div class="small text-muted">
                {{ $attachment->uploader->name ?? 'Pengguna tidak tersedia' }}
                ·
                {{ $attachment->created_at?->format('d M Y, H:i') ?? '-' }}
            </div>

            <div class="small text-muted">
                {{ number_format($fileSize / 1024, 2) }} KB
            </div>
        </div>
    </div>

    <div class="d-flex align-items-center gap-2 flex-wrap">
        @if ($isPdf)
            <a href="{{ route('letters.attachments.show', $attachment->id) }}" target="_blank" rel="noopener"
                class="btn btn-sm btn-outline-primary">
                <i class="bi bi-eye me-1"></i>
                Lihat
            </a>
        @endif

        <a href="{{ route('letters.attachments.download', $attachment->id) }}" class="btn btn-sm btn-outline-secondary">
            <i class="bi bi-download me-1"></i>
            Download
        </a>

        @if ($canDelete)
            <form action="{{ route('letters.attachments.destroy', $attachment->id) }}" method="POST" data-confirm
                data-confirm-title="{{ $label }}" data-confirm-message="{{ $message }}"
                data-confirm-button="Ya, Hapus" data-confirm-type="danger">

                @csrf
                @method('DELETE')

                <button type="submit" class="btn btn-sm btn-outline-danger">
                    <i class="bi bi-trash"></i>
                </button>
            </form>
        @endif
    </div>
</div>
