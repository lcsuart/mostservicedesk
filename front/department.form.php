<?php

use GlpiPlugin\Mostservicedesk\Department;

Session::checkRight('config', READ);
$department = new Department();

if (isset($_POST['add'])) {
    Session::checkRight('config', UPDATE);
    $newId = $department->add($_POST);
    Html::redirect('department.form.php?id=' . $newId);
} elseif (isset($_POST['update'])) {
    Session::checkRight('config', UPDATE);
    $department->update($_POST);
    Html::back();
} elseif (isset($_POST['delete'])) {
    Session::checkRight('config', DELETE);
    $department->delete($_POST, true);
    Html::redirect('department.php');
}

$id = (int) ($_GET['id'] ?? 0);
Html::header('MOST Service Desk', $_SERVER['PHP_SELF'], 'config', 'plugins');
$department->display(['id' => $id]);
Html::footer();
