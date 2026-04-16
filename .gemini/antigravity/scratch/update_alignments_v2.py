import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/custom.blade.php'

with open(file_path, 'r') as f:
    lines = f.readlines()

processed_lines = []
for i, line in enumerate(lines):
    # i is 0-indexed, so line 298 is index 297
    line_num = i + 1
    
    # Row Layout (around 298-308)
    if 290 <= line_num <= 315:
        if 'title2_font_size' in line:
            line = line.replace('!important;">', '!important; text-align: {{ $title2_align }};">')
        elif 'subtitle2_font_size' in line:
            line = line.replace('!important;">', '!important; text-align: {{ $title2_align }};">')
        elif 'title_font_size' in line:
            line = line.replace('!important;">', '!important; text-align: {{ $title1_align }};">')
        elif 'sub_title_font_size' in line:
            line = line.replace('!important;">', '!important; text-align: {{ $title1_align }};">')
            
    # Column Layout (around 333-343)
    if 330 <= line_num <= 355:
        if 'title2_font_size' in line:
            line = line.replace('tracking-tighter"', 'tracking-tighter w-full"')
            line = line.replace('!important;">', '!important; text-align: {{ $title2_align }};">')
        elif 'subtitle2_font_size' in line:
            line = line.replace('mt-2 text-md"', 'mt-2 text-md w-full"')
            line = line.replace('!important;">', '!important; text-align: {{ $title2_align }};">')
        elif 'title_font_size' in line:
            line = line.replace('tracking-tighter"', 'tracking-tighter w-full"')
            line = line.replace('!important;">', '!important; text-align: {{ $title1_align }};">')
        elif 'sub_title_font_size' in line:
            line = line.replace('mt-2 text-md"', 'mt-2 text-md w-full"')
            line = line.replace('!important;">', '!important; text-align: {{ $title1_align }};">')
            
    processed_lines.append(line)

with open(file_path, 'w') as f:
    f.writelines(processed_lines)

print("Replacement complete.")
