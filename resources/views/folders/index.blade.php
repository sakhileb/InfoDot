<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Folders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                @if(session('success'))
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                        {{ session('success') }}
                    </div>
                @endif

                @if(session('error'))
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                        {{ session('error') }}
                    </div>
                @endif

                <div class="mb-4">
                    <h3 class="text-lg font-semibold mb-2">Create New Folder</h3>
                    <form action="{{ route('folders.store') }}" method="POST">
                        @csrf
                        <div class="flex gap-4">
                            <input type="text" name="name" placeholder="Folder name" required class="flex-1">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Create</button>
                        </div>
                    </form>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Your Folders</h3>
                    @if($folders->count() > 0)
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th class="text-left">Created</th>
                                    <th class="text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($folders as $folder)
                                    <tr>
                                        <td>{{ $folder->name }}</td>
                                        <td>{{ $folder->created_at->diffForHumans() }}</td>
                                        <td>
                                            <form action="{{ route('folders.destroy', $folder) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $folders->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No folders created yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
