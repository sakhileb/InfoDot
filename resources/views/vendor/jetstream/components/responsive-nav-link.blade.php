@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block border-l-4 border-blue-500 bg-slate-800 py-2 pl-3 pr-4 text-base font-medium text-blue-300 transition focus:outline-none focus:text-blue-200 focus:bg-slate-800 focus:border-blue-400'
            : 'block border-l-4 border-transparent py-2 pl-3 pr-4 text-base font-medium text-slate-300 transition hover:border-blue-400/50 hover:bg-slate-800 hover:text-blue-300 focus:outline-none focus:border-blue-400/50 focus:bg-slate-800 focus:text-blue-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
