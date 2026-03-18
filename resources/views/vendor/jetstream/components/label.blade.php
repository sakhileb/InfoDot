@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium leading-6 text-gray-700']) }}>
    {{ $value ?? $slot }}
</label>
