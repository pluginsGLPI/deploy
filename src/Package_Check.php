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

class Package_Check extends CommonDBTM
{
    use Package_Subitem;

    public static $rightname = 'entity';

    private const SUBITEM_TYPE = 'check';

    public const WINKEYEXISTS         = 'winkeyExists';
    public const WINVALUEEXISTS       = 'winvalueExists';
    public const WINKEYMISSING        = 'winkeyMissing';
    public const WINVALUEMISSING      = 'winvalueMissing';
    public const WINKEYEQUALS         = 'winkeyEquals';
    public const WINKEYNOTEQUALS      = 'winkeyNotEquals';
    public const WINVALUETYPE         = 'winvalueType';
    public const FILEEXISTS           = 'fileExists';
    public const FILEMISSING          = 'fileMissing';
    public const FILESIZEGREATER      = 'fileSizeGreater';
    public const FILESIZEEQUALS       = 'fileSizeEquals';
    public const FILESIZELOWER        = 'fileSizeLower';
    public const FILESHA512           = 'fileSHA512';
    public const FILESHA512MISMATCH   = 'fileSHA512mismatch';
    public const DIRECTORYEXISTS      = 'directoryExists';
    public const DIRECTORYMISSING     = 'directoryMissing';
    public const FREESPACEGREATER     = 'freespaceGreater';

    public const REG_SZ               = 'REG_SZ';
    public const REG_DWORD            = 'REG_DWORD';
    public const REG_BINARY           = 'REG_BINARY';
    public const REG_EXPAND_SZ        = 'REG_EXPAND_SZ';
    public const REG_MULTI_SZ         = 'REG_MULTI_SZ';
    public const REG_LINK             = 'REG_LINK';
    public const REG_DWORD_BIG_ENDIAN = 'REG_DWORD_BIG_ENDIAN';
    public const REG_NONE             = 'REG_NONE';

    public const RET_ERROR            = "error";
    public const RET_SKIP             = "skip";
    public const RET_STARTNOW         = "startnow";
    public const RET_INFO             = "info";
    public const RET_WARNING          = "warning";

    public static function getTypeName($nb = 0)
    {
        return _n('Check', 'Checks', $nb, 'deploy');
    }


    public static function getIcon()
    {
        return 'ti ti-checks';
    }


    private static function getheadings(): array
    {
        return [
            'name'   => __('Label', 'deploy'),
            'type'   => __('Check type', 'deploy'),
            'path'   => __('Path', 'deploy'),
            'value'  => __('Value', 'deploy'),
            'return' => __('If not successful', 'deploy'),
        ];
    }


    public static function getTypes(bool $flat = false): array
    {
        $types = [
            'registry' => [
                'icon'     => 'ti ti-brand-windows',
                'label'    => __('Registry', 'deploy'),
                'subtypes' => [
                    self::WINKEYEXISTS       => __("Registry key exists", 'deploy'),
                    self::WINVALUEEXISTS     => __("Registry value exists", 'deploy'),
                    self::WINKEYMISSING      => __("Registry key missing", 'deploy'),
                    self::WINVALUEMISSING    => __("Registry value missing", 'deploy'),
                    self::WINKEYEQUALS       => __("Registry value equals to", 'deploy'),
                    self::WINKEYNOTEQUALS    => __("Registry value not equals to", 'deploy'),
                    self::WINVALUETYPE       => __("Type of registry value equals to", 'deploy')
                ]
            ],
            'file' => [
                'icon'     => 'ti ti-file',
                'label'    => __('File'),
                'subtypes' => [
                    self::FILEEXISTS         => __("File exists", 'deploy'),
                    self::FILEMISSING        => __("File is missing", 'deploy'),
                    self::FILESIZEGREATER    => __("File size is greater than", 'deploy'),
                    self::FILESIZEEQUALS     => __("File size is equal to", 'deploy'),
                    self::FILESIZELOWER      => __("File size is lower than", 'deploy'),
                    self::FILESHA512         => __("SHA-512 hash value matches", 'deploy'),
                    self::FILESHA512MISMATCH => __("SHA-512 hash value mismatch", 'deploy'),
                ]
            ],
            'directory' => [
                'icon'     => 'ti ti-subtask',
                'label'    => __('Directory'),
                'subtypes' => [
                    self::DIRECTORYEXISTS  => __("Directory exists", 'deploy'),
                    self::DIRECTORYMISSING => __("Directory is missing", 'deploy'),
                ]
            ],
            'other' => [
                'icon'     => 'ti ti-dots',
                'label'    => __('Other'),
                'subtypes' => [
                    self::FREESPACEGREATER => __("Free space is greater than", 'deploy')
                ]
            ]
        ];

        if ($flat) {
            $flat_types = [];
            foreach ($types as $key => $type) {
                $flat_types = array_merge($flat_types, $type['subtypes']);
            }
            $types = $flat_types;
        }

        return $types;
    }


