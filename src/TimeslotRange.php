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

use CommonDBTM;
use CommonGLPI;
use DBConnection;
use Glpi\Application\View\TemplateRenderer;
use Migration;

class TimeslotRange extends CommonDBTM
{
    public static function getTypeName($nb = 0)
    {
        return __('Range', 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-calendar-event';
    }

    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == Timeslot::class) {
            return self::createTabEntry(self::getTypeName(1), 1);
        }
        return '';
    }

    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Timeslot::class) {
            self::showForTimeslot($item);
        }
        return true;
    }

    public static function getForTimeslot(Timeslot $timeslot)
    {
        $timeslots = new self();
        $timeslots = $timeslots->find([
            'plugin_deploy_timeslots_id' => $timeslot->fields['id'],
        ]);
        $timeslots_data = [];
        foreach ($timeslots as $timeslot) {
            $timeslots_data[$timeslot['weekday']][] = [
                'checked' => 'checked',
                'starttime' => intval(substr($timeslot['time_start'], 0, 2)),
                'endtime' => intval(substr($timeslot['time_end'], 0, 2)),
            ];
        }
        for ($i = 1; $i <= 7; $i++) {
            if (!isset($timeslots_data[$i])) {
                $timeslots_data[$i] = [
                    [
                        'checked' => '',
                        'starttime' => 8,
                        'endtime' => 12,
                    ],
                    [
                        'checked' => '',
                        'starttime' => 14,
                        'endtime' => 18,
                    ]
                ];
            }
        }
        return $timeslots_data;
    }

    public static function showForTimeslot(Timeslot $timeslot)
    {
        $timeslots_data = self::getForTimeslot($timeslot);
        TemplateRenderer::getInstance()->display('@deploy/timeslot/timeslotrange.html.twig', [
            'rand'         => mt_rand(),
            'timeslot_id'   => $timeslot->fields['id'],
            'days_list'     => self::getDayList(),
            'timeslots_data' => $timeslots_data ?? '-1'
        ]);
    }

    public static function getDayList()
    {
        return [
            1 => __('Monday'),
            2 => __('Tuesday'),
            3 => __('Wednesday'),
            4 => __('Thursday'),
            5 => __('Friday'),
            6 => __('Saturday'),
            7 => __('Sunday'),
        ];
    }

    public static function cleanOldData(array $input)
    {
        $timeslot = new self();
        $olddata = $timeslot->find(
            [
                'plugin_deploy_timeslots_id' => $input['plugin_deploy_timeslots_id'],
            ]
        );
        foreach ($olddata as $data) {
            $timeslot->delete(
                [
                    'id' => $data['id']
                ]
            );
        }
    }

    public static function cleanInput(array $input)
    {
        $output = [];
        foreach ($input['timeslot'] as $key => $value) {
            foreach ($value as $k => $v) {
                if ($k == 'is_enable') {
                    continue;
                }
                $start_time = sprintf('%02d:00:00', $v['starttime']);
                $end_time = sprintf('%02d:00:00', $v['endtime']);
                $output[$key][$k] = [
                    'plugin_deploy_timeslots_id' => $input['plugin_deploy_timeslots_id'],
                    'weekday' => $key,
                    'time_start' => $start_time,
                    'time_end' => $end_time,
                    'is_enable' => $value['is_enable'],
                ];
            }
        }

        return $output;
    }

    public static function install(Migration $migration)
    {
        /** @var object $DB */
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE {$table} (
                `id` int unsigned NOT NULL AUTO_INCREMENT,
                `plugin_deploy_timeslots_id` int unsigned NOT NULL DEFAULT '0',
                `weekday` tinyint NOT NULL DEFAULT '1',
                `time_start`  time NULL DEFAULT NULL,
                `time_end`  time NULL DEFAULT NULL,
                `entities_id` int unsigned NOT NULL DEFAULT '0',
                `is_active` tinyint(1) NOT NULL DEFAULT '1',
                `is_recursive` tinyint(1) NOT NULL DEFAULT '0',
                `date_mod` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `entities_id` (`entities_id`),
                KEY `is_active` (`is_active`),
                KEY `is_recursive` (`is_recursive`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->request($query);
        }
    }

    public static function uninstall(Migration $migration)
    {
        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }
}
