import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/custom.blade.php'

with open(file_path, 'r') as f:
    lines = f.readlines()

processed_lines = []
for i, line in enumerate(lines):
    line_num = i + 1
    
    # Update googleFonts array (around 71)
    if 50 <= line_num <= 80:
        if "'Playfair Display'," in line and "'Cairo'," not in line:
            line = line.replace("'Playfair Display',", "'Playfair Display',\n            'Cairo',\n            'Almarai',\n            'Tajawal',\n            'IBM Plex Sans Arabic',\n            'Readex Pro',")
    
    # PHP block header for variables (around 177)
    if 170 <= line_num <= 185:
        if 'subtitle2_align = ' in line and 'title2_font' not in line:
            line = line.replace(
                "subtitle2_align = $custom_styles['subtitle2_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');",
                "subtitle2_align = $custom_styles['subtitle2_alignment'] ?? ($custom_styles['layout'] == 'row' ? 'left' : 'center');\n                $title2_font = $custom_styles['title2_font_family'] ?? 'Poppins';\n                $subtitle2_font = $custom_styles['subtitle2_font_family'] ?? 'Poppins';"
            )

    # Row Layout (around 298-310)
    if 290 <= line_num <= 315:
        if 'title2_font_size' in line:
            line = line.replace('!important;', "!important; font-family: '{{ $title2_font }}', sans-serif;")
        elif 'subtitle2_font_size' in line:
            line = line.replace('!important;', "!important; font-family: '{{ $subtitle2_font }}', sans-serif;")
            
    # Column Layout (around 333-345)
    if 330 <= line_num <= 355:
        if 'title2_font_size' in line:
            line = line.replace('!important;', "!important; font-family: '{{ $title2_font }}', sans-serif;")
        elif 'subtitle2_font_size' in line:
            line = line.replace('!important;', "!important; font-family: '{{ $subtitle2_font }}', sans-serif;")
            
    processed_lines.append(line)

with open(file_path, 'w') as f:
    f.writelines(processed_lines)

print("Title2/Subtitle2 font replacement complete.")
