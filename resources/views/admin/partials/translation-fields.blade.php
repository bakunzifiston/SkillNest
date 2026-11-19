{{--
    Optional translations of a record's public text (English stays the original).
    Expects:
      $fields = ['title' => ['label' => __('Title'), 'type' => 'input'],
                 'description' => ['label' => __('Description'), 'type' => 'textarea', 'rows' => 4]]
      $model  = the existing record, or null on create forms
    Saved by the controller with $model->saveTranslations($request->input('tr', [])).
--}}
@php
    $model = $model ?? null;
    $others = collect(config('localization.locales'))->except(config('localization.default'));
@endphp

@foreach($others as $code => $meta)
    @php
        $hasAny = ($model && $model->isTranslatedTo($code))
            || collect($fields)->keys()->contains(fn ($f) => filled(old("tr.{$code}.{$f}")))
            || $errors->has("tr.{$code}.*");
    @endphp
    <details class="rounded-xl border border-slate-200 bg-slate-50/70 p-4" @if($hasAny) open @endif>
        <summary class="cursor-pointer select-none text-sm font-semibold text-navy">
            {{ __(':language translation (optional)', ['language' => $meta['name']]) }}
        </summary>
        <p class="mt-2 text-xs text-slate-500">
            {{ __('Leave a field empty to show the English text to learners who use :language.', ['language' => $meta['name']]) }}
        </p>
        <div class="mt-3 space-y-4">
            @foreach($fields as $field => $cfg)
                @php
                    $inputId = "tr_{$code}_{$field}";
                    $value = old("tr.{$code}.{$field}", $model?->translationFor($field, $code));
                    $english = $model ? (string) $model->getAttribute($field) : '';
                @endphp
                <div>
                    <label for="{{ $inputId }}" class="block text-sm font-medium text-gray-700">{{ $cfg['label'] }}</label>
                    @if(($cfg['type'] ?? 'input') === 'textarea')
                        <textarea id="{{ $inputId }}" name="tr[{{ $code }}][{{ $field }}]" rows="{{ $cfg['rows'] ?? 4 }}" lang="{{ $code }}" placeholder="{{ \Illuminate\Support\Str::limit(strip_tags($english), 120) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">{{ $value }}</textarea>
                    @else
                        <input type="text" id="{{ $inputId }}" name="tr[{{ $code }}][{{ $field }}]" value="{{ $value }}" lang="{{ $code }}" placeholder="{{ \Illuminate\Support\Str::limit($english, 120) }}" class="mt-1 block w-full rounded-lg border-gray-300 shadow-sm focus:border-accent focus:ring-accent">
                    @endif
                    @error("tr.{$code}.{$field}")<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            @endforeach
        </div>
    </details>
@endforeach
