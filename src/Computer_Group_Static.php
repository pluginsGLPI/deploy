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
use Computer;
use Dropdown;
use Entity;
use Glpi\Application\View\TemplateRenderer;
use Html;
use Migration;
use Search;
use Session;
use Toolbox;

class Computer_Group_Static extends CommonDBRelation
{

   // From CommonDBRelation
   static public $itemtype_1 = 'GlpiPlugin\Deploy\Computer_Group';
   static public $items_id_1 = 'plugin_deploy_computers_groups_id';
   static public $itemtype_2 = 'Computer';
   static public $items_id_2 = 'computers_id';

   static public $checkItem_2_Rights  = self::DONT_CHECK_ITEM_RIGHTS;
   static public $logs_for_item_2     = false;
   public $auto_message_on_action     = false;

   static    $rightname  = 'computer_group';


   static function getTypeName($nb = 0) {
      return _n('Static groups', 'Static group', $nb, 'deploy');
   }


   static function canCreate() {
      return Session::haveRight(static::$rightname, UPDATE);
   }


   function canCreateItem() {
      return Session::haveRight(static::$rightname, UPDATE);
   }


   static function canPurge() {
      return Session::haveRight(static::$rightname, UPDATE);
      return true;
   }


   function getTabNameForItem(CommonGLPI $item, $withtemplate = 0) {
      if (get_class($item) == Computer_Group::getType()) {
         $count = 0;
         $count = countElementsInTable(self::getTable(), ['plugin_deploy_computers_groups_id' => $item->getID()]);
         $ong = [];
         $ong[1] = self::createTabEntry(Self::getTypeName(Session::getPluralNumber()), $count);
         return $ong;
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {
      switch ($tabnum) {
         case 1 :
            self::showForItem($item);
            break;
      }
      return true;
   }


   private static function getheadings(): array
   {
       return [
         'name' => __('Name'),
         'is_dynamic' => __('Automatic inventory'),
         'entities_id' => Entity::getTypeName(1),
         'serial' => __('Serial number'),
         'otherserial' => __('Inventory number'),
       ];
   }


   static function showForItem(Computer_Group $computergroup)
   {
      global $DB;

      $ID = $computergroup->getField('id');
      if (!$computergroup->can($ID, UPDATE)) {
         return false;
      }

      $datas = [];
      $used  = [];
      $params = [
         'SELECT' => '*',
         'FROM'   => self::getTable(),
         'WHERE'  => ['plugin_deploy_computers_groups_id' => $ID],
      ];

      $iterator = $DB->request($params);
      foreach ($iterator as $data) {
         $datas[] = $data;
         $used [] = $data['computers_id'];
      }

      if ($computergroup->canAddItem('itemtype')) {
         TemplateRenderer::getInstance()->display('@deploy/computer_group/computer_group_static.html.twig', [
            'form_action'  => Toolbox::getItemTypeFormURL("GlpiPlugin\Deploy\Computer_Group"),
            'computers_groups_id' => $ID,
            'computer_used' => $used,
            'params'       => [],
         ]);
      }

      $canread = $computergroup->can($ID, READ);
      $rows = [];
      if ($canread) {
         foreach ($datas as $data) {
            $row = [];

            $computer = new Computer();
            $computer->getFromDB($data["computers_id"]);
            $linkname = $computer->fields["name"];
            $itemtype = Computer::getType();
            if ($_SESSION["glpiis_ids_visible"] || empty($computer->fields["name"])) {
               $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $computer->fields["id"]);
            }
            $link = $itemtype::getFormURLWithID($computer->fields["id"]);
            $name = "<a href=\"".$link."\">".$linkname."</a>";

            $row['name'] = $name;
            $row['id'] = $data["id"];
            $row['is_deleted'] = $computer->fields["is_deleted"];
            $row['is_dynamic'] = Dropdown::getYesNo($computer->fields['is_dynamic']);
            $row['entity'] = Dropdown::getDropdownName("glpi_entities", $computer->fields['entities_id']);
            $row['serial'] = (isset($computer->fields["serial"])? "".$computer->fields["serial"]."" :"-");
            $row['otherserial'] = (isset($computer->fields["otherserial"])? "".$computer->fields["otherserial"]."" :"-");
            $rows[] = $row;
         }

         TemplateRenderer::getInstance()->display('@deploy/computer_group/computer_group_static_list.html.twig', [
            'subitem_type'                      => 'ComputerGroupStatic',
            'itemtype'                  => self::getType(),
            'plugin_deploy_computers_groups_id' => $ID,
            'count'                     => count($rows),
            'entries'                   => $rows,
            'none_found'                => sprintf(__('No %s found', 'deploy'), self::getTypeName(Session::getPluralNumber())),
            'headings'                  => self::getheadings(),
         ]);

      }

      return true;
   }


   public static function install(Migration $migration) {
      global $DB;
      $table = self::getTable();
      if (!$DB->tableExists($table)) {
         $migration->displayMessage("Installing $table");
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
                      `id` int(11) NOT NULL AUTO_INCREMENT,
                      `plugin_deploy_computers_groups_id` int(11) NOT NULL DEFAULT '0',
                      `computers_id` int(11) NOT NULL DEFAULT '0',
                      PRIMARY KEY (`id`),
                      KEY `computers_id` (`computers_id`),
                      KEY `computergroups_id` (`plugin_deploy_computers_groups_id`)
                      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;";
         $DB->query($query) or die($DB->error());
      }
   }


   public static function uninstall(Migration $migration) {
      global $DB;
      $table = self::getTable();
      if ($DB->tableExists($table)) {
         $DB->query("DROP TABLE IF EXISTS `".self::getTable()."`") or die ($DB->error());
      }
   }

}
