<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Partner;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class PartnerController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->get('search', ''));

        $partners = Partner::query()
            ->when($search !== '', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        $total = Partner::count();
        $named = Partner::query()->whereNotNull('name')->where('name', '!=', '')->count();

        $kpis = [
            [
                'label' => 'Partners',
                'value' => $total,
                'icon' => 'users',
                'tone' => 'primary',
            ],
            [
                'label' => 'Named',
                'value' => $named,
                'icon' => 'check',
                'tone' => 'success',
            ],
            [
                'label' => 'Unnamed',
                'value' => max(0, $total - $named),
                'icon' => 'folder',
                'tone' => 'accent',
            ],
            [
                'label' => 'On homepage',
                'value' => $total,
                'icon' => 'live',
                'tone' => 'slate',
            ],
        ];

        return view('admin.partners.index', compact('partners', 'kpis', 'search'));
    }

    public function create(): View
    {
        return view('admin.partners.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'required|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);
        $path = $request->file('logo')->store('partners', 'public');
        $sortOrder = (int) Partner::max('sort_order') + 1;
        Partner::create([
            'name' => $request->input('name'),
            'logo' => $path,
            'sort_order' => $sortOrder,
        ]);
        return redirect()->route('admin.partners.index')->with('success', 'Partner added.');
    }

    public function edit(Partner $partner): View
    {
        return view('admin.partners.edit', compact('partner'));
    }

    public function update(Request $request, Partner $partner): RedirectResponse
    {
        $request->validate([
            'name' => 'nullable|string|max:255',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:2048',
        ]);
        $data = ['name' => $request->input('name')];
        if ($request->hasFile('logo')) {
            if ($partner->logo && Storage::disk('public')->exists($partner->logo)) {
                Storage::disk('public')->delete($partner->logo);
            }
            $data['logo'] = $request->file('logo')->store('partners', 'public');
        }
        $partner->update($data);
        return redirect()->route('admin.partners.index')->with('success', 'Partner updated.');
    }

    public function destroy(Partner $partner): RedirectResponse
    {
        if ($partner->logo && Storage::disk('public')->exists($partner->logo)) {
            Storage::disk('public')->delete($partner->logo);
        }
        $partner->delete();
        return redirect()->route('admin.partners.index')->with('success', 'Partner removed.');
    }
}
