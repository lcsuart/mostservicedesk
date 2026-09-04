<?php

namespace GlpiPlugin\Mostservicedesk;

use CommonDBTM;

class TicketDepartment extends CommonDBTM
{
    public static $rightname = 'ticket';

    public static function getTable($classname = null): string
    {
        return 'glpi_plugin_mostservicedesk_tickets';
    }
}
