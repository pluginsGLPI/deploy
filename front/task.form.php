<?php
/*
 -------------------------------------------------------------------------
 Deploy plugin for GLPI
 Copyright (C) 2022 by the Deploy Development Team.

 https://github.com/pluginsGLPI/deploy
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Deploy.

 Deploy is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Deploy is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

include ('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

$task = new PluginDeployTask();

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

} else {
    Html::header(
        PluginDeployTask::getTypeName(Session::getPluralNumber()),
        '',
        'tools',
        'plugindeploymenu',
        'task'
    );

   //show question form to add
   $task->display([
      'id' => (int) $_GET["id"],
   ]);
   Html::footer();
}
