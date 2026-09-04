<?php

namespace GlpiPlugin\Mostservicedesk;

use CommonDBTM;
use Html;
use Session;
use User;

class DepartmentUser extends CommonDBTM
{
    public static $rightname = 'config';

    public static function canCreate(): bool
    {
        return static::canUpdate();
    }

    public static function canPurge(): bool
    {
        return static::canUpdate();
    }

    public static function showForDepartment(int $departmentId): void
    {
        global $DB, $CFG_GLPI;

        if (!Session::haveRight('config', READ)) {
            return;
        }

        echo '<div class="center"><h3>Logins autorizados</h3>';

        if (Session::haveRight('config', UPDATE)) {
            $action = $CFG_GLPI['root_doc'] . '/plugins/mostservicedesk/front/departmentuser.form.php';
            echo '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">';
            echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
            echo Html::hidden('plugin_mostservicedesk_departments_id', ['value' => $departmentId]);
            User::dropdown(['name' => 'users_id', 'right' => 'all']);
            echo Html::submit('Adicionar login', ['name' => 'add', 'class' => 'btn btn-primary']);
            echo '</form>';
        }

        echo '<table class="tab_cadre_fixe"><thead><tr><th>Login</th><th>Nome</th><th>Ação</th></tr></thead><tbody>';
        $iterator = $DB->request([
            'SELECT' => [
                'glpi_plugin_mostservicedesk_departments_users.id AS relation_id',
                'glpi_users.name AS login',
                'glpi_users.realname',
                'glpi_users.firstname',
            ],
            'FROM' => 'glpi_plugin_mostservicedesk_departments_users',
            'INNER JOIN' => [
                'glpi_users' => [
                    'ON' => [
                        'glpi_plugin_mostservicedesk_departments_users' => 'users_id',
                        'glpi_users' => 'id',
                    ],
                ],
            ],
            'WHERE' => [
                'glpi_plugin_mostservicedesk_departments_users.plugin_mostservicedesk_departments_id' => $departmentId,
                'glpi_plugin_mostservicedesk_departments_users.is_active' => 1,
            ],
            'ORDER' => ['glpi_users.name'],
        ]);

        foreach ($iterator as $row) {
            $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['realname'] ?? ''));
            echo '<tr><td>' . Html::clean($row['login']) . '</td><td>' . Html::clean($fullName) . '</td><td>';
            if (Session::haveRight('config', UPDATE)) {
                $action = $CFG_GLPI['root_doc'] . '/plugins/mostservicedesk/front/departmentuser.form.php';
                echo '<form method="post" action="' . htmlspecialchars($action, ENT_QUOTES, 'UTF-8') . '">';
                echo Html::hidden('_glpi_csrf_token', ['value' => Session::getNewCSRFToken()]);
                echo Html::hidden('id', ['value' => (int) $row['relation_id']]);
                echo Html::hidden('department_id', ['value' => $departmentId]);
                echo Html::submit('Remover', ['name' => 'delete', 'class' => 'btn btn-danger']);
                echo '</form>';
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
