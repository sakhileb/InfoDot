<div class="justify-end mr-5">
    <ul class="flex inline-block">
        <li class="list-items mx-3">
            <button href="#" wire:click="editSolution">
                <i class="fa fa-pen hover:text-blue-500"></i>
            </button>
        </li>
        <li class="list-items mx-3">
            <a href="#" wire:click.prevent="deleteSolution" wire:loading.attr="disabled">
                <i class="fa fa-trash hover:text-red-500"></i>
            </a>
        </li>
    </ul>
    <x-dialog-modal wire:model="editSolution" wire:key="'custom-edit-modal-'.time()">
        <x-slot name="title">
            {{ __('Edit Solution') }}
        </x-slot>
        <form method="POST" wire:submit.prevent="eSolution" onkeydown="return event.key != 'Enter';">
            <x-slot name="content">
                @csrf
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-full px-3 mb-6">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="solution_title">Solution Title:</label>
                        <input class="appearance-none block w-full input input-bordered" name="solution_title" id="solution_title" type="text" placeholder="How to......?" wire:model="solution.solution_title">
                        @error('solution_title')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="w-full px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                            Solution Description:
                        </label>
                        <textarea class="textarea h-24 textarea-bordered w-full" name="solution_description" placeholder="This solution will help you accomplish 1..2..3..." wire:model="solution.solution_description"></textarea>
                        @error('solution_description')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full px-3">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-password">Tags: <span class="text-red-500">(Note: Do not remove the first tag)</span></label>
                        <div class="appearance-none block w-full input input-bordered tags-input" data-name="tags" wire:model="solution.tags"></div>
                        @error('tags')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-city">Duration:</label>
                        <input class="appearance-none block w-full input input-bordered" name="duration" id="grid-city" type="number" placeholder="1" wire:model="solution.duration">
                        @error('duration')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-state">Duration Type:</label>
                        <div class="relative">
                            <select class="appearance-none block w-full input input-bordered" name="duration_type" id="grid-state" wire:model="solution.duration_type">
                                <option value="hours">Hours</option>
                                <option value="days">Days</option>
                                <option value="weeks">Weeks</option>
                                <option value="months">Months</option>
                                <option value="years">Years</option>
                                <option value="infinite">Unknown</option>
                            </select>
                            @error('duration_type')
                                <div class="text-red-500">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="w-full md:w-1/3 px-3 mb-6 md:mb-0">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="grid-zip">Estimated Steps:</label>
                        <input class="appearance-none block w-full input input-bordered" name="steps" id="grid-zip" type="number" placeholder="12" wire:model="solution.steps">
                        @error('steps')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-button class="text-center bg-gray-900 text-white active:bg-gray-700 text-sm font-bold px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1" wire:click="$toggle('editSolution', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-button>

                <x-button class="text-center bg-gray-900 text-white active:bg-gray-700 text-sm font-bold px-6 py-3 rounded shadow hover:shadow-lg outline-none focus:outline-none mr-1 mb-1" wire:click="eSolution({{ $solution->id }})" wire:loading.attr="disabled">
                    {{ __('Save') }}
                </x-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-dialog-modal wire:model="deleteSolution" wire:key="'custom-delete-modal-'.time()">
        <x-slot name="title">
            {{ __('Delete Solution') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete this solution? This action can not be undone.') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('deleteSolution', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="delSolution({{ $solution->id }})" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
