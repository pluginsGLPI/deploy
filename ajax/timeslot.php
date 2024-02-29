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

use Glpi\Application\View\TemplateRenderer;

include("../../../inc/includes.php");

\Session::checkLoginUser();
$trange = new TimeslotRange();
$id = $_POST['plugin_deploy_timeslots_id'];
TimeslotRange::cleanOldData($_POST);
$_POST = TimeslotRange::cleanInput($_POST);
foreach ($_POST as $timeslot) {
    foreach ($timeslot as $range) {
        if ((bool)$range['is_enable'] == true) {
            $trange->add($range);
        }
    }
}
$timeslots_data = TimeslotRange::getForTimeslot(Timeslot::getById($id));
echo TemplateRenderer::getInstance()->render('@deploy/timeslot/timeslotrange.html.twig', [
    'rand'           => mt_rand(),
    'timeslot_id'    => $id,
    'days_list'      => TimeslotRange ::getDayList(),
    'timeslots_data' => $timeslots_data
]);
