@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center border-b-2 border-blue-500 px-1 pt-1 text-sm font-medium leading-5 text-blue-700 transition focus:outline-none focus:border-blue-700'
            : 'inline-flex items-center border-b-2 border-transparent px-1 pt-1 text-sm font-medium leading-5 text-slate-300 transition hover:border-blue-400/50 hover:text-blue-300 focus:outline-none focus:border-blue-400/50 focus:text-blue-300';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
