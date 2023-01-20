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

use CommonDBRelation;
use CommonGLPI;
use Computer;
use Dropdown;
use Entity;
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

   static    $rightname  = 'database_inventory';


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


   static function showForItem(Computer_Group $computergroup) {
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
      $number = count($datas);

      echo "<div class='spaced'>";
      if ($computergroup->canAddItem('itemtype')) {
         $rand = mt_rand();
         echo "<div class='firstbloc'>";
         echo "<form method='post' name='staticcomputer_form$rand'
                     id='staticcomputer$rand'
                     action='" . Toolbox::getItemTypeFormURL("GlpiPlugin\Deploy\Computer_Group") . "'>";

         echo "<table class='tab_cadre_fixe'>";
         echo "<tr class='tab_bg_2'>";
         echo "<th colspan='2'>" . __('Add an item') . "</th></tr>";

         echo "<tr class='tab_bg_1'><td class='left'>";
         Dropdown::show("Computer",
            [
               "name" => "computers_id",
               "used" => $used,
               "condition" => ["is_dynamic" => true]
            ]
         );
         echo "</td><td class='center' class='tab_bg_1'>";

         echo Html::hidden('plugin_deploy_computers_groups_id', ['value' => $ID]);
         echo Html::submit(_x('button', 'Add'), ['name' => 'add_staticcomputer']);
         echo "</td></tr>";
         echo "</table>";
         Html::closeForm();
         echo "</div>";
      }
      echo "</div>";

      $canread = $computergroup->can($ID, READ);
      $canedit = $computergroup->can($ID, UPDATE);
      echo "<div class='spaced'>";
      if ($canread) {
         echo "<div class='spaced'>";
         if ($canedit) {
            Html::openMassiveActionsForm('mass'.__CLASS__.$rand);
            $massiveactionparams= ['num_displayed'
                           => min($_SESSION['glpilist_limit'], $number),
                       'specific_actions'
                           => ['purge' => _x('button', 'Remove')],
                       'container'
                           => 'mass'.__CLASS__.$rand];
            Html::showMassiveActions($massiveactionparams);
         }
         echo "<table class='tab_cadre_fixehov'>";
         $header_begin  = "<tr>";
         $header_top    = '';
         $header_bottom = '';
         $header_end    = '';

         if ($canedit) {
            $header_top    .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_top    .= "</th>";
            $header_bottom .= "<th width='10'>".Html::getCheckAllAsCheckbox('mass'.__CLASS__.$rand);
            $header_bottom .=  "</th>";
         }

         $header_end .= "<th>".__('Name')."</th>";
         $header_end .= "<th>".__('Automatic inventory')."</th>";
         $header_end .= "<th>".Entity::getTypeName(1)."</th>";
         $header_end .= "<th>".__('Serial number')."</th>";
         $header_end .= "<th>".__('Inventory number')."</th>";
         $header_end .= "</tr>";
         echo $header_begin.$header_top.$header_end;

         foreach ($datas as $data) {

            $computer = new Computer();
            $computer->getFromDB($data["computers_id"]);
            $linkname = $computer->fields["name"];
            $itemtype = Computer::getType();
            if ($_SESSION["glpiis_ids_visible"] || empty($computer->fields["name"])) {
               $linkname = sprintf(__('%1$s (%2$s)'), $linkname, $computer->fields["id"]);
            }
            $link = $itemtype::getFormURLWithID($computer->fields["id"]);
            $name = "<a href=\"".$link."\">".$linkname."</a>";
            echo "<tr class='tab_bg_1'>";

            if ($canedit) {
               echo "<td width='10'>";
               Html::showMassiveActionCheckBox(__CLASS__, $data["id"]);
               echo "</td>";
            }
            echo "<td ".
                  ((isset($computer->fields['is_deleted']) && $computer->fields['is_deleted'])?"class='tab_bg_2_2'":"").
                 ">".$name."</td>";
            echo "<td>".Dropdown::getYesNo($computer->fields['is_dynamic'])."</td>";
            echo "<td>".Dropdown::getDropdownName("glpi_entities",
                                                               $computer->fields['entities_id']);
            echo "</td>";
            echo "<td>".
                   (isset($computer->fields["serial"])? "".$computer->fields["serial"]."" :"-")."</td>";
            echo "<td>".
                   (isset($computer->fields["otherserial"])? "".$computer->fields["otherserial"]."" :"-")."</td>";
            echo "</tr>";
         }
         echo $header_begin.$header_bottom.$header_end;

         echo "</table>";
         if ($canedit && $number) {
            $massiveactionparams['ontop'] = false;
            Html::showMassiveActions($massiveactionparams);
            Html::closeForm();
         }
         echo "</div>";
      }
      echo "</div>";
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
