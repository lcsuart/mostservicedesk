<?php

include('../../../inc/includes.php');

use GlpiPlugin\Mostservicedesk\DepartmentUser;

Session::checkRight('config', UPDATE);
$relation = new DepartmentUser();

if (isset($_POST['add'])) {
    $relation->add([
        'plugin_mostservicedesk_departments_id' => (int) $_POST['plugin_mostservicedesk_departments_id'],
        'users_id' => (int) $_POST['users_id'],
        'is_active' => 1,
    ]);
    Html::redirect('department.form.php?id=' . (int) $_POST['plugin_mostservicedesk_departments_id']);
}

if (isset($_POST['delete'])) {
    $relation->delete(['id' => (int) $_POST['id']], true);
    Html::redirect('department.form.php?id=' . (int) $_POST['department_id']);
}

Html::back();
