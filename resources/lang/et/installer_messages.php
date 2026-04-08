<?php

return [
    "title" => "NativeCode installija",
    "next" => "Järgmine samm",
    "back" => "Eelnev",
    "finish" => "Paigaldama",
    "forms" => [
        "errorTitle" => "Tekkis järgmised vead:"
    ],
    "welcome" => [
        "templateTitle" => "Tervitus",
        "title" => "NativeCode installija",
        "message" => "Lihtne paigaldamine ja seadistusviisard.",
        "next" => "Kontrollinõuded"
    ],
    "requirements" => [
        "templateTitle" => "1. samm | ",
        "title" => "Serveri nõuded",
        "next" => "Kontrollida õigusi"
    ],
    "permissions" => [
        "templateTitle" => "2. samm | ",
        "title" => "Luba",
        "next" => "Keskkonna konfigureerimine"
    ],
    "environment" => [
        "menu" => [
            "templateTitle" => "3. samm | ",
            "title" => "Paigaldus",
            "desc" => "Valige, kuidas soovite rakendusi konfigureerida <code>.env</code> fail.",
            "wizard-button" => "Vormi viisardi seadistamine",
            "classic-button" => "Klassikaline tekstiredaktor"
        ],
        "wizard" => [
            "templateTitle" => "3. samm | ",
            "title" => "Juhendatud <code>.env</code> Võlur",
            "tabs" => [
                "environment" => "Keskkond",
                "database" => "Andmebaas",
                "application" => "Rakendus"
            ],
            "form" => [
                "name_required" => "Vajalik on keskkonnanimi.",
                "app_name_label" => "Rakenduse nimi",
                "app_name_placeholder" => "Rakenduse nimi",
                "app_environment_label" => "Rakendusekeskkond",
                "app_environment_label_local" => "Kohalik",
                "app_environment_label_developement" => "Arendamine",
                "app_environment_label_qa" => "QA",
                "app_environment_label_production" => "Tootmine",
                "app_environment_label_other" => "Teine",
                "app_environment_placeholder_other" => "Sisestage oma keskkonda ...",
                "app_debug_label" => "Rakenduse silumine",
                "app_debug_label_true" => "True",
                "app_debug_label_false" => "Vale",
                "app_log_level_label" => "Rakenduse logi tase",
                "app_log_level_label_debug" => "silumine",
                "app_log_level_label_info" => "teave",
                "app_log_level_label_notice" => "teade",
                "app_log_level_label_warning" => "hoiatus",
                "app_log_level_label_error" => "viga",
                "app_log_level_label_critical" => "kriitiline",
                "app_log_level_label_alert" => "märguanne",
                "app_log_level_label_emergency" => "hädaolukord",
                "app_url_label" => "Rakenduse URL",
                "app_url_placeholder" => "Rakenduse URL",
                "db_connection_failed" => "Andmebaasiga ei saanud ühendust luua.",
                "db_connection_label" => "Andmebaasiühendus",
                "db_connection_label_mysql" => "mysql",
                "db_connection_label_sqlite" => "sqlite",
                "db_connection_label_pgsql" => "pgsql",
                "db_connection_label_sqlsrv" => "SQLSRV",
                "db_host_label" => "Andmebaasi host",
                "db_host_placeholder" => "Andmebaasi host",
                "db_port_label" => "Andmebaasi port",
                "db_port_placeholder" => "Andmebaasi port",
                "db_name_label" => "Andmebaasi nimi",
                "db_name_placeholder" => "Andmebaasi nimi",
                "db_username_label" => "Andmebaasi kasutajanimi",
                "db_username_placeholder" => "Andmebaasi kasutajanimi",
                "db_password_label" => "Andmebaasi parool",
                "db_password_placeholder" => "Andmebaasi parool",
                "app_tabs" => [
                    "more_info" => "Lisateave",
                    "broadcasting_title" => "Ringhääling, vahemällu salvestamine, seanss ja järjekord",
                    "broadcasting_label" => "Saatejuht",
                    "broadcasting_placeholder" => "Saatejuht",
                    "cache_label" => "Vahemälujuht",
                    "cache_placeholder" => "Vahemälujuht",
                    "session_label" => "Seansijuht",
                    "session_placeholder" => "Seansijuht",
                    "queue_label" => "Järjekorrajuht",
                    "queue_placeholder" => "Järjekorrajuht",
                    "redis_label" => "Redise juht",
                    "redis_host" => "Redis host",
                    "redis_password" => "Redis parool",
                    "redis_port" => "Redise port",
                    "mail_label" => "Post",
                    "mail_driver_label" => "Postijuht",
                    "mail_driver_placeholder" => "Postijuht",
                    "mail_host_label" => "Postimest",
                    "mail_host_placeholder" => "Postimest",
                    "mail_port_label" => "Postiportaar",
                    "mail_port_placeholder" => "Postiportaar",
                    "mail_username_label" => "Posti kasutajanimi",
                    "mail_username_placeholder" => "Posti kasutajanimi",
                    "mail_password_label" => "Posti parool",
                    "mail_password_placeholder" => "Posti parool",
                    "mail_encryption_label" => "Postikrüptimine",
                    "mail_encryption_placeholder" => "Postikrüptimine",
                    "pusher_label" => "Tõukaja",
                    "pusher_app_id_label" => "Tõukajarakenduse ID",
                    "pusher_app_id_palceholder" => "Tõukajarakenduse ID",
                    "pusher_app_key_label" => "Tõukuri rakenduse võti",
                    "pusher_app_key_palceholder" => "Tõukuri rakenduse võti",
                    "pusher_app_secret_label" => "Pusher App Secret",
                    "pusher_app_secret_palceholder" => "Pusher App Secret"
                ],
                "buttons" => [
                    "setup_database" => "Seadistamise andmebaas",
                    "setup_application" => "Seadistusrakendus",
                    "install" => "Paigaldama"
                ]
            ]
        ],
        "classic" => [
            "templateTitle" => "3. samm | ",
            "title" => "Klassikaline keskkonnatoimetaja",
            "save" => "Salvesta .env",
            "back" => "Kasutage vormi viisardit",
            "install" => "Salvesta ja installida"
        ],
        "success" => "Teie .env faili sätted on salvestatud.",
        "errors" => "Kui ei saa .env -faili salvestada, looge see käsitsi."
    ],
    "install" => "Paigaldama",
    "installed" => [
        "success_log_message" => "NativeCode Installer installiti edukalt "
    ],
    "final" => [
        "title" => "Paigaldus valmis",
        "templateTitle" => "Paigaldus valmis",
        "finished" => "Rakendus on edukalt installitud.",
        "migration" => "Rände- ja seemnekonsooli väljund:",
        "console" => "Rakenduskonsooli väljund:",
        "log" => "Installimislogi kirje:",
        "env" => "Lõplik .env -fail:",
        "exit" => "Väljumiseks klõpsake siin"
    ],
    "updater" => [
        "title" => "Laraveli värskendaja",
        "welcome" => [
            "title" => "Tere tulemast värskendaja juurde",
            "message" => "Tere tulemast värskenduse viisardisse."
        ],
        "overview" => [
            "title" => "Ülevaade",
            "message" => "Seal on 1 värskendus. | On: numbrite värskendusi.",
            "install_updates" => "Installige värskendused"
        ],
        "final" => [
            "title" => "Valmis",
            "finished" => "Rakenduse andmebaasi on edukalt värskendatud.",
            "exit" => "Väljumiseks klõpsake siin"
        ],
        "log" => [
            "success_message" => "NativeCode Installer värskendati edukalt "
        ]
    ]
];
