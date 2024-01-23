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
use DbUtils;
use Html;
use Migration;
use Profile as GlobalProfile;
use ProfileRight;

class Profile extends GlobalProfile {

   public static $rightname = 'profile';

   static function getTypeName($nb = 0) {
      return __('Deploy', 'deploy');
   }


   static function getAllRights($all = false) {
      $rights = [
         ['itemtype' => Computer_Group::getType(),
               'label'    => Computer_Group::getTypeName(),
               'field'    => 'computer_group'
         ]
      ];
      return $rights;
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {

      if ($item->getType() == GlobalProfile::class) {
         return self::createTabEntry(self::getTypeName());
      }
      return '';
   }


   static function displayTabContentForItem(CommonGLPI $item, $tabnum = 1, $withtemplate = 0) {

      if ($item instanceof GlobalProfile
          && $item->getField('id')) {
         return self::showForProfile($item->getID());
      }
      return true;
   }


   static function showForProfile($profiles_id = 0) {
      $canupdate = self::canUpdate();
      $profile = new GlobalProfile();
      $profile->getFromDB($profiles_id);
      echo "<div class='firstbloc'>";
      echo "<form method='post' action='".$profile->getFormURL()."'>";

      $rights = self::getAllRights();
      $profile->displayRightsChoiceMatrix($rights, array(
         'canedit'       => $canupdate,
         'title'         => self::getTypeName(),
      ));

      if ($canupdate) {
         echo "<div class='center'>";
         echo Html::hidden('id', array('value' => $profiles_id));
         echo Html::submit(_sx('button', 'Save'), array('name' => 'update'));
         echo "</div>\n";
         Html::closeForm();

         echo "</div>";
      }
   }


   /**
    * @param $ID
    */
    static function createFirstAccess($ID) {
      self::addDefaultProfileInfos($ID, ['computer_group' => PURGE + CREATE + UPDATE + READ ], true);
   }

   /**
    * @param      $profiles_id
    * @param      $rights
    * @param bool $drop_existing
    *
    * @internal param $profile
    */
   static function addDefaultProfileInfos($profiles_id, $rights, $drop_existing = false) {

      $profileRight = new ProfileRight();
      $dbu = new DbUtils();
      foreach ($rights as $right => $value) {
         if ($dbu->countElementsInTable('glpi_profilerights',
                                        ["profiles_id" => $profiles_id, "name" => $right]) && $drop_existing) {
            $profileRight->deleteByCriteria(['profiles_id' => $profiles_id, 'name' => $right]);
         }
         if (!$dbu->countElementsInTable('glpi_profilerights',
                                         ["profiles_id" => $profiles_id, "name" => $right])) {
            $plugin_right['profiles_id'] = $profiles_id;
            $plugin_right['name']        = $right;
            $plugin_right['rights']      = $value;
            $profileRight->add($plugin_right);

            //Add right to the current session
            $_SESSION['glpiactiveprofile'][$right] = $value;
         }
      }
   }

   public static function install(Migration $migration) { 
      foreach (Profile::getAllRights() as $right) {
         ProfileRight::addProfileRights([$right['field']]);
      }
      self::createFirstAccess($_SESSION['glpiactiveprofile']['id']);
      return true;
   }

   public static function uninstall(Migration $migration) {
      foreach (Profile::getAllRights() as $right) {
         ProfileRight::deleteProfileRights([$right['field']]);
      }
   }
}
