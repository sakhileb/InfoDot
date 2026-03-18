<div class="flex justify-between md:col-span-1">
    <div class="px-4 sm:px-0">
        <h3 class="text-base font-medium leading-7 text-gray-900 sm:text-lg">{{ $title }}</h3>

        <p class="mt-1.5 text-sm leading-6 text-gray-600">
            {{ $description }}
        </p>
    </div>

    <div class="px-4 sm:px-0">
        {{ $aside ?? '' }}
    </div>
</div>
