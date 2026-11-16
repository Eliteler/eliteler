import os
import glob

store_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/views/templates/store/'
files = glob.glob(store_path + '**/*.blade.php', recursive=True)

map_html = """
            <!-- Store Map -->
            @if (isset($storeSocialLinks) && $storeSocialLinks->count() > 0)
                @foreach ($storeSocialLinks as $socialLink)
                    @if ($socialLink->type == 'map')
                        <div class="container-xl mb-4">
                            <div class="card p-2 shadow-sm rounded">
                                <iframe referrerpolicy="strict-origin-when-cross-origin" src="https://www.google.com/maps/embed?{!! $socialLink->content !!}" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endif

            <!-- Footer -->"""

for filepath in files:
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()
    
    original = content
    
    # Wrap the li in the if statement
    target_for = "@foreach ($storeSocialLinks as $socialLink)\n                                                    <li class=\"list-inline-item\">"
    if target_for in content:
        content = content.replace(target_for, "@foreach ($storeSocialLinks as $socialLink)\n                                                    @if ($socialLink->type != 'map')\n                                                    <li class=\"list-inline-item\">")
        
    target_endfor = "</a>\n                                                    </li>\n                                                @endforeach"
    if target_endfor in content:
        content = content.replace(target_endfor, "</a>\n                                                    </li>\n                                                    @endif\n                                                @endforeach")

    # Add the map before footer
    if "<!-- Footer -->" in content and "<!-- Store Map -->" not in content:
        content = content.replace("<!-- Footer -->", map_html)
        
    if content != original:
        with open(filepath, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f"Updated {filepath}")

