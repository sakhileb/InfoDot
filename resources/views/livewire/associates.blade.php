<div>
    <a href="#" wire:click.prevent="connect" class="justify-items-end btn rounded-full uppercase text-white font-bold hover:shadow-md shadow text-xs px-4 py-2 rounded outline-none focus:outline-none sm:mr-2 mb-1" style="transition: all 0.15s ease 0s;">
        {{ Auth::user()->associates()->where('associate_id', $user->id)->exists() ? 'Unfollow' : 'Follow' }}
    </a>
</div>
