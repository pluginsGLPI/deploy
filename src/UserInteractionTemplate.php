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

namespace GlpiPlugin\Deploy;

use CommonDBTM;
use Migration;
use DBConnection;
use DisplayPreference;
use Dropdown;
use Entity;
use Glpi\Application\View\TemplateRenderer;

class UserInteractionTemplate extends CommonDBTM
{
    public static $rightname = 'entity';

    // Define time constant
    public const TIME_NEVER = 0;
    public const TIME_30_SEC = 30;
    public const TIME_45_SEC = 45;
    public const TIME_1_MIN = 60;
    public const TIME_2_MIN = 120;
    public const TIME_5_MIN = 300;
    public const TIME_10_MIN = 600;
    public const TIME_15_MIN = 900;
    public const TIME_20_MIN = 1200;
    public const TIME_25_MIN = 1500;
    public const TIME_30_MIN = 1800;
    public const TIME_45_MIN = 2700;
    public const TIME_60_MIN = 3600;
    public const TIME_2_HR = 7200;
    public const TIME_3_HR = 10800;
    public const TIME_4_HR = 14400;
    public const TIME_5_HR = 18000;
    public const TIME_6_HR = 21600;
    public const TIME_7_HR = 25200;
    public const TIME_8_HR = 28800;
    public const TIME_9_HR = 32400;
    public const TIME_10_HR = 36000;
    public const TIME_11_HR = 39600;
    public const TIME_12_HR = 43200;

    public static function getTypeName($nb = 0)
    {
        return _n('Alert template', 'Alert templates', $nb, 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-template';
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
            ->addStandardTab(UserInteractionTemplate_Behavior::getType(), $ong, $options)
            ->addStandardTab('Log', $ong, $options);

        return $ong;
    }


    public function rawSearchOptions()
    {

        $tab = parent::rawSearchOptions();

        $tab[] = [
            'id'                 => '2',
            'table'              => $this->getTable(),
            'field'              => 'id',
            'name'               => __('ID'),
            'massiveaction'      => false, // implicit field is id
            'datatype'           => 'number'
        ];

        $tab[] = [
            'id'                 => '19',
            'table'              => $this->getTable(),
            'field'              => 'date_mod',
            'name'               => __('Last update'),
            'datatype'           => 'datetime',
            'massiveaction'      => false
        ];

        $tab[] = [
            'id'                 => '80',
            'table'              => 'glpi_entities',
            'field'              => 'completename',
            'name'               => Entity::getTypeName(1),
            'datatype'           => 'dropdown'
        ];

        return $tab;
    }

    public static function install(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();
            $sign              = DBConnection::getDefaultPrimaryKeySignOption();

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int $sign NOT NULL AUTO_INCREMENT,
                `entities_id` int $sign NOT NULL DEFAULT '0',
                `is_recursive` tinyint NOT NULL DEFAULT '0',
                `name` varchar(255) DEFAULT NULL,
                `alert_type` varchar(50) DEFAULT NULL,
                `interaction_type` varchar(50) DEFAULT NULL,
                `icon` varchar(10) DEFAULT NULL,
                `timeout` int NOT NULL DEFAULT '0',
                `ok_action` varchar(50) DEFAULT NULL,
                `timeout_action` varchar(50) DEFAULT NULL,
                `no_user_action` varchar(50) DEFAULT NULL,
                `multi_users_action` varchar(50) DEFAULT NULL,
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->request($query);
        }

        // add display preferences
        $nb_display_pref = countElementsInTable(DisplayPreference::getTable(), [
            'itemtype' => self::getType()
        ]);
        if ($nb_display_pref == 0) {
            $migration->updateDisplayPrefs([self::class => [19, 80]]);
        }
    }

    public static function uninstall(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }

