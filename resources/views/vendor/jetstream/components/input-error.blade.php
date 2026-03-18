@props(['for'])

@error($for)
    <p {{ $attributes->merge(['class' => 'text-sm leading-5 text-red-600']) }}>{{ $message }}</p>
@enderror
