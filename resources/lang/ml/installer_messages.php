<?php

return [
    'title' => 'നേറ്റീവ് കോഡ് ഇൻസ്റ്റാളർ',
    'next' => 'അടുത്ത ഘട്ടം',
    'back' => 'മുമ്പത്തെ',
    'finish' => 'ഇൻസ്റ്റാൾ ചെയ്യുക',
    'forms' => [
        'errorTitle' => 'ഇനിപ്പറയുന്ന പിശകുകൾ സംഭവിച്ചു:'
    ],
    'welcome' => [
        'templateTitle' => 'സ്വാഗതം',
        'title' => 'നേറ്റീവ് കോഡ് ഇൻസ്റ്റാളർ',
        'message' => 'എളുപ്പത്തിലുള്ള ഇൻസ്റ്റാളേഷനും സജ്ജീകരണ വിസാർഡും.',
        'next' => 'ആവശ്യകതകൾ പരിശോധിക്കുക'
    ],
    'requirements' => [
        'templateTitle' => 'ഘട്ടം 1 | ',
        'title' => 'സെർവർ ആവശ്യകതകൾ',
        'next' => 'അനുമതികൾ പരിശോധിക്കുക'
    ],
    'permissions' => [
        'templateTitle' => 'ഘട്ടം 2 | ',
        'title' => 'അനുമതികൾ',
        'next' => 'പരിസ്ഥിതി കോൺഫിഗർ ചെയ്യുക'
    ],
    'environment' => [
        'menu' => [
            'templateTitle' => 'ഘട്ടം 3 | ',
            'title' => 'ഇൻസ്റ്റലേഷൻ',
            'desc' => 'ആപ്പുകൾ എങ്ങനെ കോൺഫിഗർ ചെയ്യണമെന്ന് ദയവായി തിരഞ്ഞെടുക്കുക <code>.env</code> ഫയൽ.',
            'wizard-button' => 'ഫോം വിസാർഡ് സജ്ജീകരണം',
            'classic-button' => 'ക്ലാസിക് ടെക്സ്റ്റ് എഡിറ്റർ'
        ],
        'wizard' => [
            'templateTitle' => 'ഘട്ടം 3 | ',
            'title' => 'വഴികാട്ടി <code>.env</code> മാന്ത്രികൻ',
            'tabs' => [
                'environment' => 'പരിസ്ഥിതി',
                'database' => 'ഡാറ്റാബേസ്',
                'application' => 'അപേക്ഷ'
            ],
            'form' => [
                'name_required' => 'ഒരു പരിസ്ഥിതി നാമം ആവശ്യമാണ്.',
                'app_name_label' => 'ആപ്പിൻ്റെ പേര്',
                'app_name_placeholder' => 'ആപ്പിൻ്റെ പേര്',
                'app_environment_label' => 'ആപ്പ് പരിസ്ഥിതി',
                'app_environment_label_local' => 'പ്രാദേശിക',
                'app_environment_label_developement' => 'വികസനം',
                'app_environment_label_qa' => 'Qa',
                'app_environment_label_production' => 'ഉത്പാദനം',
                'app_environment_label_other' => 'മറ്റുള്ളവ',
                'app_environment_placeholder_other' => 'നിങ്ങളുടെ പരിസ്ഥിതി നൽകുക...',
                'app_debug_label' => 'ആപ്പ് ഡീബഗ്',
                'app_debug_label_true' => 'സത്യം',
                'app_debug_label_false' => 'തെറ്റായ',
                'app_log_level_label' => 'ആപ്പ് ലോഗ് ലെവൽ',
                'app_log_level_label_debug' => 'ഡീബഗ്',
                'app_log_level_label_info' => 'വിവരം',
                'app_log_level_label_notice' => 'നോട്ടീസ്',
                'app_log_level_label_warning' => 'മുന്നറിയിപ്പ്',
                'app_log_level_label_error' => 'പിശക്',
                'app_log_level_label_critical' => 'വിമർശനാത്മകം',
                'app_log_level_label_alert' => 'ജാഗ്രത',
                'app_log_level_label_emergency' => 'അടിയന്തരാവസ്ഥ',
                'app_url_label' => 'ആപ്പ് Url',
                'app_url_placeholder' => 'ആപ്പ് Url',
                'db_connection_failed' => 'ഡാറ്റാബേസിലേക്ക് ബന്ധിപ്പിക്കാൻ കഴിഞ്ഞില്ല.',
                'db_connection_label' => 'ഡാറ്റാബേസ് കണക്ഷൻ',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'ഡാറ്റാബേസ് ഹോസ്റ്റ്',
                'db_host_placeholder' => 'ഡാറ്റാബേസ് ഹോസ്റ്റ്',
                'db_port_label' => 'ഡാറ്റാബേസ് പോർട്ട്',
                'db_port_placeholder' => 'ഡാറ്റാബേസ് പോർട്ട്',
                'db_name_label' => 'ഡാറ്റാബേസ് നാമം',
                'db_name_placeholder' => 'ഡാറ്റാബേസ് നാമം',
                'db_username_label' => 'ഡാറ്റാബേസ് ഉപയോക്തൃ നാമം',
                'db_username_placeholder' => 'ഡാറ്റാബേസ് ഉപയോക്തൃ നാമം',
                'db_password_label' => 'ഡാറ്റാബേസ് പാസ്‌വേഡ്',
                'db_password_placeholder' => 'ഡാറ്റാബേസ് പാസ്‌വേഡ്',
                'app_tabs' => [
                    'more_info' => 'കൂടുതൽ വിവരങ്ങൾ',
                    'broadcasting_title' => 'ബ്രോഡ്കാസ്റ്റിംഗ്, കാഷിംഗ്, സെഷൻ, & ക്യൂ',
                    'broadcasting_label' => 'ബ്രോഡ്കാസ്റ്റ് ഡ്രൈവർ',
                    'broadcasting_placeholder' => 'ബ്രോഡ്കാസ്റ്റ് ഡ്രൈവർ',
                    'cache_label' => 'കാഷെ ഡ്രൈവർ',
                    'cache_placeholder' => 'കാഷെ ഡ്രൈവർ',
                    'session_label' => 'സെഷൻ ഡ്രൈവർ',
                    'session_placeholder' => 'സെഷൻ ഡ്രൈവർ',
                    'queue_label' => 'ക്യൂ ഡ്രൈവർ',
                    'queue_placeholder' => 'ക്യൂ ഡ്രൈവർ',
                    'redis_label' => 'റെഡിസ് ഡ്രൈവർ',
                    'redis_host' => 'റെഡിസ് ഹോസ്റ്റ്',
                    'redis_password' => 'റെഡിസ് പാസ്‌വേഡ്',
                    'redis_port' => 'റെഡിസ് പോർട്ട്',
                    'mail_label' => 'മെയിൽ',
                    'mail_driver_label' => 'മെയിൽ ഡ്രൈവർ',
                    'mail_driver_placeholder' => 'മെയിൽ ഡ്രൈവർ',
                    'mail_host_label' => 'മെയിൽ ഹോസ്റ്റ്',
                    'mail_host_placeholder' => 'മെയിൽ ഹോസ്റ്റ്',
                    'mail_port_label' => 'മെയിൽ പോർട്ട്',
                    'mail_port_placeholder' => 'മെയിൽ പോർട്ട്',
                    'mail_username_label' => 'മെയിൽ ഉപയോക്തൃനാമം',
                    'mail_username_placeholder' => 'മെയിൽ ഉപയോക്തൃനാമം',
                    'mail_password_label' => 'മെയിൽ പാസ്‌വേഡ്',
                    'mail_password_placeholder' => 'മെയിൽ പാസ്‌വേഡ്',
                    'mail_encryption_label' => 'മെയിൽ എൻക്രിപ്ഷൻ',
                    'mail_encryption_placeholder' => 'മെയിൽ എൻക്രിപ്ഷൻ',
                    'pusher_label' => 'പുഷർ',
                    'pusher_app_id_label' => 'പുഷർ ആപ്പ് ഐഡി',
                    'pusher_app_id_palceholder' => 'പുഷർ ആപ്പ് ഐഡി',
                    'pusher_app_key_label' => 'പുഷർ ആപ്പ് കീ',
                    'pusher_app_key_palceholder' => 'പുഷർ ആപ്പ് കീ',
                    'pusher_app_secret_label' => 'പുഷർ ആപ്പ് രഹസ്യം',
                    'pusher_app_secret_palceholder' => 'പുഷർ ആപ്പ് രഹസ്യം'
                ],
                'buttons' => [
                    'setup_database' => 'ഡാറ്റാബേസ് സജ്ജീകരിക്കുക',
                    'setup_application' => 'സെറ്റപ്പ് ആപ്ലിക്കേഷൻ',
                    'install' => 'ഇൻസ്റ്റാൾ ചെയ്യുക'
                ]
            ]
        ],
        'classic' => [
            'templateTitle' => 'ഘട്ടം 3 | ',
            'title' => 'ക്ലാസിക് എൻവയോൺമെൻ്റ് എഡിറ്റർ',
            'save' => 'സംരക്ഷിക്കുക .env',
            'back' => 'ഫോം വിസാർഡ് ഉപയോഗിക്കുക',
            'install' => 'സംരക്ഷിച്ച് ഇൻസ്റ്റാൾ ചെയ്യുക'
        ],
        'success' => 'നിങ്ങളുടെ .env ഫയൽ ക്രമീകരണങ്ങൾ സംരക്ഷിച്ചു.',
        'errors' => '.env ഫയൽ സംരക്ഷിക്കാൻ കഴിയുന്നില്ല, ദയവായി ഇത് സ്വമേധയാ സൃഷ്ടിക്കുക.'
    ],
    'install' => 'ഇൻസ്റ്റാൾ ചെയ്യുക',
    'installed' => [
        'success_log_message' => 'നേറ്റീവ് കോഡ് ഇൻസ്റ്റാളർ വിജയകരമായി ഇൻസ്റ്റാൾ ചെയ്തു '
    ],
    'final' => [
        'title' => 'ഇൻസ്റ്റലേഷൻ പൂർത്തിയായി',
        'templateTitle' => 'ഇൻസ്റ്റലേഷൻ പൂർത്തിയായി',
        'finished' => 'ആപ്ലിക്കേഷൻ വിജയകരമായി ഇൻസ്റ്റാൾ ചെയ്തു.',
        'migration' => 'മൈഗ്രേഷൻ & സീഡ് കൺസോൾ ഔട്ട്പുട്ട്:',
        'console' => 'ആപ്ലിക്കേഷൻ കൺസോൾ ഔട്ട്പുട്ട്:',
        'log' => 'ഇൻസ്റ്റലേഷൻ ലോഗ് എൻട്രി:',
        'env' => 'അന്തിമ .env ഫയൽ:',
        'exit' => 'പുറത്തുകടക്കാൻ ഇവിടെ ക്ലിക്ക് ചെയ്യുക'
    ],
    'updater' => [
        'title' => 'ലാറവെൽ അപ്ഡേറ്റർ',
        'welcome' => [
            'title' => 'അപ്‌ഡേറ്ററിലേക്ക് സ്വാഗതം',
            'message' => 'അപ്‌ഡേറ്റ് വിസാർഡിലേക്ക് സ്വാഗതം.'
        ],
        'overview' => [
            'title' => 'അവലോകനം',
            'message' => '1 അപ്ഡേറ്റ് ഉണ്ട്.|അവിടെയുണ്ട് :നമ്പർ അപ്ഡേറ്റുകൾ.',
            'install_updates' => 'അപ്ഡേറ്റുകൾ ഇൻസ്റ്റാൾ ചെയ്യുക'
        ],
        'final' => [
            'title' => 'തീർന്നു',
            'finished' => 'ആപ്ലിക്കേഷൻ്റെ ഡാറ്റാബേസ് വിജയകരമായി അപ്ഡേറ്റ് ചെയ്തു.',
            'exit' => 'പുറത്തുകടക്കാൻ ഇവിടെ ക്ലിക്ക് ചെയ്യുക'
        ],
        'log' => [
            'success_message' => 'നേറ്റീവ് കോഡ് ഇൻസ്റ്റാളർ വിജയകരമായി അപ്ഡേറ്റ് ചെയ്തു '
        ]
    ]
];