    public function showForm($ID, array $options = [])
    {
        $this->initForm($ID, $options);
        $list = [
            'alert_type' => [null => "--"] + Package_UserInteraction::getTypes(),
            'interaction_type'   => Package_UserInteraction::getInteractionTypes(),
            'icon'      => Package_UserInteraction::getIcons(),
            'timeout'   => self::getAllTimeout(),
        ];
        TemplateRenderer::getInstance()->display(
            '@deploy/userinteractiontemplate/userinteractiontemplate.html.twig',
            [
                'item' => $this,
                'list' => $list,
                'params' => $options,
            ]
        );

        return true;
    }

    public static function getDataLabel(string $type, string $value): string
    {
        if ($value === "") {
            return NOT_AVAILABLE;
        }

        switch ($type) {
            case 'alert_type':
                $values = Package_UserInteraction::getTypes();
                break;
            case 'interaction_type':
                $all = Package_UserInteraction::getInteractionTypes();
                break;
            case 'icon':
                $all = Package_UserInteraction::getIcons()();
                break;
            case 'timeout':
                $all = static::getAllTimeout();
                break;
        }

        if (!isset($all[$value])) {
            trigger_error(
                sprintf(
                    'Type %1$s does not exists!',
                    $value
                ),
                E_USER_WARNING
            );http://127.0.0.1/10.0bugfixes/front/knowbaseitem.php
            return NOT_AVAILABLE;
        }
        return $all[$value];
    }

    public static function getDataLabelDropdown(
        $type,
        $value = 0,
        $options = []
    ): string {
        $name = $type;
        switch ($type) {
            case 'alert_type':
                $values = Package_UserInteraction::getTypes();
                break;
            case 'interaction_type':
                $values = Package_UserInteraction::getInteractionTypes();
                break;
            case 'icon':
                $values = Package_UserInteraction::getIcons()();
                break;
            case 'timeout':
                $values = static::getAllTimeout();
                break;
        }

        return Dropdown::showFromArray(
            $name,
            $values,
            [
                'value'   => $value,
                'display' => false
            ]
        );
    }
    public static function getAllTimeout(): array
    {
        return [
            self::TIME_NEVER => __('Never', 'deploy'),
            self::TIME_30_SEC => __('30 seconds', 'deploy'),
            self::TIME_45_SEC => __('45 seconds', 'deploy'),
            self::TIME_1_MIN => __('1 minute', 'deploy'),
            self::TIME_2_MIN => __('2 minutes', 'deploy'),
            self::TIME_5_MIN => __('5 minutes', 'deploy'),
            self::TIME_10_MIN => __('10 minutes', 'deploy'),
            self::TIME_15_MIN => __('15 minutes', 'deploy'),
            self::TIME_20_MIN => __('20 minutes', 'deploy'),
            self::TIME_25_MIN => __('25 minutes', 'deploy'),
            self::TIME_30_MIN => __('30 minutes', 'deploy'),
            self::TIME_45_MIN => __('45 minutes', 'deploy'),
            self::TIME_60_MIN => __('1 hour', 'deploy'),
            self::TIME_2_HR => __('2 hours', 'deploy'),
            self::TIME_3_HR => __('3 hours', 'deploy'),
            self::TIME_4_HR => __('4 hours', 'deploy'),
            self::TIME_5_HR => __('5 hours', 'deploy'),
            self::TIME_6_HR => __('6 hours', 'deploy'),
            self::TIME_7_HR => __('7 hours', 'deploy'),
            self::TIME_8_HR => __('8 hours', 'deploy'),
            self::TIME_9_HR => __('9 hours', 'deploy'),
            self::TIME_10_HR => __('10 hours', 'deploy'),
            self::TIME_11_HR => __('11 hours', 'deploy'),
            self::TIME_12_HR => __('12 hours', 'deploy'),
        ];
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []): string
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'alert_type':
            case 'interaction_type':
            case 'icon':
            case 'timeout':
                return self::getDataLabelDropdown($field, $values[$field], $options);
        }
        return parent::getSpecificValueToSelect($field, $name, $values, $options);
    }

    public static function getSpecificValueToDisplay($field, $values, array $options = [])
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }

        switch ($field) {
            case 'alert_type':
            case 'interaction_type':
            case 'icon':
            case 'timeout':
                return self::getDataLabel($field, $values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
}
