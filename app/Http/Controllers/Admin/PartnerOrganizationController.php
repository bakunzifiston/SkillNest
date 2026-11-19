<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PartnerOrganization;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PartnerOrganizationController extends Controller
{
    public function index(): View
    {
        $organizations = PartnerOrganization::query()
            ->withCount(['users', 'courses'])
            ->orderBy('name')
            ->get();

        return view('admin.partner-organizations.index', compact('organizations'));
    }

    public function create(): View
    {
        return view('admin.partner-organizations.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('partner_organizations', 'name')],
        ]);

        PartnerOrganization::create([
            'name' => trim($validated['name']),
            'slug' => $this->uniqueSlug($validated['name']),
            'registration_code' => PartnerOrganization::generateCode(),
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('admin.partner-organizations.index')->with('success', __('Partner organization added.'));
    }

    public function edit(PartnerOrganization $partner_organization): View
    {
        return view('admin.partner-organizations.edit', ['organization' => $partner_organization]);
    }

    public function update(Request $request, PartnerOrganization $partner_organization): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('partner_organizations', 'name')->ignore($partner_organization->id)],
        ]);

        $partner_organization->update([
            'name' => trim($validated['name']),
            'is_active' => $request->boolean('is_active'),
        ]);

        return redirect()->route('admin.partner-organizations.index')->with('success', __('Partner organization updated.'));
    }

    public function regenerateCode(PartnerOrganization $partner_organization): RedirectResponse
    {
        $partner_organization->update(['registration_code' => PartnerOrganization::generateCode()]);

        return redirect()->route('admin.partner-organizations.index')
            ->with('success', __('New registration code for :name: :code. The old code no longer works.', ['name' => $partner_organization->name, 'code' => $partner_organization->registration_code]));
    }

    public function destroy(PartnerOrganization $partner_organization): RedirectResponse
    {
        if ($partner_organization->users()->exists() || $partner_organization->courses()->exists()) {
            return redirect()->route('admin.partner-organizations.index')
                ->with('error', __('This organization has learners or courses assigned to it and cannot be deleted. Deactivate it instead.'));
        }

        $partner_organization->delete();

        return redirect()->route('admin.partner-organizations.index')->with('success', __('Partner organization deleted.'));
    }

    private function uniqueSlug(string $name): string
    {
        $base = Str::slug($name) ?: 'partner';
        $slug = $base;
        $i = 2;

        while (PartnerOrganization::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$i++;
        }

        return $slug;
    }
}