    public static function getTypesWithValueField(): array
    {
        return [
            self::WINKEYEQUALS       => __("Value", 'deploy') . '<i class="ms-2 ti ti-equal"></i>',
            self::WINKEYNOTEQUALS    => __("Value", 'deploy') . '<i class="ms-2 ti ti-equal-not"></i>',
            self::WINVALUETYPE       => __("Type of value", 'deploy'). '<i class="ms-2 ti ti-forms"></i>',

            self::FILESIZEGREATER    => __("Size", 'deploy') . '<i class="ms-2 fas fa-greater-than"></i>',
            self::FILESIZEEQUALS     => __("Size", 'deploy') . '<i class="ms-2 ti ti-equal"></i>',
            self::FILESIZELOWER      => __("Size", 'deploy') . '<i class="ms-2 fas fa-less-than"></i>',
            self::FILESHA512         => __("SHA512", 'deploy') . '<i class="ms-2 ti ti-hash"></i>',
            self::FILESHA512MISMATCH => __("SHA512", 'deploy') . '<i class="ms-2 ti ti-hash"></i>',

            self::FREESPACEGREATER   => __("Size", 'deploy') . '<i class="ms-2 fas fa-greater-than"></i>',
        ];
    }


    public static function getIconForParentType(string $parent_type = null): string
    {
        $types = self::getTypes(false);
        return $types[$parent_type]['icon'] ?? "";
    }



    public static function getLabelForType(string $type = null): string
    {
        $types = self::getTypes(true);
        return $types[$type] ?? "";
    }


    public static function getRegistryTypes(): array
    {
        return [
            self::REG_SZ,
            self::REG_DWORD,
            self::REG_BINARY,
            self::REG_EXPAND_SZ,
            self::REG_MULTI_SZ,
            self::REG_LINK,
            self::REG_DWORD_BIG_ENDIAN,
            self::REG_NONE,
        ];
    }

    public static function getReturnValues(bool $with_icon = false): array
    {
        return  [
            self::RET_ERROR    => ($with_icon ? '<i class="fa-fw me-1 ti ti-circle-x"></i>' : "")
                                  . __("Abort job", 'deploy'),
            self::RET_SKIP     => ($with_icon ? '<i class="fa-fw me-1 ti ti-player-skip-forward"></i>' : "")
                                  . __("Skip job", 'deploy'),
            self::RET_STARTNOW => ($with_icon ? '<i class="fa-fw me-1 ti ti-player-play"></i>' : "")
                                  . __("Start job now", 'deploy'),
            self::RET_INFO     => ($with_icon ? '<i class="fa-fw me-1 ti ti-info-circle"></i>' : "")
                                  . __("Report info", 'deploy'),
            self::RET_WARNING  => ($with_icon ? '<i class="fa-fw me-1 ti ti-alert-triangle"></i>' : "")
                                  . __("Report warning", 'deploy')
        ];
    }


    public static function getLabelForReturnValue(string $value = null, bool $with_icon = false): string
    {
        $values = self::getReturnValues($with_icon);
        $string = $values[$value] ?? "";

        switch ($value) {
            case self::RET_ERROR:
                $string = "<span class='text-danger'>$string</span>";
                break;
            case self::RET_INFO:
                $string = "<span class='text-info'>$string</span>";
                break;
            case self::RET_WARNING:
                $string = "<span class='text-warning'>$string</span>";
                break;
        }

        return $string;
    }


    public static function getFormattedArrayForPackage(Package $package): array
    {
        $checks = [];
        foreach (self::getForPackage($package) as $entry) {
            $checks[] = [
                'name'   => $entry['name'] ?? "",
                'type'   => $entry['type'] ?? "",
                'path'   => $entry['path'] ?? "",
                'value'  => $entry['value'] ?? "",
                'return' => $entry['return'] ?? "",
            ];
        }

        return $checks;
    }


    public function prepareInputForAdd($input)
    {
        $input["order"] = $input['order'] ?? $this->getNextOrder((int) $input['plugin_deploy_packages_id']);

        return $input;
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
                `parent_type` varchar(50) DEFAULT NULL,
                `type` varchar(50) DEFAULT NULL,
                `name` varchar(255) DEFAULT NULL,
                `path` text,
                `value` text,
                `return` varchar(255) DEFAULT NULL,
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
