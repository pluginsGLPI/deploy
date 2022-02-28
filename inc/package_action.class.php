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
    public const ACTION_MKDIR  = 'delete';

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
            'type' => __('Action type', 'deploy'),
        ];
    }


    public static function getTypes(): array
    {
        return [
            SELF::ACTION_CMD    => __('Run command', 'deploy'),
            SELF::ACTION_MOVE   => __('Move file', 'deploy'),
            SELF::ACTION_COPY   => __('Copy file', 'deploy'),
            SELF::ACTION_DELETE => __('Delete file', 'deploy'),
            SELF::ACTION_MKDIR  => __('Create directory', 'deploy'),
        ];
    }


    public static function getLabelForType(string $type = null): string
    {
        $types = self::getTypes();
        return $types[$type] ?? "";
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
