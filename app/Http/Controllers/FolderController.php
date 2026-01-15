<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\Obj;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class FolderController extends Controller
{
    /**
     * Display a listing of folders.
     */
    public function index(Request $request)
    {
        $folders = Folder::where('user_id', $request->user()->id)
            ->with('objectable')
            ->latest()
            ->paginate(20);

        return view('folders.index', compact('folders'));
    }

    /**
     * Store a newly created folder.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:objs,id',
        ]);

        try {
            // Create folder record
            $folder = Folder::create([
                'name' => $validated['name'],
                'user_id' => $request->user()->id,
            ]);

            // Create Obj relationship
            Obj::create([
                'parent_id' => $validated['parent_id'] ?? null,
                'objectable_type' => Folder::class,
                'objectable_id' => $folder->id,
                'user_id' => $request->user()->id,
                'team_id' => $request->user()->currentTeam->id ?? null,
            ]);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Folder created successfully',
                    'folder' => $folder->load('objectable'),
                ], 201);
            }

            return redirect()->back()->with('success', 'Folder created successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder creation failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Folder creation failed');
        }
    }

    /**
     * Update the specified folder.
     */
    public function update(Request $request, Folder $folder): JsonResponse|RedirectResponse
    {
        // Check authorization
        if ($folder->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to folder');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        try {
            $folder->update($validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Folder updated successfully',
                    'folder' => $folder,
                ]);
            }

            return redirect()->back()->with('success', 'Folder updated successfully');
        } catch (\Exception $e) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder update failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Folder update failed');
        }
    }

    /**
     * Delete a folder.
     */
    public function destroy(Folder $folder): JsonResponse|RedirectResponse
    {
        // Check authorization
        if ($folder->user_id !== auth()->id()) {
            abort(403, 'Unauthorized access to folder');
        }

        try {
            $folder->delete();

            if (request()->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Folder deleted successfully',
                ]);
            }

            return redirect()->back()->with('success', 'Folder deleted successfully');
        } catch (\Exception $e) {
            if (request()->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Folder deletion failed: ' . $e->getMessage(),
                ], 500);
            }

            return redirect()->back()->with('error', 'Folder deletion failed');
        }
    }
}
