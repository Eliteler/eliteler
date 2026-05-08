import os
import re

# Replacement rules
replacements = {
    r'https?://[a-zA-Z0-9.-]*gobiz[a-zA-Z0-9.-]*goapps\.online/?': 'https://eliteler.net',
    r'https?://[a-zA-Z0-9.-]*eliteler[a-zA-Z0-9.-]*goapps\.online/?': 'https://eliteler.net'
}

# Directories to scan
scan_dirs = ['resources/views', 'resources/lang', 'plugins']

def replace_in_file(file_path):
    try:
        with open(file_path, 'r', encoding='utf-8') as f:
            content = f.read()
        
        new_content = content
        for pattern, replacement in replacements.items():
            new_content = re.sub(pattern, replacement, new_content)
        
        if new_content != content:
            with open(file_path, 'w', encoding='utf-8') as f:
                f.write(new_content)
            print(f"Updated URL in: {file_path}")
            return True
    except Exception as e:
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
