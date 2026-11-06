@extends('layouts.app')

@section('title', 'Tindak Lanjut - SIDILAN')

@section('content')
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h3>Tindak Lanjut</h3>
            <p class="text-muted mb-0">
                Daftar aktivitas dan perkembangan tindak lanjut surat.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <form action="{{ route('followups.index') }}" method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor surat, perihal, atau keterangan..." value="{{ $search ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>

                            <a href="{{ route('followups.index') }}" class="btn btn-outline-secondary">
                                Reset
                            </a>
                        </div>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Nomor Surat</th>
                                <th>Perihal</th>
                                <th>Unit Pengirim</th>
                                <th>Unit Penerima</th>
                                <th>Aktivitas Tindak Lanjut</th>
                                <th>Bukti Tindak Lanjut</th>
                                <th>Petugas</th>
                                <th>Tanggal</th>
                                <th>Status Surat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($letters as $index => $letter)
                                @php
                                    $followUp = $letter->followUps->sortByDesc('created_at')->first();

                                    $evidenceFiles = $followUp
                                        ? $followUp->attachments->where('document_type', 'bukti_tindak_lanjut')
                                        : collect();
                                @endphp

                                <tr>
                                    <td>{{ $letters->firstItem() + $index }}</td>

                                    <td>
                                        <a href="{{ route('letters.show', $letter->id) }}"
                                            class="text-decoration-none fw-semibold">
                                            {{ $letter->letter_number }}
                                        </a>
                                    </td>

                                    <td>{{ $letter->subject }}</td>

                                    <td>{{ $letter->senderUnit->name ?? '-' }}</td>

                                    <td>{{ $letter->receiverUnit->name ?? '-' }}</td>

                                    <td style="min-width: 250px;">
                                        {{ $followUp->description ?? '-' }}
                                    </td>

                                    <td style="min-width: 180px;">
                                        @forelse($evidenceFiles as $file)
                                            <div class="d-flex flex-column gap-1 mb-2">
                                                <span class="small text-muted">
                                                    <i class="bi bi-file-earmark-pdf text-danger"></i>
                                                    {{ $file->original_name ?? 'Dokumen PDF' }}
                                                </span>

                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('letters.attachments.show', $file->id) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary">
                                                        <i class="bi bi-eye"></i> Lihat
                                                    </a>

                                                    <a href="{{ route('letters.attachments.download', $file->id) }}"
                                                        class="btn btn-sm btn-outline-secondary">
                                                        <i class="bi bi-download"></i> Unduh
                                                    </a>
                                                </div>
                                            </div>
                                        @empty
                                            <span class="text-muted">Belum ada bukti</span>
                                        @endforelse
                                    </td>

                                    <td>{{ $followUp->user->name ?? '-' }}</td>

                                    <td>
                                        {{ $followUp?->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted">
                                        Belum ada aktivitas tindak lanjut.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $letters->appends(request()->query())->links('pagination::bootstrap-5') }}
                </div>

            </div>
        </div>

    </div>
@endsection
