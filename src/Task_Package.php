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

use CommonDBRelation;
use CommonGLPI;
use DBConnection;
use Glpi\Application\View\TemplateRenderer;
use Migration;
use Session;

class Task_Package extends CommonDBRelation
{
    public static $itemtype_1 = Task::class;
    public static $items_id_1 = "plugin_deploy_tasks_id";

    public static $itemtype_2 = Package::class;
    public static $items_id_2 = "plugin_deploy_packages_id";

    public static function getTypeName($nb = 0)
    {
        return Package::getTypeName($nb);
    }


    public static function getIcon()
    {
        return Package::getIcon();
    }


    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        $number = self::countForItem($item);

        switch ($item->getType()) {
            case Task::class:
                return self::createTabEntry(Package::getTypeName($number), $number);
            case Package::class:
                return self::createTabEntry(Task::getTypeName($number), $number);
        }

        return parent::getTabNameForItem($item, $withtemplate);
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        switch ($item->getType()) {
            case Task::class:
                return self::showForTask($item);
            case Package::class:
                return self::showForPackage($item);
        }

        return parent::displayTabContentForItem($item, $tabnum, $withtemplate);
    }


    public static function showForTask(Task $task)
    {
        global $DB;

        $existings = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => [
                'plugin_deploy_tasks_id' => $task->fields['id']
            ],
        ]);
        $packages = [];
        $used = [];
        $package = new Package();
        foreach ($existings as $existing) {
            $package->getFromDB($existing['plugin_deploy_packages_id']);
            $packages[$existing['id']] = $package;
            $used[] = $package->fields['id'];
        }

        TemplateRenderer::getInstance()->display('@deploy/task/package.list.html.twig', [
            'task'        => $task,
            'subitems'    => $packages,
            'used'        => $used,
            'none_found'  => sprintf(__('No %s found', 'deploy'), Package::getTypeName(Session::getPluralNumber())),
            'task_active' => $task->fields['is_active'],
            'ma_itemtype' => self::class,
        ]);

    }


    public static function showForPackage(Package $package)
    {
        global $DB;

        $existings = $DB->request([
            'FROM'  => self::getTable(),
            'WHERE' => [
                'plugin_deploy_packages_id' => $package->fields['id']
            ],
        ]);
        $tasks = [];
        $task = new Task();
        foreach ($existings as $existing) {
            $task->getFromDB($existing['plugin_deploy_tasks_id']);
            $tasks[] = $task;
        }

        TemplateRenderer::getInstance()->display('@deploy/package/task.list.html.twig', [
            'tasks' => $tasks,
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

            $task_fk = getForeignKeyFieldForItemType(Task::class);
            $package_fk = getForeignKeyFieldForItemType(Package::class);

            $query = "CREATE TABLE IF NOT EXISTS `$table` (
                `id` int $sign NOT NULL AUTO_INCREMENT,
                `$task_fk` int $sign NOT NULL DEFAULT '0',
                `$package_fk` int $sign NOT NULL DEFAULT '0',
                `date_creation` timestamp NULL DEFAULT NULL,
                `date_mod` timestamp NULL DEFAULT NULL,
                PRIMARY KEY (`id`),
                KEY `$task_fk` (`$task_fk`),
                KEY `$package_fk` (`$package_fk`),
                KEY `date_creation` (`date_creation`),
                KEY `date_mod` (`date_mod`)
            ) ENGINE=InnoDB DEFAULT CHARSET={$default_charset} COLLATE={$default_collation} ROW_FORMAT=DYNAMIC;";
            $DB->query($query) or die($DB->error());
        }
    }


    public static function uninstall(Migration $migration)
    {
        $table = self::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }
}
