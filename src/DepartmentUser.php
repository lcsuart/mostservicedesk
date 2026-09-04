<?php

namespace GlpiPlugin\Mostservicedesk;

use CommonDBTM;
use Html;
use Session;
use User;

class DepartmentUser extends CommonDBTM
{
    public static $rightname = 'config';

    public static function showForDepartment(int $departmentId): void
    {
        global $DB, $CFG_GLPI;

        if (!Session::haveRight('config', READ)) {
            return;
        }

        echo '<div class="center"><h3>Logins autorizados</h3>';

        if (Session::haveRight('config', UPDATE)) {
            Html::openForm($CFG_GLPI['root_doc'] . '/plugins/mostservicedesk/front/departmentuser.form.php');
            echo Html::hidden('plugin_mostservicedesk_departments_id', ['value' => $departmentId]);
            User::dropdown(['name' => 'users_id', 'right' => 'all']);
            echo Html::submit('Adicionar login', ['name' => 'add', 'class' => 'btn btn-primary']);
            Html::closeForm();
        }

        echo '<table class="tab_cadre_fixe"><thead><tr><th>Login</th><th>Nome</th><th>Ação</th></tr></thead><tbody>';
        $iterator = $DB->request([
            'SELECT' => [
                'du.id AS relation_id',
                'u.name AS login',
                'u.realname',
                'u.firstname',
            ],
            'FROM' => 'glpi_plugin_mostservicedesk_departments_users AS du',
            'INNER JOIN' => [
                'glpi_users AS u' => ['ON' => ['du' => 'users_id', 'u' => 'id']],
            ],
            'WHERE' => [
                'du.plugin_mostservicedesk_departments_id' => $departmentId,
                'du.is_active' => 1,
            ],
            'ORDER' => ['u.name'],
        ]);

        foreach ($iterator as $row) {
            $fullName = trim(($row['firstname'] ?? '') . ' ' . ($row['realname'] ?? ''));
            echo '<tr><td>' . Html::clean($row['login']) . '</td><td>' . Html::clean($fullName) . '</td><td>';
            if (Session::haveRight('config', UPDATE)) {
                Html::openForm($CFG_GLPI['root_doc'] . '/plugins/mostservicedesk/front/departmentuser.form.php');
                echo Html::hidden('id', ['value' => (int) $row['relation_id']]);
                echo Html::hidden('department_id', ['value' => $departmentId]);
                echo Html::submit('Remover', ['name' => 'delete', 'class' => 'btn btn-danger']);
                Html::closeForm();
            }
            echo '</td></tr>';
        }
        echo '</tbody></table></div>';
    }
}
