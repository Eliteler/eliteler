import json
import os

file_path = '/var/www/vhosts/eliteler.net/httpdocs/resources/lang/ar.json'

with open(file_path, 'r', encoding='utf-8') as f:
    data = json.load(f)

# English and lowercase mappings to desired Arabic strings
mapping = {
    "Monday": "يوم الإثنين",
    "Tuesday": "يوم الثلاثاء",
    "Wednesday": "يوم الأربعاء",
    "Thursday": "يوم الخميس",
    "Friday": "يوم الجمعة",
    "Saturday": "يوم السبت",
    "Sunday": "يوم الأحد",
    "monday": "يوم الإثنين",
    "tuesday": "يوم الثلاثاء",
    "wednesday": "يوم الأربعاء",
    "thursday": "يوم الخميس",
    "friday": "يوم الجمعة",
    "saturday": "يوم السبت",
    "sunday": "يوم الأحد"
}

for key, val in mapping.items():
    if key in data:
        data[key] = val

with open(file_path, 'w', encoding='utf-8') as f:
    json.dump(data, f, ensure_ascii=False, indent=2)

print("Arabic translations updated.")
