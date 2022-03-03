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

include ("../../../inc/includes.php");

Session::checkLoginUser();

switch (($_POST['action'] ?? "")) {
    case "add_check":
        PluginDeployPackage_Check::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_check":
        PluginDeployPackage_Check::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "add_file":
        PluginDeployPackage_File::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_file":
        PluginDeployPackage_File::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "add_action":
        PluginDeployPackage_Action::showAdd((int) ($_POST['plugin_deploy_packages_id'] ?? 0));
        break;
    case "edit_action":
        PluginDeployPackage_Action::showEdit((int) ($_POST['id'] ?? 0));
        break;
    case "move_subitem":
        PluginDeployPackage::moveSubitem(
            $_POST['subitem_itemtype'],
            (int) $_POST['subitem_id'],
            (int) $_POST['ref_id'],
            $_POST['sort_action']
        );
        break;
}
