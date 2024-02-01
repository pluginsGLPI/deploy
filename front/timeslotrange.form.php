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

$trange = new TimeslotRange();

if (isset($_POST['timeslot']) && !empty($_POST['timeslot'])) {
    $_POST['timeslot'] = json_decode($_POST['timeslot'], true);
    TimeslotRange::cleanOldData($_POST);
    $_POST = TimeslotRange::cleanInput($_POST);
    foreach ($_POST as $timeslot) {
        foreach ($timeslot as $range) {
            if ($range['is_enable'] == true) {
                $trange->add($range);
            }
        }
    }
    Session::addMessageAfterRedirect(__('Range options saved', 'deploy'), true, INFO);
}
Html::back();
