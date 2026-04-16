import json
import os
import re

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/custom.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# Row Layout Replacements
content = re.sub(
    r'(<h1\s+class="lg:text-4xl text-2xl font-medium text-\[{{ \$custom_styles\[\x27title_color\x27\] \}\}] tracking-tighter" style="margin-bottom: 0px; padding-bottom: 0px; font-size: \{\{ \$custom_styles\[\x27title2_font_size\x27\] \?\? \x2736\x27 \}\}px !important;">\{\{ \$business_card_details->title2 \}\}</h1>)',
    r'\1'.replace('!important;">', '!important; text-align: {{ $title2_align }};">'),
    content
)

content = re.sub(
    r'(<p class="text-\[{{ \$custom_styles\[\x27sub_title_color\x27\] \}\}] font-bold mt-2 text-md" style="margin-bottom: 5px; padding-bottom: 0px; font-size: \{\{ \$custom_styles\[\x27subtitle2_font_size\x27\] \?\? \x2718\x27 \}\}px !important;">\{\{ \$business_card_details->subtitle2 \}\}</p>)',
    r'\1'.replace('!important;">', '!important; text-align: {{ $title2_align }};">'),
    content
)

content = re.sub(
    r'(<h1\s+class="lg:text-4xl text-2xl font-medium text-\[{{ \$custom_styles\[\x27title_color\x27\] \}\}] tracking-tighter" style="font-size: \{\{ \$custom_styles\[\x27title_font_size\x27\] \?\? \x2736\x27 \}\}px !important;">\{\{ \$business_card_details->title \}\}</h1>)',
    r'\1'.replace('!important;">', '!important; text-align: {{ $title1_align }};">'),
    content
)

content = re.sub(
    r'(<p class="text-\[{{ \$custom_styles\[\x27sub_title_color\x27\] \}\}] font-bold mt-2 text-md" style="font-size: \{\{ \$custom_styles\[\x27sub_title_font_size\x27\] \?\? \x2718\x27 \}\}px !important;">\{\{ \$card_details->sub_title \}\}</p>)',
    r'\1'.replace('!important;">', '!important; text-align: {{ $title1_align }};">'),
    content
)

# For the column layout, we might need slightly different regex if class order is different or if w-full is needed.
# But looking at the file, they are identical except for the container.

# I will also add w-full to the column layout ones to ensure text-align works correctly.
# I'll do this by matching the blocks after line 319 (Column layout start)

with open(file_path, 'w') as f:
    f.write(content)

print("Replacement complete.")
