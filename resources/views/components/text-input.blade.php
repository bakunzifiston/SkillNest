@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-slate-300 focus:border-primary focus:ring-primary rounded-xl shadow-sm']) }}>
