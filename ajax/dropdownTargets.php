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

// there is no called form here, and as we require another ajax file,
// we have a double define
define('GLPI_USE_CSRF_CHECK', 0);
include ("../../../inc/includes.php");

switch ($_POST["idtable"]) {
    case 'Computer':
        $_POST['condition'] = [
            'is_deleted' => 0
        ];
        break;
    case 'Group':
        $_POST['condition'] = [
            'is_itemgroup' => 1
        ];
        break;
    case 'SavedSearch':
        $_POST['condition'] = [
            'itemtype'   => 'Computer',
            'is_private' => 0,
        ];
        break;
}

chdir("../../../ajax");
require "dropdownAllItems.php";
