<?php

return [
    "title" => "NativeCode Installer",
    "next" => "Sljedeći korak",
    "back" => "Prethodno",
    "finish" => "Instalirati",
    "forms" => [
        "errorTitle" => "Dogodile su se sljedeće pogreške:"
    ],
    "welcome" => [
        "templateTitle" => "Dobrodošli",
        "title" => "NativeCode Installer",
        "message" => "Čarobnjak za jednostavnu instalaciju i postavljanje.",
        "next" => "Provjerite zahtjeve"
    ],
    "requirements" => [
        "templateTitle" => "Korak 1 | ",
        "title" => "Zahtjevi poslužitelja",
        "next" => "Provjerite dopuštenja"
    ],
    "permissions" => [
        "templateTitle" => "Korak 2 | ",
        "title" => "Dozvole",
        "next" => "Konfigurirajte okruženje"
    ],
    "environment" => [
        "menu" => [
            "templateTitle" => "Korak 3 | ",
            "title" => "Montaža",
            "desc" => "Odaberite kako želite konfigurirati aplikacije <code>.env</code> datoteka.",
            "wizard-button" => "Postavljanje čarobnjaka za obrazac",
            "classic-button" => "Klasični uređivač teksta"
        ],
        "wizard" => [
            "templateTitle" => "Korak 3 | ",
            "title" => "Vođeni <code>.env</code> Čarobnjak",
            "tabs" => [
                "environment" => "okoliš",
                "database" => "Baza podataka",
                "application" => "Primjena"
            ],
            "form" => [
                "name_required" => "Potreban je naziv okruženja.",
                "app_name_label" => "Naziv aplikacije",
                "app_name_placeholder" => "Naziv aplikacije",
                "app_environment_label" => "Okolina aplikacije",
                "app_environment_label_local" => "Lokalno",
                "app_environment_label_developement" => "Razvoj",
                "app_environment_label_qa" => "Qa",
                "app_environment_label_production" => "Proizvodnja",
                "app_debug_label" => "Debug aplikacije",
                "app_debug_label_true" => "Pravi",
                "app_debug_label_false" => "lažno",
                "app_log_level_label" => "Razina dnevnika aplikacije",
                "app_log_level_label_debug" => "otkloniti pogreške",
                "app_log_level_label_info" => "info",
                "app_log_level_label_notice" => "obavijest",
                "app_log_level_label_warning" => "upozorenje",
                "app_log_level_label_error" => "greška",
                "app_log_level_label_critical" => "kritičan",
                "app_log_level_label_alert" => "uzbuna",
                "app_log_level_label_emergency" => "hitan slučaj",
                "app_url_label" => "URL aplikacije",
                "app_url_placeholder" => "URL aplikacije",
                "db_connection_failed" => "Nije moguće povezati se s bazom podataka.",
                "db_connection_label" => "Veza s bazom podataka",
                "db_connection_label_mysql" => "mysql",
                "db_connection_label_sqlite" => "sqlite",
                "db_connection_label_pgsql" => "pgsql",
                "db_connection_label_sqlsrv" => "sqlsrv",
                "db_host_label" => "Host baze podataka",
                "db_host_placeholder" => "Host baze podataka",
                "db_port_label" => "Port baze podataka",
                "db_port_placeholder" => "Port baze podataka",
                "db_name_label" => "Naziv baze podataka",
                "db_name_placeholder" => "Naziv baze podataka",
                "db_username_label" => "Korisničko ime baze podataka",
                "db_username_placeholder" => "Korisničko ime baze podataka",
                "db_password_label" => "Lozinka baze podataka",
                "db_password_placeholder" => "Lozinka baze podataka",
                "app_tabs" => [
                    "more_info" => "Više informacija",
                    "broadcasting_title" => "Emitiranje, predmemoriranje, sesija i red čekanja",
                    "broadcasting_label" => "Pokretač emitiranja",
                    "broadcasting_placeholder" => "Pokretač emitiranja",
                    "cache_label" => "Upravljački program za predmemoriju",
                    "cache_placeholder" => "Upravljački program za predmemoriju",
                    "session_label" => "Pokretač sesije",
                    "session_placeholder" => "Pokretač sesije",
                    "queue_label" => "Pokretač čekanja",
                    "queue_placeholder" => "Pokretač čekanja",
                    "redis_label" => "Redis upravljački program",
                    "redis_host" => "Redis Host",
                    "redis_password" => "Redis lozinka",
                    "redis_port" => "Redis luka",
                    "mail_label" => "pošta",
                    "mail_driver_label" => "Mail Driver",
                    "mail_driver_placeholder" => "Mail Driver",
                    "mail_host_label" => "Domaćin pošte",
                    "mail_host_placeholder" => "Domaćin pošte",
                    "mail_port_label" => "Mail Port",
                    "mail_port_placeholder" => "Mail Port",
                    "mail_username_label" => "Korisničko ime pošte",
                    "mail_username_placeholder" => "Korisničko ime pošte",
                    "mail_password_label" => "Lozinka pošte",
                    "mail_password_placeholder" => "Lozinka pošte",
                    "mail_encryption_label" => "Enkripcija pošte",
                    "mail_encryption_placeholder" => "Enkripcija pošte",
                    "pusher_label" => "Gurač",
                    "pusher_app_id_label" => "ID aplikacije za potiskivanje",
                    "pusher_app_id_palceholder" => "ID aplikacije za potiskivanje",
                    "pusher_app_key_label" => "Ključ aplikacije Pusher",
                    "pusher_app_key_palceholder" => "Ključ aplikacije Pusher",
                    "pusher_app_secret_label" => "Tajna aplikacije Pusher",
                    "pusher_app_secret_palceholder" => "Tajna aplikacije Pusher"
                ],
                "buttons" => [
                    "setup_database" => "Postavljanje baze podataka",
                    "setup_application" => "Aplikacija za postavljanje",
                    "install" => "Instalirati"
                ],
                "app_environment_label_one" => "Okolina aplikacije",
                "app_environment_label_few" => "ostalo",
                "app_environment_label_other" => "ostalo",
                "app_environment_placeholder_one" => "Uđite u svoje okruženje...",
                "app_environment_placeholder_few" => "Uđite u svoje okruženje...",
                "app_environment_placeholder_other" => "Uđite u svoje okruženje..."
            ]
        ],
        "classic" => [
            "templateTitle" => "Korak 3 | ",
            "title" => "Klasični uređivač okruženja",
            "save" => "Spremi .env",
            "back" => "Koristite čarobnjak za obrazac",
            "install" => "Spremi i instaliraj"
        ],
        "success" => "Vaše postavke .env datoteke su spremljene.",
        "errors" => "Nije moguće spremiti .env datoteku, izradite je ručno."
    ],
    "install" => "Instalirati",
    "installed" => [
        "success_log_message" => "NativeCode Installer uspješno je INSTALIRAN na "
    ],
    "final" => [
        "title" => "Instalacija završena",
        "templateTitle" => "Instalacija završena",
        "finished" => "Aplikacija je uspješno instalirana.",
        "migration" => "Izlaz konzole migracije i sjemena:",
        "console" => "Izlaz aplikacijske konzole:",
        "log" => "Unos u dnevnik instalacije:",
        "env" => "Konačna .env datoteka:",
        "exit" => "Kliknite ovdje za izlaz"
    ],
    "updater" => [
        "title" => "Laravel Updater",
        "welcome" => [
            "title" => "Dobro došli u program za ažuriranje",
            "message" => "Dobrodošli u čarobnjak za ažuriranje."
        ],
        "overview" => [
            "title" => "Pregled",
            "message" => "Postoji 1 ažuriranje.|Postoji :broj ažuriranja.",
            "install_updates" => "Instalirajte ažuriranja"
        ],
        "final" => [
            "title" => "Gotovo",
            "finished" => "Baza podataka aplikacije je uspješno ažurirana.",
            "exit" => "Kliknite ovdje za izlaz"
        ],
        "log" => [
            "success_message" => "NativeCode Installer uspješno je AŽURIRAN na "
        ]
    ]
];
