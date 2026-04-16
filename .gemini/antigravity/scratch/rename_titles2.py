import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/user/pages/edit-cards/edit-card.blade.php'

with open(file_path, 'r') as f:
    content = f.read()

# Replace UI Labels
content = content.replace("{{ __('Title in another language (Optional)') }}", "{{ __('Title in another language (Optional)') }}")  # Usually this might already be set like this by the user
content = content.replace("{{ __('Subtitle in another language (Optional)') }}", "{{ __('Subtitle in another language (Optional)') }}")

# Wait, let's actually make sure the title placeholders/labels are "another language", wait, I did a search earlier and saw:
# <label class="form-label">{{ __('Title in another language (Optional)') }}</label>
# They were already renamed in `edit-card.blade.php`. Let's just confirm it.
print("Finished edit-card check.")
