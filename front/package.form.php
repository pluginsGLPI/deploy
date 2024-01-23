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
} else if (isset($_POST["add_userinteraction"])) {
    unset($_POST['id']);
    $userinteraction = new Package_UserInteraction();
    $userinteraction->add($_POST);
    Html::back();
} else if (isset($_POST["edit_userinteraction"])) {
    $userinteraction = new Package_UserInteraction();
    $userinteraction->update($_POST);
    Html::back();
} else if (isset($_POST["delete_userinteraction"])) {
    $userinteraction = new Package_UserInteraction();
    $userinteraction->delete($_POST);
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
