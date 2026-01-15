<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Files') }}
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
                    <h3 class="text-lg font-semibold mb-2">Upload New File</h3>
                    <form action="{{ route('files.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="flex gap-4">
                            <input type="file" name="file" required class="flex-1">
                            <input type="text" name="name" placeholder="File name (optional)" class="flex-1">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded">Upload</button>
                        </div>
                    </form>
                </div>

                <div class="mt-6">
                    <h3 class="text-lg font-semibold mb-2">Your Files</h3>
                    @if($files->count() > 0)
                        <table class="min-w-full">
                            <thead>
                                <tr>
                                    <th class="text-left">Name</th>
                                    <th class="text-left">Size</th>
                                    <th class="text-left">Uploaded</th>
                                    <th class="text-left">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($files as $file)
                                    <tr>
                                        <td>{{ $file->name }}</td>
                                        <td>{{ $file->sizeForHumans() }}</td>
                                        <td>{{ $file->created_at->diffForHumans() }}</td>
                                        <td>
                                            <a href="{{ route('files.download', $file) }}" class="text-blue-500">Download</a>
                                            <form action="{{ route('files.destroy', $file) }}" method="POST" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-500 ml-2">Delete</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                        <div class="mt-4">
                            {{ $files->links() }}
                        </div>
                    @else
                        <p class="text-gray-500">No files uploaded yet.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
