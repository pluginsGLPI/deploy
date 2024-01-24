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
