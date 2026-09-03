<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white border border-slate-300 rounded-xl font-semibold text-xs text-slate-700 uppercase tracking-widest shadow-sm hover:bg-primary-light hover:border-primary-muted focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
