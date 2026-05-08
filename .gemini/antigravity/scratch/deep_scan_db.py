import os
import subprocess

def run_tinker(command):
    cmd = ['php', 'artisan', 'tinker', '--execute', command]
    result = subprocess.run(cmd, capture_output=True, text=True)
    return result.stdout

script = """
$tables = DB::select('SHOW TABLES');
$db = env('DB_DATABASE');
$prop = "Tables_in_" . $db;
foreach ($tables as $table) {
    $tableName = $table->$prop;
    $cols = Schema::getColumnListing($tableName);
    foreach ($cols as $col) {
        $count = DB::table($tableName)->where($col, 'LIKE', '%ElitelerOriginal%')->count();
        if ($count > 0) {
            echo "MATCH: $tableName.$col ($count)\\n";
        }
    }
}
"""
print(run_tinker(script))
