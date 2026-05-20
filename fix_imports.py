import re
import os

with open('routes/web.php', 'r') as f:
    content = f.read()

used_classes = set(re.findall(r'\[([A-Za-z0-9_]+)::class', content))

existing_imports = set(re.findall(r'use (.*?);', content))

available_classes = set()
for imp in existing_imports:
    imp = imp.strip()
    if ' as ' in imp:
        alias = imp.split(' as ')[-1].strip()
        available_classes.add(alias)
    else:
        cls = imp.split('\\')[-1].strip()
        available_classes.add(cls)

missing_classes = used_classes - available_classes - {'Route', 'Auth', 'File', 'DB', 'App'}

print("Missing classes:", missing_classes)

new_imports = []
for root, dirs, files in os.walk('app/Http/Controllers'):
    for file in files:
        if file.endswith('.php'):
            class_name = file[:-4]
            if class_name in missing_classes:
                with open(os.path.join(root, file), 'r') as cf:
                    c_content = cf.read()
                    ns_match = re.search(r'namespace\s+(.*?);', c_content)
                    if ns_match:
                        ns = ns_match.group(1).strip()
                        full_class = f"{ns}\\{class_name}"
                        # Check if this full class is already imported under an alias
                        # If it is, maybe we should just use it, but for now we'll import it if it's missing
                        new_imports.append(f"use {full_class};")

print("New imports to add:")
for imp in new_imports:
    print(imp)

if new_imports:
    lines = content.split('\n')
    last_use_idx = 0
    for i, line in enumerate(lines):
        if line.startswith('use '):
            last_use_idx = i
    
    for imp in new_imports:
        lines.insert(last_use_idx + 1, imp)
    
    with open('routes/web.php', 'w') as f:
        f.write('\n'.join(lines))
    print("Fixed routes/web.php")
