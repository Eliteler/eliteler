import os
import re

def fix_file(file_path):
    with open(file_path, 'r', encoding='utf-8') as f:
        content = f.read()

    # Pattern to match the nested ternary we created by mistake
    # (App::isLocale('ar') && !empty($...)) ? $... : (App::isLocale('ar') && !empty($...)) ? $... : __($...)
    
    # We want to keep the simplest version. 
    # Usually it looks like:
    # {{ (App::isLocale('ar') && !empty($__t_ar)) ? $__t_ar : (App::isLocale('ar') && !empty($feature_details[0]->title_ar)) ? $feature_details[0]->title_ar : __($feature_details[0]->title) }}
    
    # Regex explanation:
    # Match the start {{
    # Match the first ternary part
    # Match the second ternary part which is redundant
    # Group the final __($...) part
    
    pattern = re.compile(r"\{\{\s*\(App::isLocale\('ar'\)\s*&& !empty\(\$(?:__t_ar|[a-zA-Z0-0_\[\]\-\>]+)\)\)\s*\?\s*\$(?:__t_ar|[a-zA-Z0-0_\[\]\-\>]+)\s*:\s*\(App::isLocale\('ar'\)\s*&& !empty\(\$([a-zA-Z0-0_\[\]\-\>]+)\)\)\s*\?\s*\$[a-zA-Z0-0_\[\]\-\>]+\s*:\s*__\(\$([a-zA-Z0-0_\[\]\-\>]+)\)\s*\}\}")
    
    def replacement(match):
        var1 = match.group(1)
        var2 = match.group(2)
        # We prefer the one with $var1 if possible, but they are likely the same.
        return f"{{{{ (App::isLocale('ar') && !empty(${var1})) ? ${var1} : __(${var2}) }}}}"

    new_content = pattern.sub(replacement, content)
    
    # Also catch cases where $__t_ar was used but it still nested
    # Pattern: (App::isLocale('ar') && !empty($__t_ar)) ? $__t_ar : ... ? ... : ...
    pattern2 = re.compile(r"\(App::isLocale\('ar'\) && !empty\(\$__t_ar\)\) \? \$__t_ar : \(App::isLocale\('ar'\) && !empty\(\$([a-zA-Z0-0_\[\]\-\>]+)\)\) \? \$[a-zA-Z0-0_\[\]\-\>]+ : __\(\$([a-zA-Z0-0_\[\]\-\>]+)\)")
    
    def replacement2(match):
        var1 = match.group(1)
        var2 = match.group(2)
        return f"(App::isLocale('ar') && !empty(${var1})) ? ${var1} : __(${var2})"

    new_content = pattern2.sub(replacement2, new_content)

    if content != new_content:
        with open(file_path, 'w', encoding='utf-8') as f:
            f.write(new_content)
        return True
    return False

directory = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates'
fixed_files = []

for root, dirs, files in os.walk(directory):
    for file in files:
        if file.endswith('.blade.php'):
            path = os.path.join(root, file)
            if fix_file(path):
                fixed_files.append(path)

print(f"Fixed {len(fixed_files)} files.")
for f in fixed_files:
    print(f" - {f}")
