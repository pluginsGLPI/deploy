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

use Glpi\Application\View\TemplateRenderer;

trait PluginDeployPackage_Subitem
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginDeployPackage') {
            $entries = self::getForPackage($item);
            $number  = count($entries);
            return self::createTabEntry(self::getTypeName($number), $number);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == 'PluginDeployPackage') {
            self::showForPackage($item);
        }
    }


    public static function getForPackage(PluginDeployPackage $package): DBmysqlIterator
    {
        $DBread   = DBConnection::getReadConnection();
        $iterator = $DBread->request([
            'FROM'  => self::getTable(),
            'WHERE' => [
                'plugin_deploy_packages_id' => $package->fields['id']
            ],
            'ORDER' => ['order']
        ]);

        return $iterator;
    }


    private static function getheadings(): array
    {
        return [];
    }


    public static function showForPackage(PluginDeployPackage $package)
    {
        $entries = self::getForPackage($package);
        TemplateRenderer::getInstance()->display('@deploy/package/subitem.list.html.twig', [
            'icon'                      => self::getIcon(),
            'subitem_type'              => self::SUBITEM_TYPE,
            'subitem_itemtype'          => self::getType(),
            'plugin_deploy_packages_id' => $package->fields['id'],
            'count'                     => count($entries),
            'entries'                   => $entries,
            'none_found'                => sprintf(__('No %s found', 'deploy'), self::getTypeName(Session::getPluralNumber())),
            'add_title'                 => sprintf(__('Add a %s', 'deploy'), self::getTypeName(1)),
            'edit_title'                => sprintf(__('Edit a %s', 'deploy'), self::getTypeName(1)),
            'subitem_line'              => '@deploy/package/' . self::SUBITEM_TYPE . '.line.html.twig',
            'headings'                  => self::getheadings(),
        ]);
    }


    public static function showAdd(int $plugin_deploy_packages_id = 0)
    {
        $subitem_instance = new self;
        $subitem_instance->getEmpty();
        $subitem_instance->fields['plugin_deploy_packages_id'] = $plugin_deploy_packages_id;
        TemplateRenderer::getInstance()->display('@deploy/package/subitem.form.html.twig', [
            'subitem_type'     => self::SUBITEM_TYPE,
            'subitem_instance' => $subitem_instance,
            'subitem_form'     => '@deploy/package/' . self::SUBITEM_TYPE . '.form.html.twig',
        ]);
    }


    public static function showEdit(int $ID = 0)
    {
        $subitem_instance = new self();
        $subitem_instance->getFromDB($ID);
        TemplateRenderer::getInstance()->display('@deploy/package/subitem.form.html.twig', [
            'subitem_type'     => self::SUBITEM_TYPE,
            'subitem_instance' => $subitem_instance,
            'subitem_form'     => '@deploy/package/' . self::SUBITEM_TYPE . '.form.html.twig',
        ]);
    }


    public function getNextOrder()
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['MAX' => 'order'],
            'FROM'   => self::getTable(),
            'WHERE'  => ['plugin_deploy_packages_id' => $this->fields['plugin_deploy_packages_id']]
        ]);

        if (count($iterator)) {
            $data = $iterator->current();
            return $data["order"] + 1;
        }
        return 0;
    }


    public static function uninstall(Migration $migration)
    {
        $table = static::getTable();
        $migration->displayMessage("Uninstalling $table");
        $migration->dropTable($table);
    }
}
