<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function index()
    {
        $settings = [
            'app_name' => Setting::where('key', 'app_name')->value('value') ?? 'SIDILAN',
            'agency_name' => Setting::where('key', 'agency_name')->value('value') ?? 'BNPB',
            'app_description' => Setting::where('key', 'app_description')->value('value') ?? 'Sistem Informasi Monitoring Surat',
        ];

        return view('admin.settings', compact('settings'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:100',
            'agency_name' => 'required|string|max:150',
            'app_description' => 'nullable|string|max:500',
        ]);

        foreach ($validated as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => $value]
            );
        }

        return redirect()->route('admin.settings.index')
            ->with('success', 'Pengaturan berhasil disimpan.');
    }
}
