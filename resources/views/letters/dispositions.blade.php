@extends('layouts.app')

@section('title', 'Disposisi - SIDILAN')

@section('content')
    <div class="container-fluid py-4">

        <div class="mb-4">
            <h3>Disposisi</h3>
            <p class="text-muted mb-0">
                Daftar surat yang telah mendapatkan disposisi.
            </p>
        </div>

        @if (session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-body">

                <form action="{{ route('dispositions.index') }}" method="GET" class="mb-4">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="search" class="form-control"
                                placeholder="Cari nomor surat, perihal, atau instruksi disposisi..."
                                value="{{ $search ?? '' }}">
                        </div>

                        <div class="col-md-3">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-search"></i> Cari
                            </button>

                            <a href="{{ route('dispositions.index') }}" class="btn btn-outline-secondary">
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
                                <th>Pengirim</th>
                                <th>Penerima</th>
                                <th>Instruksi Disposisi</th>
                                <th>Tujuan Disposisi</th>
                                <th>Batas Waktu Penyelesaian</th>
                                <th>Ditugaskan Kepada</th>
                                <th>Tanggal Disposisi</th>
                                <th>Status Surat</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($letters as $index => $letter)
                                @php
                                    $disposition = $letter->dispositions->sortByDesc('created_at')->first();

                                    $targets = $disposition?->targets ?? collect();

                                    $dispositionTargetUnits =
                                        $disposition?->target_units ?: $targets->pluck('code')->implode(', ');

                                    $deadline = $disposition?->deadline_at;

                                    $isTerminal = in_array($letter->status, ['selesai', 'dibatalkan']);

                                    $isOverdue = $deadline && !$isTerminal && $deadline->isPast();

                                    $remainingTime =
                                        $deadline && !$isTerminal && !$isOverdue
                                            ? now()->diffForHumans($deadline, [
                                                'parts' => 2,
                                                'short' => true,
                                                'syntax' => \Carbon\CarbonInterface::DIFF_ABSOLUTE,
                                            ])
                                            : null;
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

                                    <td style="min-width: 220px;">
                                        {{ $disposition->instruction ?? '-' }}
                                    </td>

                                    <td style="min-width: 180px;">
                                        {{ $dispositionTargetUnits ?: '-' }}
                                    </td>

                                    <td style="min-width: 170px;">
                                        @if ($deadline)
                                            <div class="small">
                                                <div>
                                                    <i class="bi bi-calendar-event me-1"></i>
                                                    {{ $deadline->format('d/m/Y H:i') }}
                                                </div>

                                                @if ($isTerminal)
                                                    <span class="badge bg-success mt-1">
                                                        {{ $letter->status === 'selesai' ? 'Selesai' : 'Dibatalkan' }}
                                                    </span>
                                                @elseif($isOverdue)
                                                    <span class="badge bg-danger mt-1">
                                                        Melewati Tenggat
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning text-dark mt-1">
                                                        Sisa {{ $remainingTime }}
                                                    </span>
                                                @endif
                                            </div>
                                        @else
                                            <span class="text-muted">
                                                Tidak ditentukan
                                            </span>
                                        @endif
                                    </td>

                                    <td>
                                        @if ($disposition?->assignedUser)
                                            {{ $disposition->assignedUser->name }}
                                        @elseif($disposition?->assignedUnit)
                                            {{ $disposition->assignedUnit->name }}
                                        @else
                                            -
                                        @endif
                                    </td>

                                    <td>
                                        {{ $disposition?->created_at?->format('d/m/Y H:i') ?? '-' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-secondary">
                                            {{ ucfirst(str_replace('_', ' ', $letter->status)) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4 text-muted">
                                        Belum ada surat yang memiliki disposisi.
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
