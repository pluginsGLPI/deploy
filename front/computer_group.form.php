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

use Glpi\Event;
use GlpiPlugin\Deploy\Computer_Group;
use GlpiPlugin\Deploy\Computer_Group_Dynamic;
use GlpiPlugin\Deploy\Computer_Group_Static;

include ('../../../inc/includes.php');

Session::checkRight("computer_group", READ);

if (!isset($_GET["id"])) {
   $_GET["id"] = "";
}

if (!isset($_GET["withtemplate"])) {
   $_GET["withtemplate"] = "";
}

$computergroup = new Computer_Group();
$computergroupstatic = new Computer_Group_Static();
$computergroup_dynamic = new Computer_Group_Dynamic();

//Add a new computergroup
if (isset($_POST["add"])) {
   $computergroup->check(-1, CREATE, $_POST);
   if ($newID = $computergroup->add($_POST)) {
      Event::log($newID, "Computer_Group", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $_POST["name"]));

      if ($_SESSION['glpibackcreated']) {
         Html::redirect($computergroup->getLinkURL());
      }
   }
   Html::back();

   // purge a computergroup
}if (isset($_POST["add_staticcomputer"])) {

   if (!$_POST['computers_id']){
      Session::addMessageAfterRedirect(__('Please select a computer'), false, ERROR);
      Html::back();
   }

   $computergroupstatic->check(-1, CREATE, $_POST);
   if ($newID = $computergroupstatic->add($_POST)) {
      Event::log($newID, "Computer_Group_Static", 4, "inventory",
                 sprintf(__('%1$s adds the item %2$s'), $_SESSION["glpiname"], $computergroupstatic::getTypeName(0)));

      if ($_SESSION['glpibackcreated']) {
         $computergroup->getFromDB($_POST['plugin_deploy_computers_groups_id']);
         Html::redirect($computergroup->getLinkURL());
      }
   }
   Html::back();

   // purge a computergroup
} else if (isset($_POST["purge"])) {
   $computergroup->check($_POST['id'], PURGE);
   if ($computergroup->delete($_POST, 1)) {
      Event::log($_POST["id"], "Computer_Group", 4, "inventory",
                 //TRANS: %s is the user login
                 sprintf(__('%s purges an item'), $_SESSION["glpiname"]));
   }
   $computergroup->redirectToList();

   //update a computergroup
} else if (isset($_POST["update"])) {
   $computergroup->check($_POST['id'], UPDATE);
   $computergroup->update($_POST);
   Event::log($_POST["id"], "Computer_Group", 4, "inventory",
              //TRANS: %s is the user login
              sprintf(__('%s updates an item'), $_SESSION["glpiname"]));
   Html::back();

} else {//print computergroup information
   //save search parameters for dynamic group
   if (isset($_GET["save"])) {
      $input = ['plugin_deploy_computers_groups_id' => $_GET['plugin_deploy_computers_groups_id']];
      $search = serialize(['is_deleted' => isset($_GET['is_deleted']) ? $_GET['is_deleted'] : 0 ,
                           'as_map' =>  isset($_GET['as_map']) ? $_GET['as_map'] : 0,
                           'criteria'     => $_GET['criteria'],
                           'metacriteria' => isset($_GET['metacriteria']) ? $_GET['metacriteria'] : []]);

      if (!$computergroup_dynamic->getFromDBByCrit($input)) {
         $input['search'] = $search;
         $computergroup_dynamic->add($input);
      } else  {
         $input = $computergroup_dynamic->fields;
         $input['search'] = $search;
         $computergroup_dynamic->update($input);
      }
   }else if (isset($_GET["reset"])) {
      $computergroup_dynamic->deleteByCriteria(["plugin_deploy_computers_groups_id" => $_GET['id']]);
   }


   Html::header(
      Computer_Group::getTypeName(Session::getPluralNumber()),
      '',
      'tools',
      'glpiplugin\deploy\menu',
      'computer_group'
  );

   //show computergroup form to add
   $computergroup->display([
      'id' => (int) $_GET["id"],
   ]);

   Html::footer();
}
