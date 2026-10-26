<?php

namespace App\Services\Admin;

use App\Backup;
use Illuminate\Support\Facades\DB;
use ZipArchive;

/**
 * File and database backups, stored privately under storage/app/backups.
 * backups.status: 1 kept, 0 deleted. backups.path is relative to storage/app.
 */
class BackupService
{
    private const DIR = 'backups';

    /** Folders left out of file backups. */
    private const SKIP = ['storage/app/backups', 'storage/app/auto-backups', 'node_modules', '.git', 'storage/logs', 'storage/framework/cache', 'storage/framework/sessions', 'storage/framework/views'];

    public function absolutePath(Backup $backup): string
    {
        return storage_path('app/' . ltrim($backup->path, '/'));
    }

    /** Zip the whole project (except the folders in SKIP). */
    public function createFileBackup(): Backup
    {
        $this->prepare();
        $version = $this->version();
        $name    = 'file_backup_v' . str_replace('.', '', $version) . '_' . now()->format('Y-m-d_H-i-s') . '.zip';
        $path    = storage_path('app/' . self::DIR . '/' . $name);

        $this->writeProjectZip($path, self::SKIP);

        return $this->record('file', $version, $name, self::DIR . '/' . $name);
    }

    /**
     * Dump every table to a .sql file, written row by row so large databases
     * don't run out of memory. The file can be imported with phpMyAdmin or
     * the mysql command line.
     */
    public function createDatabaseBackup(): Backup
    {
        $this->prepare();
        $version = $this->version();
        $name    = 'database_backup_v' . str_replace('.', '', $version) . '_' . now()->format('Y-m-d_H-i-s') . '.sql';
        $folder  = self::DIR . '/database';
        @mkdir(storage_path('app/' . $folder), 0755, true);

        $this->writeDatabaseDump(storage_path('app/' . $folder . '/' . $name));

        return $this->record('database', $version, $name, $folder . '/' . $name);
    }

    /**
     * Zip the whole project into $path, leaving out symlinks, unreadable files
     * and the folders in $skip (paths relative to the project root).
     * Also used by the AutoBackup plugin.
     */
    public function writeProjectZip(string $path, array $skip = self::SKIP): void
    {
        $this->prepare();

        $zip = new ZipArchive();
        if ($zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException(__('Couldn’t create the zip file. Check that the backup folder is writable.'));
        }

        $base  = base_path() . DIRECTORY_SEPARATOR;
        $files = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator(base_path(), \RecursiveDirectoryIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::SELF_FIRST
        );

        foreach ($files as $file) {
            if ($file->isLink() || ! $file->isReadable()) {
                continue;
            }
            $relative = str_replace('\\', '/', substr($file->getPathname(), strlen($base)));
            if ($this->skipped($relative, $skip)) {
                continue;
            }
            $file->isDir() ? $zip->addEmptyDir($relative) : $zip->addFile($file->getPathname(), $relative);
        }

        if (! $zip->close()) {
            throw new \RuntimeException(__('Couldn’t finish writing the zip file.'));
        }
    }

    /**
     * Dump every table to a .sql file at $path, written row by row so large
     * databases don't run out of memory. A path ending in .gz is gzipped.
     * Also used by the AutoBackup plugin.
     */
    public function writeDatabaseDump(string $path): void
    {
        $this->prepare();

        $gzip   = str_ends_with($path, '.gz');
        $handle = $gzip ? gzopen($path, 'wb6') : fopen($path, 'w');
        if (! $handle) {
            throw new \RuntimeException(__('Couldn’t create the backup file. Check that the backup folder is writable.'));
        }
        $write = fn (string $text) => $gzip ? gzwrite($handle, $text) : fwrite($handle, $text);

        $pdo      = DB::getPdo();
        $database = DB::getDatabaseName();
        $version  = $this->version();

        try {
            $write("-- GoBiz database backup ({$database}), version {$version}\n-- Created " . now()->toDateTimeString() . "\n\n");
            $write("SET NAMES utf8mb4;\nSET FOREIGN_KEY_CHECKS=0;\n\n");

            foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                $table  = array_values((array) $row)[0];
                $quoted = '`' . str_replace('`', '``', $table) . '`';
                $create = (array) DB::selectOne("SHOW CREATE TABLE {$quoted}");

                $write("--\n-- Table {$table}\n--\n");
                $write("DROP TABLE IF EXISTS {$quoted};\n" . $create['Create Table'] . ";\n\n");

                foreach (DB::table($table)->cursor() as $record) {
                    $record  = (array) $record;
                    $columns = implode(', ', array_map(fn ($column) => '`' . str_replace('`', '``', $column) . '`', array_keys($record)));
                    $values  = implode(', ', array_map(fn ($value) => $value === null ? 'NULL' : $pdo->quote((string) $value), $record));
                    $write("INSERT INTO {$quoted} ({$columns}) VALUES ({$values});\n");
                }

                $write("\n");
            }

            $write("SET FOREIGN_KEY_CHECKS=1;\n");
        } finally {
            $gzip ? gzclose($handle) : fclose($handle);
        }
    }

    public function delete(Backup $backup): void
    {
        $path = $this->absolutePath($backup);
        if (is_file($path)) {
            unlink($path);
        }

        $backup->forceFill(['status' => 0])->save();
    }

    private function record(string $type, string $version, string $name, string $path): Backup
    {
        $backup            = new Backup();
        $backup->backup_id = uniqid();
        $backup->type      = $type;
        $backup->version   = $version;
        $backup->file_name = $name;
        $backup->path      = $path;
        $backup->save();

        return $backup;
    }

    private function skipped(string $relative, array $skip = self::SKIP): bool
    {
        foreach ($skip as $folder) {
            if ($relative === $folder || str_starts_with($relative, $folder . '/')) {
                return true;
            }
        }

        return false;
    }

    private function prepare(): void
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');
        @mkdir(storage_path('app/' . self::DIR), 0755, true);
    }

    private function version(): string
    {
        return (string) (DB::table('config')->where('config_key', 'app_version')->value('config_value') ?: '0');
    }
}
