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

    // Define button interaction type
    public const INTERACTION_OK = "ok";
    public const INTERACTION_OK_ASYNC = "ok_async";
    public const INTERACTION_OK_CANCEL = "okcancel";
    public const INTERACTION_YES_NO = "yesno";
    public const INTERACTION_ABORT_RETRY_IGNORE = "abortretryignore";
    public const INTERACTION_RETRY_CANCEL = "retrycancel";
    public const INTERACTION_CANCEL_TRY_CONTINUE = "canceltrycontinue";
    public const INTERACTION_YES_NO_CANCEL = "yesnocancel";

    // Define platform constant
    public const PLATFORM_WINDOWS_SYSTEM_ALERT = "win32";

    // Define icon constant
    public const ICON_NONE = "none";
    public const ICON_INFO = "info";
    public const ICON_WARNING = "warning";
    public const ICON_ERROR = "error";
    public const ICON_QUESTION = "question";

    // Define time constant
    public const TIME_30_SEC = 30;
    public const TIME_35_SEC = 35;
    public const TIME_40_SEC = 40;
    public const TIME_45_SEC = 45;
    public const TIME_50_SEC = 50;
    public const TIME_55_SEC = 55;
    public const TIME_60_SEC = 60;
    public const TIME_2_MIN = 120;
    public const TIME_3_MIN = 180;
    public const TIME_4_MIN = 240;
    public const TIME_5_MIN = 300;
    public const TIME_10_MIN = 600;
    public const TIME_15_MIN = 900;
    public const TIME_20_MIN = 1200;
    public const TIME_25_MIN = 1500;
    public const TIME_30_MIN = 1800;
    public const TIME_35_MIN = 2100;
    public const TIME_40_MIN = 2400;
    public const TIME_45_MIN = 2700;
    public const TIME_50_MIN = 3000;
    public const TIME_55_MIN = 3300;
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
    public const TIME_13_HR = 46800;
    public const TIME_14_HR = 50400;
    public const TIME_15_HR = 54000;
    public const TIME_16_HR = 57600;
    public const TIME_17_HR = 61200;
    public const TIME_18_HR = 64800;
    public const TIME_19_HR = 68400;
    public const TIME_20_HR = 72000;
    public const TIME_21_HR = 75600;
    public const TIME_22_HR = 79200;
    public const TIME_23_HR = 82800;
    public const TIME_24_HR = 86400;
    public const TIME_2_DAY = 172800;
    public const TIME_3_DAY = 259200;
    public const TIME_4_DAY = 345600;
    public const TIME_5_DAY = 432000;
    public const TIME_6_DAY = 518400;
    public const TIME_7_DAY = 604800;
    public const TIME_1_MONTH = 2592000;

    public static function getTypeName($nb = 0)
    {
        return _n('User interaction template', 'User interaction templates', $nb, 'deploy');
    }

    public static function getIcon()
    {
        return 'ti ti-template';
    }

    public function defineTabs($options = [])
    {

        $ong = [];
        $this->addDefaultFormTab($ong)
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
            'id'                 => '3',
            'table'              => $this->getTable(),
            'field'              => 'name',
            'name'               => __('Name'),
            'massiveaction'      => false,
            'datatype'           => 'itemlink'
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
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                `json` text,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->request($query);
        }

        // add display preferences
        $nb_display_pref = countElementsInTable(DisplayPreference::getTable(), [
            'itemtype' => self::getType()
        ]);
        if ($nb_display_pref == 0) {
            $migration->updateDisplayPrefs([self::class => [3, 19, 80]]);
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
            'platform' => self::getAllPlatform(),
            'buttons'   => self::getAllInteractionType(),
            'icon'      => self::getAllIcon(),
            'retry_after' => self::getAllRetryAfter(),
            'timeout'   => self::getAllTimeout(),
        ];
        $data = [
            'platform' => 'P',
            'buttons'   => 'B',
            'icon'      => 'I',
            'retry_after' => 'R',
            'nb_max_retry' => 10,
            'timeout'   => 'T',
        ];
        TemplateRenderer::getInstance()->display(
            '@deploy/userinteractiontemplate/userinteractiontemplate.html.twig',
            [
                'item' => $this,
                'list' => $list,
                'data' => $data,
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
            case 'buttons':
                $all = static::getAllInteractionType();
                break;
            case 'platform':
                $all = static::getAllPlatform();
                break;
            case 'icon':
                $all = static::getAllIcon();
                break;
            case 'timeout':
                $all = static::getAllTimeout();
                break;
            case 'retry_after':
                $all = static::getAllRetryAfter();
                break;
        }

        if (!isset($all[$value])) {
            trigger_error(
                sprintf(
                    'Type %1$s does not exists!',
                    $value
                ),
                E_USER_WARNING
            );
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
            case 'buttons':
                $values = static::getAllInteractionType();
                break;
            case 'platform':
                $values = static::getAllPlatform();
                break;
            case 'icon':
                $values = static::getAllIcon();
                break;
            case 'timeout':
                $values = static::getAllTimeout();
                break;
            case 'retry_after':
                $values = static::getAllRetryAfter();
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


    public static function getAllInteractionType(): array
    {
        return [
            self::INTERACTION_OK => __('OK', 'deploy'),
            self::INTERACTION_OK_ASYNC => __('OK (async)', 'deploy'),
            self::INTERACTION_OK_CANCEL => __('OK / Cancel', 'deploy'),
            self::INTERACTION_YES_NO => __('Yes / No', 'deploy'),
            self::INTERACTION_YES_NO_CANCEL => __('Yes / No / Cancel', 'deploy'),
            self::INTERACTION_ABORT_RETRY_IGNORE => __('Abort / Retry / Ignore', 'deploy'),
            self::INTERACTION_RETRY_CANCEL => __('Retry / Cancel', 'deploy'),
            self::INTERACTION_CANCEL_TRY_CONTINUE => __('Cancel / Try / Continue', 'deploy')
        ];
    }

    public static function getAllPlatform(): array
    {
        return [
            self::PLATFORM_WINDOWS_SYSTEM_ALERT => __('Windows system alert', 'deploy')
        ];
    }

    public static function getAllIcon(): array
    {
        return [
            self::ICON_NONE => __('None', 'deploy'),
            self::ICON_INFO => __('Info', 'deploy'),
            self::ICON_WARNING => __('Warning', 'deploy'),
            self::ICON_ERROR => __('Error', 'deploy'),
            self::ICON_QUESTION => __('Question', 'deploy')
        ];
    }

    public static function getAllTimeout(): array
    {
        $timeouts = [];
        // Generate seconds
        for ($i = 30; $i <= 55; $i += 5) {
            $timeouts['TIME_' . $i . '_SEC'] = __($i . ' seconds', 'deploy');
        }
        // Generate minutes
        for ($i = 1; $i <= 60; $i++) {
            $timeouts['TIME_' . $i . '_MIN'] = __($i . ' minutes', 'deploy');
        }
        // Generate hours
        for ($i = 1; $i <= 12; $i++) {
            $timeouts['TIME_' . $i . '_HR'] = __($i . ' hours', 'deploy');
        }
        return $timeouts;
    }

    public static function getAllRetryAfter(): array
    {
        $allTimeouts = self::getAllTimeout();
        $filteredTimeouts = [];

        foreach ($allTimeouts as $key => $value) {
            if (strpos($key, 'SEC') !== false && $key !== 'TIME_60_SEC') {
                continue;
            }
            $filteredTimeouts[$key] = $value;
        }

        // Add additional timeouts up to a month
        for ($i = 13; $i <= 24; $i++) {
            $filteredTimeouts['TIME_' . $i . '_HR'] = __($i . ' hours', 'deploy');
        }

        for ($i = 2; $i <= 7; $i++) {
            $filteredTimeouts['TIME_' . $i . '_DAY'] = __($i . ' days', 'deploy');
        }

        $filteredTimeouts['TIME_1_MONTH'] = __('1 month', 'deploy');

        return $filteredTimeouts;
    }

    public static function getSpecificValueToSelect($field, $name = '', $values = '', array $options = []): string
    {
        if (!is_array($values)) {
            $values = [$field => $values];
        }
        $options['display'] = false;

        switch ($field) {
            case 'buttons':
                return self::getDataLabelDropdown($field, $values[$field], $options);
            case 'platform':
                return self::getDataLabelDropdown($field, $values[$field], $options);
            case 'icon':
                return self::getDataLabelDropdown($field, $values[$field], $options);
            case 'timeout':
                return self::getDataLabelDropdown($field, $values[$field], $options);
            case 'retry_after':
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
            case 'buttons':
                return self::getDataLabel($field, $values[$field]);
            case 'platform':
                return self::getDataLabel($field, $values[$field]);
            case 'icon':
                return self::getDataLabel($field, $values[$field]);
            case 'timeout':
                return self::getDataLabel($field, $values[$field]);
            case 'retry_after':
                return self::getDataLabel($field, $values[$field]);
        }
        return parent::getSpecificValueToDisplay($field, $values, $options);
    }
}
