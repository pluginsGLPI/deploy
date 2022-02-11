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

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$package = new PluginDeployPackage();

if (isset($_POST["add"])) {
    $package->check(-1, CREATE, $_POST);
    if ($package->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($package->getLinkURL());
        }
    }
    Html::back();
} else if (isset($_POST["delete"])) {
    $package->check($_POST['id'], DELETE);
    $package->delete($_POST);
    $package->redirectToList();
} else if (isset($_POST["restore"])) {
    $package->check($_POST['id'], DELETE);
    $package->restore($_POST);
    $package->redirectToList();
} else if (isset($_POST["purge"])) {
    $package->check($_POST['id'], PURGE);
    $package->delete($_POST, 1);
    $package->redirectToList();
} else if (isset($_POST["update"])) {
    $package->check($_POST['id'], UPDATE);
    $package->update($_POST);
    Html::back();
} else if (isset($_POST["add_file"])) {
    unset($_POST['id']);
    $file = new PluginDeployFile();
    $file->add($_POST);
    Html::back();
} else if (isset($_POST["edit_file"])) {
    $file = new PluginDeployFile();
    $file->update($_POST);
    Html::back();
} else if (isset($_POST["delete_file"])) {
    $file = new PluginDeployFile();
    $file->delete($_POST);
    Html::back();
} else {
    Html::header(
        PluginDeployTask::getTypeName(Session::getPluralNumber()),
        '',
        'tools',
        'plugindeploymenu',
        'package'
    );

    //show question form to add
    $package->display([
        'id' => (int) $_GET["id"],
    ]);
    Html::footer();
}
