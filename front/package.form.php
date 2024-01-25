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

$package = new Package();

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
    $file = new Package_File();
    $file->add($_POST);
    Html::back();
} else if (isset($_POST["edit_file"])) {
    $file = new Package_File();
    $file->update($_POST);
    Html::back();
} else if (isset($_POST["delete_file"])) {
    $file = new Package_File();
    $file->delete($_POST);
    Html::back();
} else if (isset($_GET["download_file"])) {
    $file_id = (int)($_GET["file_id"] ?? 0);
    $file = new Package_File();
    $file->downloadFile($file_id);
    Html::back();
} else if (isset($_POST["add_check"])) {
    unset($_POST['id']);
    $check = new Package_Check();
    $check->add($_POST);
    Html::back();
} else if (isset($_POST["edit_check"])) {
    $check = new Package_Check();
    $check->update($_POST);
    Html::back();
} else if (isset($_POST["delete_check"])) {
    $check = new Package_Check();
    $check->delete($_POST);
    Html::back();
} else if (isset($_POST["add_action"])) {
    unset($_POST['id']);
    $action = new Package_Action();
    $action->add($_POST);
    Html::back();
} else if (isset($_POST["edit_action"])) {
    $action = new Package_Action();
    $action->update($_POST);
    Html::back();
} else if (isset($_POST["delete_action"])) {
    $action = new Package_Action();
    $action->delete($_POST);
    Html::back();
} else {
    Html::requireJs('sortable');
    Html::header(
        Package::getTypeName(Session::getPluralNumber()),
        '',
        'tools',
        'glpiplugin\deploy\menu',
        'package'
    );

    //show question form to add
    $package->display([
        'id' => (int) $_GET["id"],
    ]);
    Html::footer();
}
