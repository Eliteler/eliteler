import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/user/pages/edit-cards/edit-customization.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# Replace UI Labels
content = content.replace("{{ __('Title 2 Font Size (px)') }}", "{{ __('Title in another language Font Size (px)') }}")
content = content.replace("{{ __('Subtitle 2 Font Size (px)') }}", "{{ __('Subtitle in another language Font Size (px)') }}")
content = content.replace("{{ __('Title 2 Alignment') }}", "{{ __('Title in another language Alignment') }}")
content = content.replace("{{ __('Subtitle 2 Alignment') }}", "{{ __('Subtitle in another language Alignment') }}")
content = content.replace("{{ __('Title 2 Font') }}", "{{ __('Title in another language Font') }}")
content = content.replace("{{ __('Subtitle 2 Font') }}", "{{ __('Subtitle in another language Font') }}")

# Optional: also rename the comments to keep it clean
content = content.replace("{{-- Title 2 Font Size --}}", "{{-- Title in another language Font Size --}}")
content = content.replace("{{-- Subtitle 2 Font Size --}}", "{{-- Subtitle in another language Font Size --}}")
content = content.replace("{{-- Title 2 Alignment --}}", "{{-- Title in another language Alignment --}}")
content = content.replace("{{-- Subtitle 2 Alignment --}}", "{{-- Subtitle in another language Alignment --}}")
content = content.replace("{{-- Title 2 Font --}}", "{{-- Title in another language Font --}}")
content = content.replace("{{-- Subtitle 2 Font --}}", "{{-- Subtitle in another language Font --}}")

with open(file_path, 'w') as f:
    f.write(content)

print("Title 2 and Subtitle 2 labels replaced.")
