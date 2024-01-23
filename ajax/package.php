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

include ("../../../inc/includes.php");

\Session::checkLoginUser();

switch (($_POST['action'] ?? "")) {
    case "add_check":
        Package_Check::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_check":
        Package_Check::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "add_file":
        Package_File::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_file":
        Package_File::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "add_action":
        Package_Action::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_action":
        Package_Action::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "add_userinteraction":
        Package_UserInteraction::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_userinteraction":
        Package_UserInteraction::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "move_subitem":
        Package::moveSubitem(
            $_POST['subitem_itemtype'],
            (int) $_POST['subitem_id'],
            (int) $_POST['ref_id'],
            $_POST['sort_action']
        );
        break;
}
