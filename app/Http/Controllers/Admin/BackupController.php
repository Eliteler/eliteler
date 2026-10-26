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
use App\Http\Controllers\Controller;
use App\Services\Admin\BackupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

/**
 * File and database backups (create, list, delete). Backups stay on the
 * server: they can't be downloaded from the admin panel.
 */
class BackupController extends Controller
{
    public function __construct(private BackupService $backups)
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $search  = trim((string) $request->query('search', ''));
        $tab     = (string) $request->query('tab', 'all');
        $perPage = max(1, (int) $request->query('per_page', 10));

        $query = Backup::where('status', 1);

        // Filter by backup type
        if (in_array($tab, ['database', 'file'], true)) {
            $query->where('type', $tab);
        }

        // Filter by file name or version
        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('file_name', 'like', "%{$search}%")
                    ->orWhere('version', 'like', "%{$search}%");
            });
        }

        // Paginate latest backups
        $backups = $query->latest()->paginate($perPage)->withQueryString()->through(function (Backup $backup) {
            $path = $this->backups->absolutePath($backup);

            return [
                'id'        => $backup->backup_id,
                'type'      => $backup->type === 'database' ? 'database' : 'file',
                'version'   => $backup->version,
                'fileName'  => $backup->file_name,
                'sizeBytes' => is_file($path) ? filesize($path) : null,
                'createdAt' => $backup->created_at?->toIso8601String(),
            ];
        });

        return Inertia::render('settings/backups', [
            'backups' => $backups,
            'version' => (string) DB::table('config')->where('config_key', 'app_version')->value('config_value'),
            'search'  => $search,
            'tab'     => $tab,
        ]);
    }

    // Old DataTables endpoint
    public function getDatabaseBackup()
    {
        return redirect()->route('admin.backups');
    }

    public function createFileBackup()
    {
        return $this->create(fn() => $this->backups->createFileBackup(), __('File backup created.'));
    }

    public function createDatabaseBackup()
    {
        return $this->create(fn() => $this->backups->createDatabaseBackup(), __('Database backup created.'));
    }

    /**
     * Restoring from the admin panel was never offered in the UI and the old
     * database restore could leave the database half-empty, so it is disabled.
     * Restore on the server from storage/app/backups.
     */
    public function restore()
    {
        return redirect()->route('admin.backups')->with('failed', __('Restore isn’t available here. Restore from storage/app/backups on your server.'));
    }

    public function delete(Request $request)
    {
        $backup = Backup::where('backup_id', (string) $request->input('id'))->where('status', 1)->firstOrFail();
        $this->backups->delete($backup);

        return back()->with('success', __('Backup deleted.'));
    }

    private function create(callable $run, string $message)
    {
        try {
            $run();
        } catch (\Throwable $e) {
            report($e);

            return back()->with('failed', __('Backup failed: :error', ['error' => $e->getMessage()]));
        }

        return back()->with('success', $message);
    }
}
