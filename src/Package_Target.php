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

use CommonDBRelation;
use CommonGLPI;
use DBConnection;
use Glpi\Application\View\TemplateRenderer;
use GlpiPlugin\Deploy\Computer\Group;
use Migration;
use Session;

class Package_Target extends CommonDBRelation
{
    public static $itemtype_1 = Package::class;
    public static $items_id_1 = "plugin_deploy_packages_id";

    public static $itemtype_2 = Group::class;
    public static $items_id_2 = "plugin_deploy_computers_groups_id";

    static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
    static public $logs_for_item_2     = false;
    public $auto_message_on_action     = false;

    static    $rightname  = 'computer_group';

    public static function getTypeName($nb = 0)
    {
        return _n("Target", "Targets", $nb, "deploy");
    }


    public static function getIcon()
    {
        return "ti ti-devices-pc";
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $number = count(getAllDataFromTable(self::getTable(), ['plugin_deploy_computers_groups_id' => $item->getID()]));

        switch ($item->getType()) {
            case Package::class:
                return self::createTabEntry(self::getTypeName($number), $number, self::class, self::getIcon());
        }

        return parent::getTabNameForItem($item, $withtemplate);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Package::class:
                return self::showForPackage($item);
        }

        return parent::displayTabContentForItem($item, $tabnum, $withtemplate);
    }


    public static function showForPackage(Package $package)
    {
        global $DB;

        $iterator = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => [
                'plugin_deploy_packages_id' => $package->fields['id']
            ],
        ]);

        $targets = [];
        $used = [];
        foreach ($iterator as $target) {
            $item = new Group();
            $item->getFromDB($target['plugin_deploy_computers_groups_id']);
            $targets[$target['id']] = $item;
            $used[$target['plugin_deploy_computers_groups_id']] = $target['plugin_deploy_computers_groups_id'];
        }

        TemplateRenderer::getInstance()->display('@deploy/package/target.list.html.twig', [
            'package'     => $package,
            'subitems'    => $targets,
            'used_item'   => $used,
            'none_found'  => __("No target found", 'deploy'),
            'ma_itemtype' => self::class,
        ]);
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

            $package_fk = getForeignKeyFieldForItemType(Package::class);
            $computer_group_fk = getForeignKeyFieldForItemType(Group::class);

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int $sign NOT NULL AUTO_INCREMENT,
                `$package_fk` int $sign NOT NULL DEFAULT '0',
                `$computer_group_fk` int $sign NOT NULL DEFAULT '0',
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `$package_fk` (`$package_fk`),
                KEY `$computer_group_fk` (`$computer_group_fk`),
                UNIQUE KEY `item` (`$package_fk`,`$computer_group_fk`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->doQuery($query) or die($DB->error());
        }
    }


    public static function uninstall(Migration $migration)
    {
        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }
}
