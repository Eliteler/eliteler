import os

directory = 'resources/views/templates'
target = "onclick=\"downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500)\""
replacement = "onclick=\"downloadQr('{{ config('app.url') . route('dynamic.card', $business_card_details->card_id, false) }}', 500, '{{ addslashes($business_card_details->title) }}')\""

count = 0
for filename in os.listdir(directory):
    if filename.endswith('.blade.php'):
        path = os.path.join(directory, filename)
        with open(path, 'r') as f:
            content = f.read()
        
        if target in content:
            new_content = content.replace(target, replacement)
            with open(path, 'w') as f:
                f.write(new_content)
            print(f"Updated {filename}")
            count += 1

print(f"Total updated: {count}")
