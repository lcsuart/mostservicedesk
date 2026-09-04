<?php

use GlpiPlugin\Mostservicedesk\Department;

/**
 * Adiciona a seção do plugin em Configuração > Listas suspensas.
 */
function plugin_mostservicedesk_getDropdown(): array
{
    return [
        Department::class => 'Gestão de Tickets - Departamentos',
    ];
}

function plugin_mostservicedesk_install(): bool
{
    global $DB;

    $migration = new Migration(100);

    if (!$DB->tableExists('glpi_plugin_mostservicedesk_departments')) {
        $DB->doQuery("CREATE TABLE `glpi_plugin_mostservicedesk_departments` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `name` varchar(255) NOT NULL,
            `is_active` tinyint NOT NULL DEFAULT 1,
            `tickets_enabled` tinyint NOT NULL DEFAULT 1,
            `whatsapp_enabled` tinyint NOT NULL DEFAULT 1,
            `allow_audio` tinyint NOT NULL DEFAULT 1,
            `allow_video` tinyint NOT NULL DEFAULT 1,
            `allow_documents` tinyint NOT NULL DEFAULT 1,
            `default_category_id` int unsigned NOT NULL DEFAULT 0,
            `welcome_message` text,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unicity_name` (`name`),
            KEY `is_active` (`is_active`),
            KEY `tickets_enabled` (`tickets_enabled`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
    }

    if (!$DB->tableExists('glpi_plugin_mostservicedesk_departments_users')) {
        $DB->doQuery("CREATE TABLE `glpi_plugin_mostservicedesk_departments_users` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `plugin_mostservicedesk_departments_id` int unsigned NOT NULL,
            `users_id` int unsigned NOT NULL,
            `is_active` tinyint NOT NULL DEFAULT 1,
            PRIMARY KEY (`id`),
            UNIQUE KEY `department_user` (`plugin_mostservicedesk_departments_id`, `users_id`),
            KEY `users_id` (`users_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
    }

    if (!$DB->tableExists('glpi_plugin_mostservicedesk_tickets')) {
        $DB->doQuery("CREATE TABLE `glpi_plugin_mostservicedesk_tickets` (
            `id` int unsigned NOT NULL AUTO_INCREMENT,
            `tickets_id` int unsigned NOT NULL,
            `plugin_mostservicedesk_departments_id` int unsigned NOT NULL,
            `date_creation` timestamp NULL DEFAULT NULL,
            `date_mod` timestamp NULL DEFAULT NULL,
            PRIMARY KEY (`id`),
            UNIQUE KEY `tickets_id` (`tickets_id`),
            KEY `department_id` (`plugin_mostservicedesk_departments_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC");
    }

    $migration->executeMigration();
    return true;
}

function plugin_mostservicedesk_uninstall(): bool
{
    // Dados preservados intencionalmente nesta fase inicial.
    return true;
}
