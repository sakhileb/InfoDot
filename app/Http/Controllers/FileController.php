<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use App\Models\Obj;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FileController extends Controller
{
    /**
     * Display a listing of files.
     */
    public function index(Request $request)
    {
        $files = File::where('user_id', $request->user()->id)
            ->with('objectable')
            ->latest()
            ->paginate(20);

        return view('files.index', compact('files'));
    }

    /**
     * Store a newly uploaded file.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240', // 10MB max
            'name' => 'nullable|string|max:255',
        ]);

        try {
            $uploadedFile = $request->file('file');
            
            // Create file record
            $file = File::create([
                'name' => $validated['name'] ?? $uploadedFile->getClientOriginalName(),
                'size' => $uploadedFile->getSize(),
                'user_id' => $request->user()->id,
            ]);

            // Store file using Media Library
            $file->addMedia($uploadedFile)
                ->usingName($file->name)
                ->toMediaCollection('files');

            // Update path in file record
            $media = $file->getFirstMedia('files');
            if ($media) {
                $file->update(['path' => $media->getPath()]);
            }

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'file' => $file->fresh(),
                ], 201);
            }

            return redirect()->back()->with('success', 'File uploaded successfully');
        } catch (\Exception $e) {
            \Log::error('File upload failed: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File upload failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'File upload failed');
        }
    }

    /**
     * Download a file.
     */
    public function download(File $file)
    {
        // Check authorization
        if ($file->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to file');
        }

        $media = $file->getFirstMedia('files');
        
        if (!$media) {
            abort(404, 'File not found');
        }

        return response()->download($media->getPath(), $file->name);
    }

    /**
     * Delete a file.
     */
    public function destroy(File $file): JsonResponse|RedirectResponse
    {
        // Check authorization
        if ($file->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to file');
        }

        try {
            $file->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully',
                ]);
            }

            return redirect()->back()->with('success', 'File deleted successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File deletion failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'File deletion failed');
        }
    }

    /**
     * Validate file upload.
     */
    protected function validateFile(Request $request): array
    {
        return $request->validate([
            'file' => [
                'required',
                'file',
                'max:10240', // 10MB
                'mimes:jpg,jpeg,png,gif,pdf,doc,docx,xls,xlsx,ppt,pptx,txt,zip,rar',
            ],
        ]);
    }
}
