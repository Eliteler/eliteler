import re

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/user/pages/store/include/country-code.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

def replace_option(match):
    value = match.group(1)
    label = match.group(2)
    default_selected = 'selected' in match.group(0)
    
    if value == '1':
        selected_logic = "{{ old('country_code', '1') == '1' ? 'selected' : '' }}"
    else:
        selected_logic = "{{ old('country_code') == '" + value + "' ? 'selected' : '' }}"
    
    return f'<option value="{value}" {selected_logic}>{label}</option>'

# Regex to match <option value="xxx">...</option>
new_content = re.sub(r'<option value="(\d+)"(?: selected)?>{{ __\(\'(.*?)\'\) }}</option>', replace_option, content)

with open(file_path, 'w') as f:
    f.write(new_content)
