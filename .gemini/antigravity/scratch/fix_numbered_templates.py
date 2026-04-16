import os
import re

directory = 'resources/views/templates/'
files = [f for f in os.listdir(directory) if f.startswith('template-') and f.endswith('.blade.php')]

# Targeting templates 1, 2, and 3
targets = ['template-1', 'template-2', 'template-3']

h2_regex = re.compile(r'<h2 class="font-medium">@if\(!empty\(\$business_card_details->title2\)\)\s*{{ \$business_card_details->title2 }} <br>\s*@endif\s*@if\(!empty\(\$business_card_details->subtitle2\)\)\s*{{ \$business_card_details->subtitle2 }} <br>\s*@endif\s*{{ \$business_card_details->title }}</h2>', re.DOTALL)

p_regex = re.compile(r'<p class="text-sm text-gray-500">{{ \$card_details->sub_title }}</p>', re.DOTALL)

for filename in files:
    is_target = False
    for t in targets:
        if filename.startswith(t):
            is_target = True
            break
    if not is_target:
        continue

    filepath = os.path.join(directory, filename)
    with open(filepath, 'r') as f:
        content = f.read()

    # New H2 content (only title2 and title)
    new_h2 = '''<h2 class="font-medium">
                                                    @if(!empty($business_card_details->title2))
                                                        {{ $business_card_details->title2 }} <br>
                                                    @endif
                                                    {{ $business_card_details->title }}</h2>'''

    # New P content (subtitle2 and sub_title)
    new_p = '''<p class="text-sm text-gray-500">
                                                @if(!empty($business_card_details->subtitle2))
                                                    {{ $business_card_details->subtitle2 }} <br>
                                                @endif
                                                {{ $card_details->sub_title }}</p>'''

    content = h2_regex.sub(new_h2, content)
    content = p_regex.sub(new_p, content)

    with open(filepath, 'w') as f:
        f.write(content)
    print(f"Updated {filename}")
