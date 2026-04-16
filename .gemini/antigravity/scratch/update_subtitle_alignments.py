import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/custom.blade.php'

with open(file_path, 'r') as f:
    lines = f.readlines()

processed_lines = []
for i, line in enumerate(lines):
    line_num = i + 1
    
    # Update PHP block header (around 172-177)
    if 170 <= line_num <= 180:
        if 'title2_align = ' in line and 'subtitle_align' not in line:
            line = line.replace(
                "title2_align = $custom_styles['title2_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');",
                "title2_align = $custom_styles['title2_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');\n                $subtitle_align = $custom_styles['subtitle_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');\n                $subtitle2_align = $custom_styles['subtitle2_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');"
            )

    # Row Layout (around 298-310)
    if 290 <= line_num <= 315:
        if 'subtitle2_font_size' in line:
            line = line.replace('text-align: {{ $title2_align }};', 'text-align: {{ $subtitle2_align }};')
        elif 'sub_title_font_size' in line:
            line = line.replace('text-align: {{ $title1_align }};', 'text-align: {{ $subtitle_align }};')
            
    # Column Layout (around 333-345)
    if 330 <= line_num <= 355:
        if 'subtitle2_font_size' in line:
            line = line.replace('text-align: {{ $title2_align }};', 'text-align: {{ $subtitle2_align }};')
        elif 'sub_title_font_size' in line:
            line = line.replace('text-align: {{ $title1_align }};', 'text-align: {{ $subtitle_align }};')
            
    processed_lines.append(line)

with open(file_path, 'w') as f:
    f.writelines(processed_lines)

print("Subtitle alignment replacement complete.")
