<?php

/*
 |--------------------------------------------------------------------------
 | GoBiz vCard SaaS
 |--------------------------------------------------------------------------
 | Developed by NativeCode © 2021 - https://nativecode.in
 | All rights reserved
 | Unauthorized distribution is prohibited
 |--------------------------------------------------------------------------
*/

namespace App\Http\Controllers\Admin;

use App\Backup;
use ZipArchive;
use App\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class BackupController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Get File Backup
    public function index(Request $request)
    {
        // Backups
        if ($request->ajax()) {
            $data = Backup::where('status', 1)->where('type', 'file');

            // Files Backups
            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('version', function ($row) {
                    return $row->version;
                })
                ->addColumn('created_at', function ($row) { return $row->created_at->format('d-m-Y H:i:s'); })->addColumn('status', function ($row) {
                    return $row->status == 0
                        ? '<span class="badge bg-red text-white text-white">' . __('No Backed Up') . '</span>'
                        : '<span class="badge bg-green text-white text-white">' . __('Backed Up') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $downloadButton = '<a href="' . route('admin.backup.download') . '?id=' . $row->backup_id . '" class="dropdown-item">' . __('Download') . '</a>';

                    return '<span class="dropdown">
                                <button class="btn-action" data-bs-boundary="viewport" data-bs-toggle="dropdown" aria-expanded="false">
                                    <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                        height="24" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                        stroke-linejoin="round"
                                        class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                        <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                        <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    </svg>
                                </button>
                                <div class="actions dropdown-menu dropdown-menu-end">
                                    ' . $downloadButton . '
                                    <a class="dropdown-item" href="#" onclick="deleteBackup(\'' . $row->backup_id . '\'); return false;">' . __('Delete') . '</a>
                                </div>
                            </span>';
                })
                ->rawColumns(['version', 'status', 'action'])
                ->make(true);
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.backups.index', compact('settings', 'config'));
    }

    // Get Database Backup
    public function getDatabaseBackup(Request $request)
    {
        // Database Backups
        if ($request->ajax()) {
            $data = Backup::where('status', 1)->where('type', 'database');

            return DataTables::of($data)
                ->addIndexColumn()
                ->addColumn('version', function ($row) {
                    return $row->version;
                })
                ->addColumn('created_at', function ($row) { return $row->created_at->format('d-m-Y H:i:s'); })->addColumn('status', function ($row) {
                    return $row->status == 0
                        ? '<span class="badge bg-red text-white text-white">' . __('No Backed Up') . '</span>'
                        : '<span class="badge bg-green text-white text-white">' . __('Backed Up') . '</span>';
                })
                ->addColumn('action', function ($row) {
                    $downloadButton = '<a href="' . route('admin.backup.download') . '?id=' . $row->backup_id . '" class="dropdown-item">' . __('Download') . '</a>';

                    return '<a class="btn-action" href="#" data-toggle="dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                <!-- Download SVG icon from http://tabler-icons.io/i/dots-vertical -->
                                <svg xmlns="http://www.w3.org/2000/svg" width="24"
                                    height="24" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round"
                                    class="icon icon-tabler icons-tabler-outline icon-tabler-dots fw-bold">
                                    <path stroke="none" d="M0 0h24v24H0z" fill="none" />
                                    <path d="M4 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                    <path d="M18 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
                                </svg>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end" style="">
                                <div class="nav-item dropdown">
                                    ' . $downloadButton . '
                                    <a class="dropdown-item" href="#" onclick="deleteBackup(\'' . $row->backup_id . '\'); return false;">' . __('Delete') . '</a>
                                </div>
                            </div>';
                })
                ->rawColumns(['version', 'status', 'action'])
                ->make(true);
        }

        // Queries
        $settings = Setting::where('status', 1)->first();
        $config = DB::table('config')->get();

        return view('admin.pages.backups.index', compact('settings', 'config'));
    }

    // Create File Backup
    public function createFileBackup()
    {
        // Increase resource limits for large zip creation
        set_time_limit(0); 
        ini_set('memory_limit', '1024M');

        $version = DB::table('config')
            ->where('config_key', 'app_version')
            ->value('config_value');

        if (!$version) {
            return redirect()->route('admin.backups')
                ->with('failed', trans('Version not found!'));
        }

        try {

            $backupDir = storage_path('app/backups');

            if (!file_exists($backupDir)) {
                mkdir($backupDir, 0755, true);
            }

            $zipVersion = str_replace('.', '', $version);
            $zipFileName = 'file_backup_v' . $zipVersion . '_' . date('Y-m-d') . '.zip';
            $zipFilePath = $backupDir . '/' . $zipFileName;

            $zip = new \ZipArchive();

            if ($zip->open($zipFilePath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
                return redirect()->route('admin.backups')
                    ->with('failed', trans('Unable to create zip file.'));
            }

            $productFolder = base_path();

            if (!is_dir($productFolder)) {
                return redirect()->route('admin.backups')
                    ->with('failed', trans('Backup folder not found!'));
            }

            $this->addFolderToZip($productFolder, $zip);

            $zip->close();

            $backup = new Backup();
            $backup->backup_id = uniqid();
            $backup->type = 'file';
            $backup->version = $version;
            $backup->file_name = $zipFileName;
            $backup->path = 'backups/' . $zipFileName;
            $backup->save();

            return redirect()->route('admin.backups')
                ->with('success', trans('Created!'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups')
                ->with('failed', 'An error occurred: ' . $e->getMessage());
        }
    }

    // Create Database Backup
    public function createDatabaseBackup()
    {
        // Fetch the application version from the config table
        $version = DB::table('config')->where('config_key', 'app_version')->value('config_value');

        // Check if the version exists
        if (!$version) {
            return redirect()->route('admin.backups')->with('failed', trans('Version not found!'));
        }

        try {
            // Get database connection settings from .env
            $dbName = env('DB_DATABASE');

            // Generate a backup file name with timestamp
            $timestamp = Carbon::now()->format('Y-m-d_H-i-s');

            // File name and path
            $includeVersionName = str_replace('.', '', $version);
            $backupFileName = "database_backup_v{$includeVersionName}_{$timestamp}.sql";

            // Create a new backup record
            $backup = new Backup();
            $backup->backup_id = uniqid();
            $backup->type = 'database';
            $backup->version = $version;
            $backup->file_name = $backupFileName;
            $backup->path = 'backups/database/' . $backupFileName; // Corrected to store the relative path
            $backup->save();

            // Start the SQL backup file content
            $backupContent = "-- Database backup for {$dbName}\n";
            $backupContent .= "-- Created on {$timestamp}\n\n";

            // Get all tables from the database
            $tables = DB::select('SHOW TABLES');

            foreach ($tables as $table) {
                $tableName = $table->{"Tables_in_{$dbName}"};

                // Escape the table name to handle reserved words
                $escapedTableName = "`{$tableName}`";  // Correct escape with backticks

                // Get the CREATE TABLE statement with escaped table name
                $createTable = DB::select("SHOW CREATE TABLE {$escapedTableName}");
                $backupContent .= "--\n-- Create table {$tableName}\n--\n";
                $backupContent .= $createTable[0]->{"Create Table"} . ";\n\n";

                // Get all rows from the table
                $rows = DB::table(str_replace('`', '', $escapedTableName))->get();

                // Insert rows into the backup
                foreach ($rows as $row) {
                    $columns = array_keys((array) $row); // Get column names
                    $values = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL'; // Use SQL NULL for null values
                        }
                        return DB::getPdo()->quote($value); // Quote other values
                    }, (array) $row);

                    $backupContent .= "INSERT INTO {$escapedTableName} (" . implode(", ", $columns) . ") VALUES (" . implode(", ", $values) . ");\n";
                }

                $backupContent .= "\n";
            }

            // Save the backup content to the file
            Storage::put("backups/database/{$backupFileName}", $backupContent);

            // Return the success message
            return redirect()->route('admin.backups')->with('success', __('Created!'));
        } catch (\Exception $e) {
            return redirect()->route('admin.backups')->with('failed', $e->getMessage());
        }
    }

    // Restore Backup File
    public function restore(Request $request)
    {
        // Increase resource limits for restoration
        set_time_limit(600); // 10 minutes
        ini_set('memory_limit', '512M');

        // Get the backup
        $backup = Backup::where('backup_id', $request->query('id'))->first();

        if (!$backup) {
            return redirect()->route('admin.backups')->with('failed', __('Not Found!'));
        }

        $filePath = storage_path('app/' . $backup->path);

        if (!file_exists($filePath)) {
            Log::error("Backup restore failed: File not found at {$filePath}");
            return redirect()->route('admin.backups')->with('failed', __('Backup file not found!'));
        }

        try {
            Log::info("Starting backup restore for version {$backup->version} (Type: {$backup->type}, ID: {$backup->backup_id})");
            $this->updateProgress('starting', __('Preparing restoration...'));

            if ($backup->type == 'file') {
                // Ensure it's a zip file
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                if ($extension !== 'zip' && strpos($backup->file_name, '.zip') === false) {
                    Log::error("Backup restore failed: Expected .zip file for 'file' type, but got " . $extension);
                    $this->updateProgress('failed', __('Invalid backup file format for file restoration!'));
                    return redirect()->route('admin.backups')->with('failed', __('Invalid backup file format for file restoration!'));
                }

                $this->updateProgress('processing', __('Opening backup file...'));
                // Unzip the file
                $zip = new ZipArchive();
                $status = $zip->open($filePath);
                if ($status !== true) {
                    Log::error("Backup restore failed: Could not open zip file. status code: " . $status);
                    $this->updateProgress('failed', __('Could not open zip file. Error code: ') . $status);
                    return redirect()->route('admin.backups')->with('failed', __('Could not open zip file. Error code: ') . $status);
                }

                Log::info("Extracting file backup to " . base_path());
                $this->updateProgress('processing', __('Extracting files (this may take several minutes)...'));
                $zip->extractTo(base_path());
                $zip->close();
                Log::info("File extraction completed successfully.");
                $this->updateProgress('processing', __('Files extracted successfully.'));
            } else {
                // Ensure it's a sql file
                $extension = pathinfo($filePath, PATHINFO_EXTENSION);
                if ($extension !== 'sql' && strpos($backup->file_name, '.sql') === false) {
                    Log::error("Backup restore failed: Expected .sql file for 'database' type, but got " . $extension);
                    $this->updateProgress('failed', __('Invalid backup file format for database restoration!'));
                    return redirect()->route('admin.backups')->with('failed', __('Invalid backup file format for database restoration!'));
                }

                // Import database
                Log::info("Reading database backup file...");
                $this->updateProgress('processing', __('Reading database backup file...'));
                $sql = file_get_contents($filePath);

                // Disable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');

                // Drop all existing tables before import to prevent "Table already exists" errors
                $tables = DB::select('SHOW TABLES');
                $dbName = env('DB_DATABASE');

                Log::info("Dropping all existing database tables for a clean restoration...");
                $this->updateProgress('processing', __('Cleaning up existing database tables...'));
                foreach ($tables as $table) {
                    $tableName = $table->{"Tables_in_{$dbName}"};
                    Schema::drop($tableName);
                }

                // Execute the SQL file
                Log::info("Executing SQL import...");
                $this->updateProgress('processing', __('Importing database records...'));
                DB::unprepared($sql);

                // Re-enable foreign key checks
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                Log::info("Database import completed successfully.");
                $this->updateProgress('processing', __('Database imported successfully.'));
            }
        } catch (\Exception $e) {
            Log::error("Backup restore failed with exception: " . $e->getMessage());
            Log::error($e->getTraceAsString());
            $this->updateProgress('failed', __('Restore failed: ' . $e->getMessage()));
            return redirect()->route('admin.backups')->with('failed', __('Restore failed: ' . $e->getMessage()));
        }

        Log::info("Restore process finished successfully for version {$backup->version}");
        $this->updateProgress('completed', __('Restoration completed successfully!'));
        
        // Short delay to ensure the frontend sees the "completed" status
        sleep(1);
        
        // Clean up progress file
        Storage::delete('restore_progress.json');
        
        return redirect()->route('admin.backups')->with('success', __($backup->version . ' restored successfully!'));
    }

    // Download Backup
    public function download(Request $request)
    {
        // Get the backup
        $backup = Backup::where('backup_id', $request->query('id'))->first();

        if ($backup) {
            // Zip existing files
            try {
                return response()->download(storage_path('app/' . $backup->path));
            } catch (\Exception $e) {
                return redirect()->route('admin.backups')->with('failed', trans('Not Found!'));
            }
        }

        return redirect()->route('admin.backups')->with('failed', trans('Not Found!'));
    }

    // Delete backup
    public function delete(Request $request)
    {
        // Get the backup
        $backup = Backup::where('backup_id', $request->query('id'))->first();

        if ($backup) {
            // Delete backup
            try {
                unlink(storage_path('app/' . $backup->path));
            } catch (\Exception $e) {
                // Check if the backup file not found
                if (strpos($e->getMessage(), 'No such file or directory') !== false) {
                    // Delete backup details from the database
                    Backup::where('backup_id', $request->query('id'))->update(['status' => 0]);

                    return redirect()->route('admin.backups')->with('success', trans('Deleted!'));
                }
                return redirect()->route('admin.backups')->with('failed', trans('Failed to delete backup!'));
            }

            // Delete backup details from the database
            Backup::where('backup_id', $request->query('id'))->update(['status' => 0]);

            return redirect()->route('admin.backups')->with('success', trans('Deleted!'));
        }
        return redirect()->route('admin.backups')->with('failed', trans('Not Found!'));
    }

    // Helper function to add folders to a zip file
    private function addFolderToZip($folder, $zip)
    {
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(
                $folder,
                \RecursiveDirectoryIterator::SKIP_DOTS
            ),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {

            if ($file->isLink()) {
                continue;
            }

            $filePath = $file->getRealPath();

            if (!$filePath || !file_exists($filePath) || !is_readable($filePath)) {
                continue;
            }

            if (
                str_contains($filePath, 'storage/app/backups') ||
                str_contains($filePath, 'storage/framework') ||
                str_contains($filePath, 'vendor') ||
                str_contains($filePath, 'node_modules') ||
                str_contains($filePath, '.git') ||
                str_contains($filePath, 'storage/logs') ||
                str_contains($filePath, 'gobiz-source-code.zip') ||
                str_contains($filePath, '__MACOSX')
            ) {
                continue;
            }

            $relativePath = str_replace(
                base_path() . DIRECTORY_SEPARATOR,
                '',
                $filePath
            );

            if ($file->isDir()) {
                $zip->addEmptyDir($relativePath);
            } else {
                $zip->addFile($filePath, $relativePath);
            }
        }
    }

    // Update progress
    private function updateProgress($status, $details = '')
    {
        Storage::put('restore_progress.json', json_encode([
            'status' => $status,
            'details' => $details,
            'updated_at' => now()->toDateTimeString()
        ]));
    }

    // Get restoration status
    public function restoreStatus()
    {
        if (Storage::exists('restore_progress.json')) {
            $status = json_decode(Storage::get('restore_progress.json'), true);
            return response()->json($status);
        }
        return response()->json(['status' => 'idle']);
    }
}
