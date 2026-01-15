<div class="justify-end mr-5">
    <ul class="flex inline-block">
        <li class="list-items mx-3">
            <button href="#" wire:click="editQuestion">
                <i class="fa fa-pen hover:text-blue-500"></i>
            </button>
        </li>
        <li class="list-items mx-3">
            <a href="#" wire:click.prevent="deleteQuestion" wire:loading.attr="disabled">
                <i class="fa fa-trash hover:text-red-500"></i>
            </a>
        </li>
    </ul>
    <x-dialog-modal wire:model="editQuestion" :wire:key="'custom-edit-modal-'.time()">
        <x-slot name="title">
            {{ __('Edit Question') }}
        </x-slot>
        <form method="POST" wire:submit.prevent="eQuestion" onkeydown="return event.key != 'Enter';">
            <x-slot name="content">

                @csrf
                <div class="flex flex-wrap -mx-3 mb-6">
                    <div class="w-full md:w-full px-3 mb-6">
                        <label class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2" for="question">Question:</label>
                        <input class="appearance-none block w-full input input-bordered" name="question" id="question" type="text" placeholder="How to......?" wire:model="question.question">
                        @error('question')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="w-full px-3">
                        <label for="description" class="block uppercase tracking-wide text-gray-700 text-xs font-bold mb-2">
                            Description:
                        </label>
                        <textarea class="textarea h-24 textarea-bordered w-full" name="description" placeholder="Describe the problem you are facing....." wire:model="question.description"></textarea>
                        @error('description')
                            <div class="text-red-500">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </x-slot>

            <x-slot name="footer">
                <x-secondary-button wire:click="$toggle('editQuestion', false)" wire:loading.attr="disabled">
                    {{ __('Cancel') }}
                </x-secondary-button>

                <x-danger-button class="ml-2" wire:click="eQuestion({{ $question->id }})" wire:loading.attr="disabled">
                    {{ __('Save') }}
                </x-danger-button>
            </x-slot>
        </form>
    </x-dialog-modal>

    <x-dialog-modal wire:model="deleteQuestion" :wire:key="'custom-delete-modal-'.time()">
        <x-slot name="title">
            {{ __('Delete Question') }}
        </x-slot>

        <x-slot name="content">
            {{ __('Are you sure you want to delete this question? This action can not be undone.') }}
        </x-slot>

        <x-slot name="footer">
            <x-secondary-button wire:click="$toggle('deleteQuestion', false)" wire:loading.attr="disabled">
                {{ __('Cancel') }}
            </x-secondary-button>

            <x-danger-button class="ml-2" wire:click="delQuestion({{ $question->id }})" wire:loading.attr="disabled">
                {{ __('Delete') }}
            </x-danger-button>
        </x-slot>
    </x-dialog-modal>
</div>
