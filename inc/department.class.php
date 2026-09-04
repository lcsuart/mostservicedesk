<?php

/**
 * Legacy itemtype alias required by the GLPI REST v1 URL resolver.
 * The application continues to use the namespaced implementation.
 */
class PluginMostservicedeskDepartment extends \GlpiPlugin\Mostservicedesk\Department
{
    /**
     * REST consumers only need a ticket permission to list the departments
     * available during ticket creation. Administrative CRUD remains in the
     * namespaced class and continues to require the config permission.
     */
    public static $rightname = 'ticket';
}
