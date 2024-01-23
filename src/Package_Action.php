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

use CommonDBTM;
use DBConnection;
use Migration;

class Package_Action extends CommonDBTM
{
    use Package_Subitem;

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
        $base_icon = '<i class="fa-fw me-1 %s"></i>';
        return [
            SELF::ACTION_CMD    => ($with_icon ? sprintf($base_icon, self::getTypeIconClass(SELF::ACTION_CMD)) : "")
                                   . __('Run command', 'deploy'),
            SELF::ACTION_MOVE   => ($with_icon ? sprintf($base_icon, self::getTypeIconClass(SELF::ACTION_MOVE)) : "")
            . __('Move file', 'deploy'),
            SELF::ACTION_COPY   => ($with_icon ? sprintf($base_icon, self::getTypeIconClass(SELF::ACTION_COPY)) : "")
            . __('Copy file', 'deploy'),
            SELF::ACTION_DELETE => ($with_icon ? sprintf($base_icon, self::getTypeIconClass(SELF::ACTION_DELETE)) : "")
            . __('Delete file', 'deploy'),
            SELF::ACTION_MKDIR => ($with_icon ? sprintf($base_icon, self::getTypeIconClass(SELF::ACTION_MKDIR)) : "")
                                   . __('Create directory', 'deploy'),
        ];
    }


    public static function getTypeIconClass($type = ""): string
    {
        switch ($type) {
            case SELF::ACTION_CMD:
                return 'ti ti-terminal';
            case SELF::ACTION_MOVE:
                return 'ti ti-drag-drop-2';
            case SELF::ACTION_COPY:
                return 'ti ti-copy';
            case SELF::ACTION_DELETE:
                return 'ti ti-file-minus';
            case SELF::ACTION_MKDIR:
                return 'ti ti-folder-plus';
        }

        return "";
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
        $input["order"] = $input['order'] ?? $this->getNextOrder((int) $input['plugin_deploy_packages_id']);

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
                $list = str_replace('\r', '', $input['list']);
                $list = explode('\n', trim($list) ?? "");
                $list = array_map('trim', $list);
                $json_array[$input['type']]['list'] = $list;
                break;
        }

        $input['json'] = json_encode($json_array);
        return $input;
    }


    public static function getFormattedArrayForPackage(Package $package): array
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

            $default_charset   = DBConnection::getDefaultCharset();
            $default_collation = DBConnection::getDefaultCollation();
            $sign              = DBConnection::getDefaultPrimaryKeySignOption();

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int $sign NOT NULL AUTO_INCREMENT,
                `plugin_deploy_packages_id` int $sign NOT NULL DEFAULT '0',
                `name` varchar(255) DEFAULT NULL,
                `type` varchar(50) DEFAULT NULL,
                `json` text,
                `order` smallint unsigned NOT NULL DEFAULT '0',
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
