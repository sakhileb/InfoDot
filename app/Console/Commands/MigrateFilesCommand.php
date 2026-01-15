<?php

namespace App\Console\Commands;

use App\Models\File;
use App\Models\Folder;
use App\Models\Obj;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MigrateFilesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'files:migrate 
                            {--source-path= : Path to Laravel 8 storage directory}
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate files from Laravel 8 storage to Laravel 11 with Media Library';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $sourcePath = $this->option('source-path') ?? storage_path('app/public');
        $dryRun = $this->option('dry-run');

        if (!is_dir($sourcePath)) {
            $this->error("Source path does not exist: {$sourcePath}");
            return Command::FAILURE;
        }

        $this->info('Starting file migration...');
        $this->info('Source path: ' . $sourcePath);
        $this->info('Dry run: ' . ($dryRun ? 'Yes' : 'No'));
        $this->newLine();

        // Get files from old database if available
        $oldFiles = $this->getOldFiles();
        
        if ($oldFiles->isEmpty()) {
            $this->warn('No files found in database to migrate.');
            $this->info('You can manually copy files to the new storage location.');
            return Command::SUCCESS;
        }

        $this->info("Found {$oldFiles->count()} files to migrate.");
        $this->newLine();

        $migrated = 0;
        $failed = 0;

        foreach ($oldFiles as $oldFile) {
            try {
                if ($dryRun) {
                    $this->line("Would migrate: {$oldFile->name} ({$oldFile->path})");
                    $migrated++;
                } else {
                    $this->migrateFile($oldFile, $sourcePath);
                    $this->info("✓ Migrated: {$oldFile->name}");
                    $migrated++;
                }
            } catch (\Exception $e) {
                $this->error("✗ Failed to migrate {$oldFile->name}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Migration complete!");
        $this->info("Migrated: {$migrated}");
        if ($failed > 0) {
            $this->warn("Failed: {$failed}");
        }

        return Command::SUCCESS;
    }

    /**
     * Get files from old database.
     */
    protected function getOldFiles()
    {
        // Try to get files from the current database
        // This assumes the old files table has been migrated
        try {
            return DB::table('files')->get();
        } catch (\Exception $e) {
            $this->warn('Could not query files table: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Migrate a single file.
     */
    protected function migrateFile($oldFile, string $sourcePath): void
    {
        // Check if file already exists in new system
        $existingFile = File::where('uuid', $oldFile->uuid)->first();
        if ($existingFile) {
            $this->line("  File already migrated: {$oldFile->name}");
            return;
        }

        // Build full source path
        $fullSourcePath = $sourcePath . '/' . ltrim($oldFile->path, '/');
        
        if (!file_exists($fullSourcePath)) {
            throw new \Exception("Source file not found: {$fullSourcePath}");
        }

        // Create new file record
        $newFile = File::create([
            'name' => $oldFile->name,
            'size' => $oldFile->size ?? filesize($fullSourcePath),
            'uuid' => $oldFile->uuid,
            'user_id' => $oldFile->user_id ?? 1, // Default to first user if not set
        ]);

        // Add file to media library
        $newFile->addMedia($fullSourcePath)
            ->preservingOriginal()
            ->usingName($oldFile->name)
            ->toMediaCollection('files');

        // Update path in file record
        $media = $newFile->getFirstMedia('files');
        $newFile->update(['path' => $media->getPath()]);

        // Migrate Obj relationship if it exists
        $this->migrateObjRelationship($oldFile, $newFile);
    }

    /**
     * Migrate Obj relationship for a file.
     */
    protected function migrateObjRelationship($oldFile, File $newFile): void
    {
        try {
            $oldObj = DB::table('objs')
                ->where('objectable_type', 'App\\Models\\File')
                ->where('objectable_id', $oldFile->id)
                ->first();

            if ($oldObj) {
                Obj::create([
                    'parent_id' => $oldObj->parent_id,
                    'objectable_type' => File::class,
                    'objectable_id' => $newFile->id,
                    'user_id' => $oldObj->user_id ?? $newFile->user_id,
                    'team_id' => $oldObj->team_id ?? null,
                    'uuid' => $oldObj->uuid,
                ]);
            }
        } catch (\Exception $e) {
            // Obj relationship is optional, just log the error
            $this->line("  Could not migrate Obj relationship: " . $e->getMessage());
        }
    }
}
