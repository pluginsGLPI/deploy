<?php

/**
 * -------------------------------------------------------------------------
 * deploy plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022 by the deploy plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
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
