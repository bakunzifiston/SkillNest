<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Course;
use App\Models\PartnerOrganization;
use App\Models\User;
use App\Support\AgentKyc;
use App\Support\RwandaLocations;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(Request $request): View
    {
        $organizations = PartnerOrganization::active()->orderBy('name')->get();

        // Deep link from a partner-only course: /register?type=partner&organization=1&course=slug
        $defaultType = $request->query('type') === User::TYPE_PARTNER && $organizations->isNotEmpty()
            ? User::TYPE_PARTNER
            : User::TYPE_AGENT;
        $defaultOrganizationId = $organizations->firstWhere('id', (int) $request->query('organization'))?->id;

        // After signing up, bring the learner back to the course they came from.
        if ($request->filled('course')) {
            $course = Course::published()->where('slug', (string) $request->query('course'))->first();
            if ($course) {
                $request->session()->put('url.intended', route('courses.show', $course, false));
            }
        }

        return view('auth.register', [
            'partnerOrganizations' => $organizations,
            'defaultType' => $defaultType,
            'defaultOrganizationId' => $defaultOrganizationId,
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->merge(AgentKyc::normalize($request->all()));

        $validated = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            // Omitted/blank means a regular Agent, exactly as before.
            'learner_type' => ['nullable', Rule::in(array_keys(User::learnerTypes()))],
            'partner_organization_id' => [
                'nullable',
                'required_if:learner_type,'.User::TYPE_PARTNER,
                Rule::exists('partner_organizations', 'id')->where('is_active', true),
            ],
            ...AgentKyc::rules(),
            ...RwandaLocations::validationRules(),
        ], AgentKyc::messages());

        // Partners must prove they belong to the organization with its access code.
        if (($validated['learner_type'] ?? null) === User::TYPE_PARTNER) {
            $organization = PartnerOrganization::active()->find($validated['partner_organization_id']);

            if (! $organization || ! $organization->acceptsRegistrationCode($request->input('partner_code'))) {
                throw ValidationException::withMessages([
                    'partner_code' => 'The access code is missing or incorrect for the selected organization.',
                ]);
            }
        }

        $learnerType = ($validated['learner_type'] ?? null) === User::TYPE_PARTNER
            ? User::TYPE_PARTNER
            : User::TYPE_AGENT;
        $partnerOrganizationId = $learnerType === User::TYPE_PARTNER
            ? (int) $validated['partner_organization_id']
            : null;

        $hierarchyErrors = RwandaLocations::validateHierarchy($validated);
        if ($hierarchyErrors !== []) {
            throw ValidationException::withMessages($hierarchyErrors);
        }

        if (($validated['country'] ?? null) !== RwandaLocations::DEFAULT_COUNTRY) {
            $validated['province'] = null;
            $validated['district'] = null;
            $validated['sector'] = null;
        }

        // Optional KYC details are an Agent feature; ignore them for Partners.
        $kyc = $learnerType === User::TYPE_AGENT ? AgentKyc::only($validated) : [];

        $user = User::create([
            ...$kyc,
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'country' => $validated['country'],
            'province' => $validated['province'] ?? null,
            'district' => $validated['district'] ?? null,
            'sector' => $validated['sector'] ?? null,
            'learner_type' => $learnerType,
            'partner_organization_id' => $partnerOrganizationId,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->intended(route('dashboard', absolute: false));
    }
}
