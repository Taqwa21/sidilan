<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use Illuminate\Http\Request;

class SpectatorController extends Controller
{
    public function index()
    {
        return view('spectator.index');
    }

    public function search(Request $request)
    {
        $validated = $request->validate([
            'letter_number' => ['required', 'string', 'max:255'],
        ], [
            'letter_number.required' => 'Nomor surat wajib diisi.',
            'letter_number.max' => 'Nomor surat maksimal 255 karakter.',
        ]);

        $letterNumber = trim($validated['letter_number']);

        $letter = Letter::query()
            ->where('letter_number', $letterNumber)
            ->where('status', '!=', 'draft')
            ->with([
                'senderUnit',
                'receiverUnit',
                'dispositions' => function ($query) {
                    $query->with('assignedUnit')->latest();
                },
                'followUps' => function ($query) {
                    $query->latest();
                },
            ])
            ->first();

        return view('spectator.result', compact('letter'));
    }
}
