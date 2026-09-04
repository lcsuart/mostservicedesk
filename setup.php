<?php

define('PLUGIN_MOSTSERVICEDESK_VERSION', '0.1.0');
define('PLUGIN_MOSTSERVICEDESK_MIN_GLPI', '11.0.0');
define('PLUGIN_MOSTSERVICEDESK_MAX_GLPI', '11.1.0');

function plugin_init_mostservicedesk(): void
{
    global $PLUGIN_HOOKS;

    $PLUGIN_HOOKS['csrf_compliant']['mostservicedesk'] = true;
    $PLUGIN_HOOKS['config_page']['mostservicedesk'] = 'front/department.php';

    Plugin::registerClass('GlpiPlugin\\Mostservicedesk\\Department');
    Plugin::registerClass('GlpiPlugin\\Mostservicedesk\\DepartmentUser');
    Plugin::registerClass('GlpiPlugin\\Mostservicedesk\\TicketDepartment');
}

function plugin_version_mostservicedesk(): array
{
    return [
        'name'         => 'Gestão de Tickets - MOST Service Desk',
        'version'      => PLUGIN_MOSTSERVICEDESK_VERSION,
        'author'       => 'MOST Tecnologia e Inovação',
        'license'      => 'GPLv3+',
        'homepage'     => '',
        'requirements' => [
            'glpi' => [
                'min' => PLUGIN_MOSTSERVICEDESK_MIN_GLPI,
                'max' => PLUGIN_MOSTSERVICEDESK_MAX_GLPI,
            ],
            'php' => [
                'min' => '8.2',
            ],
        ],
    ];
}

function plugin_mostservicedesk_check_prerequisites(): bool
{
    return version_compare(GLPI_VERSION, PLUGIN_MOSTSERVICEDESK_MIN_GLPI, '>=')
        && version_compare(GLPI_VERSION, PLUGIN_MOSTSERVICEDESK_MAX_GLPI, '<');
}

function plugin_mostservicedesk_check_config(bool $verbose = false): bool
{
    return true;
}
