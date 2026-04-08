<?php

return [
    'title' => 'Instalador NativeCode',
    'next' => 'Próxima etapa',
    'back' => 'Anterior',
    'finish' => 'Instalar',
    'forms' => [
        'errorTitle' => 'Ocorreram os seguintes erros:'
    ],
    'welcome' => [
        'templateTitle' => 'Bem-vindo',
        'title' => 'Instalador NativeCode',
        'message' => 'Fácil instalação e assistente de configuração.',
        'next' => 'Verifique os requisitos'
    ],
    'requirements' => [
        'templateTitle' => 'Etapa 1 | ',
        'title' => 'Requisitos do servidor',
        'next' => 'Verifique as permissões'
    ],
    'permissions' => [
        'templateTitle' => 'Etapa 2 | ',
        'title' => 'Permissões',
        'next' => 'Configurar ambiente'
    ],
    'environment' => [
        'menu' => [
            'templateTitle' => 'Etapa 3 | ',
            'title' => 'Instalação',
            'desc' => 'Selecione como você deseja configurar os aplicativos <code>.env</code> arquivo.',
            'wizard-button' => 'Configuração do assistente de formulário',
            'classic-button' => 'Editor de texto clássico'
        ],
        'wizard' => [
            'templateTitle' => 'Etapa 3 | ',
            'title' => 'Guiado <code>.env</code> Mago',
            'tabs' => [
                'environment' => 'Ambiente',
                'database' => 'Banco de dados',
                'application' => 'Aplicativo'
            ],
            'form' => [
                'name_required' => 'É necessário um nome de ambiente.',
                'app_name_label' => 'Nome do aplicativo',
                'app_name_placeholder' => 'Nome do aplicativo',
                'app_environment_label' => 'Ambiente de aplicativo',
                'app_environment_label_local' => 'Local',
                'app_environment_label_developement' => 'Desenvolvimento',
                'app_environment_label_qa' => 'Controle de qualidade',
                'app_environment_label_production' => 'Produção',
                'app_debug_label' => 'Depuração de aplicativo',
                'app_debug_label_true' => 'Verdadeiro',
                'app_debug_label_false' => 'Falso',
                'app_log_level_label' => 'Nível de registro do aplicativo',
                'app_log_level_label_debug' => 'depurar',
                'app_log_level_label_info' => 'informações',
                'app_log_level_label_notice' => 'perceber',
                'app_log_level_label_warning' => 'aviso',
                'app_log_level_label_error' => 'erro',
                'app_log_level_label_critical' => 'crítico',
                'app_log_level_label_alert' => 'alerta',
                'app_log_level_label_emergency' => 'emergência',
                'app_url_label' => 'URL do aplicativo',
                'app_url_placeholder' => 'URL do aplicativo',
                'db_connection_failed' => 'Não foi possível conectar-se ao banco de dados.',
                'db_connection_label' => 'Conexão de banco de dados',
                'db_connection_label_mysql' => 'mysql',
                'db_connection_label_sqlite' => 'sqlite',
                'db_connection_label_pgsql' => 'pgsql',
                'db_connection_label_sqlsrv' => 'sqlsrv',
                'db_host_label' => 'Host de banco de dados',
                'db_host_placeholder' => 'Host de banco de dados',
                'db_port_label' => 'Porta do banco de dados',
                'db_port_placeholder' => 'Porta do banco de dados',
                'db_name_label' => 'Nome do banco de dados',
                'db_name_placeholder' => 'Nome do banco de dados',
                'db_username_label' => 'Nome de usuário do banco de dados',
                'db_username_placeholder' => 'Nome de usuário do banco de dados',
                'db_password_label' => 'Senha do banco de dados',
                'db_password_placeholder' => 'Senha do banco de dados',
                'app_tabs' => [
                    'more_info' => 'Mais informações',
                    'broadcasting_title' => 'Transmissão, cache, sessão e fila',
                    'broadcasting_label' => 'Driver de transmissão',
                    'broadcasting_placeholder' => 'Driver de transmissão',
                    'cache_label' => 'Driver de cache',
                    'cache_placeholder' => 'Driver de cache',
                    'session_label' => 'Driver de sessão',
                    'session_placeholder' => 'Driver de sessão',
                    'queue_label' => 'Driver de fila',
                    'queue_placeholder' => 'Driver de fila',
                    'redis_label' => 'Driver Redis',
                    'redis_host' => 'Anfitrião Redis',
                    'redis_password' => 'Senha Redis',
                    'redis_port' => 'Porto Redis',
                    'mail_label' => 'Correspondência',
                    'mail_driver_label' => 'Driver de correio',
                    'mail_driver_placeholder' => 'Driver de correio',
                    'mail_host_label' => 'Host de correio',
                    'mail_host_placeholder' => 'Host de correio',
                    'mail_port_label' => 'Porto de correio',
                    'mail_port_placeholder' => 'Porto de correio',
                    'mail_username_label' => 'Nome de usuário de e-mail',
                    'mail_username_placeholder' => 'Nome de usuário de e-mail',
                    'mail_password_label' => 'Senha de correio',
                    'mail_password_placeholder' => 'Senha de correio',
                    'mail_encryption_label' => 'Criptografia de correio',
                    'mail_encryption_placeholder' => 'Criptografia de correio',
                    'pusher_label' => 'Empurrador',
                    'pusher_app_id_label' => 'ID do aplicativo Pusher',
                    'pusher_app_id_palceholder' => 'ID do aplicativo Pusher',
                    'pusher_app_key_label' => 'Chave do aplicativo Pusher',
                    'pusher_app_key_palceholder' => 'Chave do aplicativo Pusher',
                    'pusher_app_secret_label' => 'Segredo do aplicativo Pusher',
                    'pusher_app_secret_palceholder' => 'Segredo do aplicativo Pusher'
                ],
                'buttons' => [
                    'setup_database' => 'Configurar banco de dados',
                    'setup_application' => 'Aplicativo de configuração',
                    'install' => 'Instalar'
                ],
                'app_environment_label_one' => 'Ambiente de aplicativo',
                'app_environment_label_many' => 'Outro',
                'app_environment_label_other' => 'Outro',
                'app_environment_placeholder_one' => 'Entre no seu ambiente...',
                'app_environment_placeholder_many' => 'Entre no seu ambiente...',
                'app_environment_placeholder_other' => 'Entre no seu ambiente...'
            ]
        ],
        'classic' => [
            'templateTitle' => 'Etapa 3 | ',
            'title' => 'Editor de ambiente clássico',
            'save' => 'Salvar .env',
            'back' => 'Usar assistente de formulário',
            'install' => 'Salvar e instalar'
        ],
        'success' => 'As configurações do seu arquivo .env foram salvas.',
        'errors' => 'Não foi possível salvar o arquivo .env. Crie-o manualmente.'
    ],
    'install' => 'Instalar',
    'installed' => [
        'success_log_message' => 'Instalador NativeCode INSTALADO com sucesso em '
    ],
    'final' => [
        'title' => 'Instalação concluída',
        'templateTitle' => 'Instalação concluída',
        'finished' => 'O aplicativo foi instalado com sucesso.',
        'migration' => 'Saída do console de migração e seed:',
        'console' => 'Saída do console do aplicativo:',
        'log' => 'Entrada do registro de instalação:',
        'env' => 'Arquivo .env final:',
        'exit' => 'Clique aqui para sair'
    ],
    'updater' => [
        'title' => 'Atualizador Laravel',
        'welcome' => [
            'title' => 'Bem-vindo ao atualizador',
            'message' => 'Bem-vindo ao assistente de atualização.'
        ],
        'overview' => [
            'title' => 'Visão geral',
            'message' => 'Há 1 atualização.|Existem :number atualizações.',
            'install_updates' => 'Instalar atualizações'
        ],
        'final' => [
            'title' => 'Finalizado',
            'finished' => 'O banco de dados do aplicativo foi atualizado com sucesso.',
            'exit' => 'Clique aqui para sair'
        ],
        'log' => [
            'success_message' => 'Instalador NativeCode ATUALIZADO com sucesso em '
        ]
    ]
];
