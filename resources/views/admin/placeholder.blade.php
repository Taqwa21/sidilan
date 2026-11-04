@extends('layouts.app')

@section('content')
<div class="card border-0 shadow-sm">
    <div class="card-body p-4">

        <h5 class="fw-bold">{{ $judul }}</h5>

        <p class="text-secondary mb-0">
            {{ $deskripsi }}
        </p>

        <div class="mt-4 text-muted">
            <i class="bi bi-tools me-2"></i>
            Fitur ini sedang dalam tahap pengembangan.
        </div>

    </div>
</div>
@endsection
