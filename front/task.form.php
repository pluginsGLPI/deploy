<?php

/**
 * -------------------------------------------------------------------------
 * Deploy plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Deploy.
 *
 * Deploy is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Deploy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022-2024 by Deploy plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$task = new Task();

if (isset($_POST["add"])) {
    $task->check(-1, CREATE, $_POST);
    if ($task->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($task->getLinkURL());
        }
    }
    Html::back();
} else if (isset($_POST["delete"])) {
    $task->check($_POST['id'], DELETE);
    $task->delete($_POST);
    $task->redirectToList();
} else if (isset($_POST["restore"])) {
    $task->check($_POST['id'], DELETE);
    $task->restore($_POST);
    $task->redirectToList();
} else if (isset($_POST["purge"])) {
    $task->check($_POST['id'], PURGE);
    $task->delete($_POST, 1);
    $task->redirectToList();
} else if (isset($_POST["update"])) {
    $task->check($_POST['id'], UPDATE);
    $task->update($_POST);
    Html::back();
} else if (isset($_POST["add_package"])) {
    if ($_POST['plugin_deploy_packages_id'] > 0) {
        $task->check($_POST['plugin_deploy_tasks_id'], UPDATE);
        $task_package = new Task_Package();
        $task_package->add([
            'plugin_deploy_tasks_id'    => (int) $_POST['plugin_deploy_tasks_id'],
            'plugin_deploy_packages_id' => (int) $_POST['plugin_deploy_packages_id'],
        ]);
    }
    Html::back();
} else if (isset($_POST["add_target"])) {
    if ($_POST['items_id'] > 0) {
        $task->check($_POST['plugin_deploy_tasks_id'], UPDATE);
        $task_target = new Task_Target();
        $result = $task_target->add([
            'plugin_deploy_tasks_id' => (int) $_POST['plugin_deploy_tasks_id'],
            'itemtype' => $_POST['itemtype'],
            'items_id' => (int) $_POST['items_id'],
        ]);
    }
    Html::back();
} else {
    Html::header(
        Task::getTypeName(Session::getPluralNumber()),
        '',
        'tools',
        'GlpiPlugin\Deploy\menu',
        'task'
    );

    //show question form to add
    $task->display([
        'id' => (int) $_GET["id"],
    ]);
    Html::footer();
}
