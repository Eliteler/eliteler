import os
import subprocess

def run_tinker(command):
    cmd = ['php', 'artisan', 'tinker', '--execute', command]
    result = subprocess.run(cmd, capture_output=True, text=True)
    return result.stdout

tables_cmd = "print_r(DB::connection()->getDoctrineSchemaManager()->listTableNames())"
tables_output = run_tinker(tables_cmd)
print(tables_output)

# Since I can't easily parse the output here, I'll just run a specific check on likely tables
check_cmd = """
$tables = ['config', 'pages', 'themes', 'business_cards', 'settings'];
foreach ($tables as $table) {
    $cols = Schema::getColumnListing($table);
    foreach ($cols as $col) {
        $count = DB::table($table)->where($col, 'LIKE', '%ElitelerOriginal%')->count();
        if ($count > 0) {
            echo "Found $count matches in $table.$col\n";
        }
    }
}
"""
print(run_tinker(check_cmd))
