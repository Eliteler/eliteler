import os
import re

# Replacement rules
replacements = {
    r'\bGoBiz\b': 'Eliteler',
    r'\bgobiz\b': 'eliteler',
    r'\bGOBIZ\b': 'ELITELER',
    'جوبيز': 'إليتيلر'
}

# Directories to scan
scan_dirs = ['resources/views', 'resources/lang', 'plugins']

# Files to exclude (don't rename files or classes)
exclude_extensions = ('.jpg', '.png', '.gif', '.svg', '.zip', '.tar', '.gz')

def replace_in_file(file_path):
    if file_path.endswith(exclude_extensions):
        return False
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = content
        for pattern, replacement in replacements.items():
            new_content = re.sub(pattern, replacement, new_content)
        
        if new_content != content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Updated: {file_path}")
            return True
    except Exception as e:
        # print(f"Error processing {file_path}: {e}")
        pass
    return False

count = 0
for scan_dir in scan_dirs:
    for root, dirs, files in os.walk(scan_dir):
        for file in files:
            file_path = os.path.join(root, file)
            if replace_in_file(file_path):
                count += 1

print(f"Total files updated: {count}")
