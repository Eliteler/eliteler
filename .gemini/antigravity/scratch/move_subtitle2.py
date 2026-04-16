import os
import re

directory = 'resources/views/templates/'

def fix_file(filepath):
    with open(filepath, 'r') as f:
        content = f.read()

    # Pattern 1: subtitle2 inside a title tag (messy implementation)
    # This pattern finds title2 and subtitle2 inside an h1/h2/h3, followed by a sub_title tag.
    # We want to move subtitle2 to the sub_title tag.
    
    # Regex to find title2 and subtitle2 inside hX and then sub_title in next tag
    # Example: <h...>[title2][subtitle2][title]</h...> ... <p...>[sub_title]</p...Outer>
    
    # Let's use a simpler approach:
    # 1. Remove subtitle2 from title tags.
    # 2. Add subtitle2 to subtitle tags (p/span containing sub_title).
    
    changed = False
    
    # Step 1: Remove subtitle2 from title tags if it exists alongside title
    title_tags = ['h1', 'h2', 'h3']
    for tag in title_tags:
        # Pattern to find subtitle2 inside a tag that also contains title OR title2
        pattern = r'<{tag}([^>]*)>(.*?)@if\(!empty\(\$business_card_details->subtitle2\)\)\s*{{ \$business_card_details->subtitle2 }} <br>\s*@endif(.*?)<\/{tag}>'.format(tag=tag)
        if re.search(pattern, content, re.DOTALL):
            content = re.sub(pattern, r'<\g<1>\g<2>\g<3></\g<tag>>', content, flags=re.DOTALL)
            changed = True

    # Step 2: Ensure subtitle2 is in the sub_title tag
    # Find tags containing $card_details->sub_title
    subtitle_pattern = r'<(p|span|h4|h5)([^>]*)>(\s*)([^{<]*){{ \$card_details->sub_title }}(\s*)<\/\1>'
    if '{{ $card_details->sub_title }}' in content and 'subtitle2' not in content: # This logic is tricky if subtitle2 was moved
        # If subtitle2 is not present at all, we might need to add it.
        # But wait, my Step 1 only removed it if it was inside a title tag.
        pass

    # Actually, let's do a targeted replacement for the "gym" style pattern which is common
    gym_pattern = re.compile(r'(<h[123][^>]*>)\s*@if\(!empty\(\$business_card_details->title2\)\)\s*{{ \$business_card_details->title2 }} <br>\s*@endif\s*@if\(!empty\(\$business_card_details->subtitle2\)\)\s*{{ \$business_card_details->subtitle2 }} <br>\s*@endif\s*({{ \$business_card_details->title }})', re.DOTALL)
    if gym_pattern.search(content):
        content = gym_pattern.sub(r'\1\n                                            @if(!empty($business_card_details->title2))\n                                                {{ $business_card_details->title2 }} <br>\n                                            @endif\n                                            \2', content)
        changed = True

    # And then insert subtitle2 before sub_title
    sub_title_tag_pattern = re.compile(r'(<p[^>]*>)\s*({{ \$card_details->sub_title }})', re.DOTALL)
    if 'subtitle2' not in content and sub_title_tag_pattern.search(content):
         content = sub_title_tag_pattern.sub(r'\1\n                                            @if(!empty($business_card_details->subtitle2))\n                                                {{ $business_card_details->subtitle2 }} <br>\n                                            @endif\n                                            \2', content)
         changed = True

    if changed:
        with open(filepath, 'w') as f:
            f.write(content)
        return True
    return False

for root, dirs, files in os.walk(directory):
    for filename in files:
        if filename.endswith('.blade.php'):
            filepath = os.path.join(root, filename)
            if fix_file(filepath):
                print(f"Fixed {filepath}")
