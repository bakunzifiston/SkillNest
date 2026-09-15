<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function edit(): View
    {
        $logoPath = Setting::get(Setting::KEY_SITE_LOGO);
        $logoUrl = $logoPath ? url('course-image/' . ltrim($logoPath, '/')) : null;
        $partnersCount = Partner::count();

        $kpis = [
            [
                'label' => 'Platform logo',
                'value' => $logoUrl ? 1 : 0,
                'display' => $logoUrl ? 'Set' : 'Missing',
                'icon' => $logoUrl ? 'check' : 'pulse',
                'tone' => $logoUrl ? 'success' : 'accent',
            ],
            [
                'label' => 'Partner logos',
                'value' => $partnersCount,
                'icon' => 'users',
                'tone' => 'primary',
            ],
        ];

        return view('admin.settings.edit', compact('logoUrl', 'kpis'));
    }

    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $oldPath = Setting::get(Setting::KEY_SITE_LOGO);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('logo')->store('site', 'public');
            Setting::set(Setting::KEY_SITE_LOGO, $path);
        }

        if ($request->boolean('remove_logo')) {
            $oldPath = Setting::get(Setting::KEY_SITE_LOGO);
            if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                Storage::disk('public')->delete($oldPath);
            }
            Setting::set(Setting::KEY_SITE_LOGO, null);
        }

        return redirect()->route('admin.settings.edit')->with('success', 'Settings saved.');
    }
}
