<?php

include('../../../inc/includes.php');

use GlpiPlugin\Mostservicedesk\Department;

Session::checkRight('config', READ);
Html::header('MOST Service Desk', $_SERVER['PHP_SELF'], 'config', 'plugins');

echo '<div class="center"><h2>Departamentos de tickets</h2>';
if (Session::haveRight('config', UPDATE)) {
    echo '<p><a class="btn btn-primary" href="department.form.php">Adicionar departamento</a></p>';
}
echo '</div>';

Search::show(Department::class);
Html::footer();
