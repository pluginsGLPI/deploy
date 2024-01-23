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

use CommonGLPI;
use DBConnection;
use DBmysqlIterator;
use Glpi\Application\View\TemplateRenderer;
use Migration;
use Session;

trait Package_Subitem
{
    public function getTabNameForItem(CommonGLPI $item, $withtemplate = 0)
    {
        if ($item->getType() == Package::class) {
            $entries = self::getForPackage($item);
            $number  = count($entries);
            return self::createTabEntry(self::getTypeName($number), $number);
        }
        return '';
    }


    public static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0)
    {
        if ($item->getType() == Package::class) {
            self::showForPackage($item);
        }
    }


    public static function getForPackage(Package $package): DBmysqlIterator
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


    public static function showForPackage(Package $package)
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


    public function getNextOrder(int $packages_id)
    {
        global $DB;

        $iterator = $DB->request([
            'SELECT' => ['MAX' => 'order as order'],
            'FROM'   => self::getTable(),
            'WHERE'  => ['plugin_deploy_packages_id' => $packages_id]
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
