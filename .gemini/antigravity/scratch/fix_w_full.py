import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/custom.blade.php'

with open(file_path, 'r') as f:
    lines = f.readlines()

processed_lines = []
for i, line in enumerate(lines):
    line_num = i + 1
            
    # Column Layout (around 333-343)
    if 330 <= line_num <= 355:
        if 'mt-2 text-md"' in line and 'w-full' not in line:
            line = line.replace('mt-2 text-md"', 'mt-2 text-md w-full"')
            
    processed_lines.append(line)

with open(file_path, 'w') as f:
    f.writelines(processed_lines)

print("Fix replacement complete.")
