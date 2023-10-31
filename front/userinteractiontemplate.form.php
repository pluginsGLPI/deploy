<?php

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$uit = new UserInteractionTemplate();

if (isset($_POST["add"])) {
    $_POST['json'] = $uit->constructJsonData($_POST);
    $uit->check(-1, CREATE, $_POST);
    if ($uit->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($uit->getLinkURL());
        }
    }
    Html::back();
} elseif (isset($_POST["update"])) {
    $uit->check($_POST['id'], UPDATE);
    if (isset($_POST['behavior'])) {
        $_POST['json'] = UserInteractionTemplate_Behavior::updateJsonWithBehavior($_POST);
    } else {
        $_POST['json'] = $uit->constructJsonData($_POST);
    }
    $uit->update($_POST);
    Html::back();
} else {
    Html::requireJs('sortable');
    Html::header(
        UserInteractionTemplate::getTypeName(Session::getPluralNumber()),
        '',
        'tools',
        'glpiplugin\deploy\menu',
        'userinteractiontemplate'
    );

    //show question form to add
    $uit->display([
        'id' => (int) $_GET["id"],
    ]);
    Html::footer();
}
