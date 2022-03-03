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

class PluginDeployPackage_Action extends CommonDBTM
{
    use PluginDeployPackage_Subitem;

    public static $rightname = 'entity';

    private const SUBITEM_TYPE = 'action';

    public const ACTION_CMD    = 'cmd';
    public const ACTION_MOVE   = 'move';
    public const ACTION_COPY   = 'copy';
    public const ACTION_DELETE = 'delete';
    public const ACTION_MKDIR  = 'mkdir';

    public static function getTypeName($nb = 0)
    {
        return _n('Action', 'Actions', $nb, 'deploy');
    }


    public static function getIcon()
    {
        return 'ti ti-bolt';
    }


    private static function getheadings(): array
    {
        return [
            'name' => __('Label', 'deploy'),
            'type' => __('Action type', 'deploy'),
            'json' => __('Action data', 'deploy'),
        ];
    }


    public static function getTypes(bool $with_icon = false): array
    {
        return [
            SELF::ACTION_CMD    => ($with_icon ? '<i class="fa-fw me-1 ti ti-terminal"></i>' : "")
                                   . __('Run command', 'deploy'),
            SELF::ACTION_MOVE   => ($with_icon ? '<i class="fa-fw me-1 ti ti-drag-drop-2"></i>' : "")
                                   . __('Move file', 'deploy'),
            SELF::ACTION_COPY   => ($with_icon ? '<i class="fa-fw me-1 ti ti-copy"></i>' : "")
                                   . __('Copy file', 'deploy'),
            SELF::ACTION_DELETE => ($with_icon ? '<i class="fa-fw me-1 ti ti-file-minus"></i>' : "")
                                   . __('Delete file', 'deploy'),
            SELF::ACTION_MKDIR  => ($with_icon ? '<i class="fa-fw me-1 ti ti-folder-plus"></i>' : "")
                                   . __('Create directory', 'deploy'),
        ];
    }


    public static function getLabelForType(string $type = null, bool $with_icon = false): string
    {
        $types = self::getTypes($with_icon);
        return $types[$type] ?? "";
    }


    public static function getFormattedData(string $json = "", string $type = ""): string
    {
        $data_str = "";
        $json_fields = self::jsonToArray($json, $type);

        switch ($type) {
            case self::ACTION_CMD:
                $data_str = '<pre>' . $json_fields['exec']. '</pre>';
                break;
            case self::ACTION_MOVE:
            case self::ACTION_COPY:
                $data_str = sprintf(
                    __("From %s to %s", 'deploy'),
                    '<code>' .$json_fields['from']. '</code>',
                    '<code>' .$json_fields['to']. '</code>'
                );
                break;
            case self::ACTION_DELETE:
                $data_str = '<del><code>' . implode('</code></del><br><del><code>', $json_fields['list']). '</code></del>';
                break;
            case self::ACTION_MKDIR:
                $data_str = '<code>' . implode('</code><br><code>', $json_fields['list']). '</code>';
                break;
        }

        return $data_str;
    }


    public function prepareInputForAdd($input)
    {
        $input = $this->prepareJsonInput($input);

        return $input;
    }

    public function prepareInputForUpdate($input)
    {
        $input = $this->prepareJsonInput($input);

        return $input;
    }

    public function post_getFromDB()
    {
        $json_fields = $this->jsonToArray($this->fields['json'], $this->fields['type']);
        $this->fields = array_merge($this->fields, $json_fields);
    }


    private static function jsonToArray(string $json = "", string $type = ""): array
    {
        $json_fields = json_decode($json, true);
        $json_fields = $json_fields[$type] ?? [];
        unset($json_fields['name']);
        return $json_fields;
    }


    private function prepareJsonInput(array $input = []): array
    {
        if (!isset($input['type'])) {
            return $input;
        }

        $json_array = [
            $input['type'] => [
                'name' => $input['name'] ?? "",
            ]
        ];
        switch ($input['type']) {
            case self::ACTION_CMD:
                $json_array[$input['type']]['exec'] = $input['exec'] ?? "";
                break;
            case self::ACTION_MOVE:
            case self::ACTION_COPY:
                $json_array[$input['type']]['from'] = $input['from'] ?? "";
                $json_array[$input['type']]['to']   = $input['to'] ?? "";
                break;
            case self::ACTION_DELETE:
            case self::ACTION_MKDIR:
                $list = explode('\n', trim($input['list']) ?? "");
                $list = array_map('trim', $list);
                $json_array[$input['type']]['list'] = $list;
                break;
        }

        $input['json'] = json_encode($json_array);
        return $input;
    }


    public static function getFormattedArrayForPackage(PluginDeployPackage $package): array
    {
        $files = [];
        foreach (self::getForPackage($package) as $entry) {
            $files[] = json_decode($entry['json'], true);
        }

        return $files;
    }


    public static function install(Migration $migration)
    {
        global $DB;

        $table = self::getTable();
        if (!$DB->tableExists($table)) {
            $migration->displayMessage("Installing $table");

            $default_charset = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int NOT NULL AUTO_INCREMENT,
                `plugin_deploy_packages_id` int unsigned NOT NULL DEFAULT '0',
                `name` varchar(255) DEFAULT NULL,
                `type` varchar(50) DEFAULT NULL,
                `json` text,
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `plugin_deploy_packages_id` (`plugin_deploy_packages_id`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die($DB->error());
        }
    }
}
