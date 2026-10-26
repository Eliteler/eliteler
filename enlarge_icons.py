import os
import glob

store_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/store/'
files = glob.glob(store_path + '**/*.blade.php', recursive=True)

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original = content
    
    # Replace fs-3 for storeSocialLinks
    target1 = 'class="{{ $socialLink->icon }} fs-3 text-reset"'
    replacement1 = 'class="{{ $socialLink->icon }} fs-2 text-reset"'
    
    # Also replace default icons without fs-3 class
    target2 = 'class="ti ti-brand-facebook-filled"'
    replacement2 = 'class="ti ti-brand-facebook-filled fs-2"'
    
    target3 = 'class="ti ti-brand-twitter-filled"'
    replacement3 = 'class="ti ti-brand-twitter-filled fs-2"'
    
    target4 = 'class="ti ti-brand-linkedin"'
    replacement4 = 'class="ti ti-brand-linkedin fs-2"'
    
    target5 = 'class="ti ti-brand-telegram"'
    replacement5 = 'class="ti ti-brand-telegram fs-2"'
    
    target6 = 'class="ti ti-brand-whatsapp"'
    replacement6 = 'class="ti ti-brand-whatsapp fs-2"'

    content = content.replace(target1, replacement1)
    content = content.replace(target2, replacement2)
    content = content.replace(target3, replacement3)
    content = content.replace(target4, replacement4)
    content = content.replace(target5, replacement5)
    content = content.replace(target6, replacement6)
    
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated icons in {filepath}")

