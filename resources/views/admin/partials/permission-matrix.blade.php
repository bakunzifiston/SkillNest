@php
    $modules = $modules ?? config('admin-modules.modules', []);
    $groups = $groups ?? config('admin-modules.groups', []);
    $actionLabels = config('admin-modules.actions', []);
    $selectedPermissions = $selectedPermissions ?? [];
    $rolePermissionMap = $rolePermissionMap ?? new stdClass;
    $customize = $customize ?? true;
    $showCustomizeToggle = $showCustomizeToggle ?? false;
    $locked = $locked ?? false;
    $selectedRoleId = $selectedRoleId ?? null;
    $inputName = $inputName ?? 'permissions';
@endphp

<div
    class="space-y-4"
    x-data="{
        selected: {{ \Illuminate\Support\Js::from($selectedPermissions) }},
        roleMap: {{ \Illuminate\Support\Js::from($rolePermissionMap) }},
        customize: {{ $customize ? 'true' : 'false' }},
        locked: {{ $locked ? 'true' : 'false' }},
        roleId: {{ \Illuminate\Support\Js::from($selectedRoleId ? (string) $selectedRoleId : null) }},
        effective() {
            if (!this.customize && this.roleId && this.roleMap[this.roleId]) {
                return this.roleMap[this.roleId];
            }
            return this.selected;
        },
        actions(module) {
            return this.effective()[module] || [];
        },
        has(module, action) {
            return this.actions(module).includes(action);
        },
        moduleOn(module) {
            return this.has(module, 'view') || this.actions(module).length > 0;
        },
        canEdit() {
            return this.customize && !this.locked;
        },
        toggleModule(module, on) {
            if (!this.canEdit()) return;
            this.selected = { ...this.selected, [module]: on ? ['view'] : [] };
        },
        toggleAction(module, action, on) {
            if (!this.canEdit()) return;
            let current = [...(this.selected[module] || [])];
            if (on) {
                if (!current.includes('view')) current.push('view');
                if (!current.includes(action)) current.push(action);
            } else if (action === 'view') {
                current = [];
            } else {
                current = current.filter((item) => item !== action);
            }
            this.selected = { ...this.selected, [module]: current };
        },
        applyRole(id) {
            this.roleId = id ? String(id) : null;
            if (!this.customize && this.roleId && this.roleMap[this.roleId]) {
                this.selected = { ...this.roleMap[this.roleId] };
            }
            if (!this.roleId) {
                this.customize = false;
                this.selected = {};
            }
        },
        enableCustomize() {
            if (this.roleId && this.roleMap[this.roleId]) {
                this.selected = { ...this.roleMap[this.roleId] };
            }
        }
    }"
    @role-changed.window="applyRole($event.detail)"
>
    @if($showCustomizeToggle)
        <label class="flex items-start gap-3 rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <input
                type="checkbox"
                name="customize_permissions"
                value="1"
                class="mt-0.5 rounded border-slate-300 text-primary focus:ring-primary"
                x-model="customize"
                @change="if (customize) enableCustomize()"
                :disabled="!roleId || locked"
            >
            <span>
                <span class="block text-sm font-medium text-navy">Customize module access for this user</span>
                <span class="block text-xs text-slate-500 mt-0.5">Override the selected role’s permissions. Leave unchecked to inherit the role exactly.</span>
            </span>
        </label>
    @endif

    @if($locked)
        <p class="text-sm text-slate-500">Super Admin always has full access to every module. These permissions cannot be reduced.</p>
    @endif

    <template x-if="canEdit()">
        <div>
            <template x-for="(actions, module) in selected" :key="module">
                <template x-for="action in actions" :key="module + '-' + action">
                    <input type="hidden" :name="'{{ $inputName }}[' + module + '][]'" :value="action">
                </template>
            </template>
        </div>
    </template>

    <div class="space-y-3">
        @foreach($groups as $groupLabel => $moduleKeys)
            @php
                $groupModules = collect($moduleKeys)->filter(fn ($key) => isset($modules[$key]));
            @endphp
            @continue($groupModules->isEmpty())
            <div class="rounded-xl border border-slate-200 overflow-hidden">
                @if($groupLabel !== '')
                    <div class="px-4 py-2 bg-slate-50 border-b border-slate-200">
                        <p class="text-[11px] font-semibold uppercase tracking-wider text-slate-500">{{ $groupLabel }}</p>
                    </div>
                @endif
                <div class="divide-y divide-slate-100">
                    @foreach($groupModules as $moduleKey)
                        @php $module = $modules[$moduleKey]; @endphp
                        <div class="px-4 py-3 flex flex-col lg:flex-row lg:items-center gap-3">
                            <label class="flex items-center gap-3 min-w-[12rem]">
                                <input
                                    type="checkbox"
                                    class="rounded border-slate-300 text-primary focus:ring-primary"
                                    :checked="moduleOn('{{ $moduleKey }}')"
                                    :disabled="!canEdit()"
                                    @change="toggleModule('{{ $moduleKey }}', $event.target.checked)"
                                >
                                <span class="text-sm font-medium text-navy">{{ $module['label'] }}</span>
                            </label>
                            <div class="flex flex-wrap gap-x-4 gap-y-2 lg:ml-auto">
                                @foreach($module['actions'] as $action)
                                    <label class="inline-flex items-center gap-2 text-sm text-slate-600">
                                        <input
                                            type="checkbox"
                                            class="rounded border-slate-300 text-primary focus:ring-primary"
                                            :checked="has('{{ $moduleKey }}', '{{ $action }}')"
                                            :disabled="!canEdit()"
                                            @change="toggleAction('{{ $moduleKey }}', '{{ $action }}', $event.target.checked)"
                                        >
                                        {{ $actionLabels[$action] ?? ucfirst($action) }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </div>
</div>
