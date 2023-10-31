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
    $uit->check(-1, CREATE, $_POST);
    if ($uit->add($_POST)) {
        if ($_SESSION['glpibackcreated']) {
            Html::redirect($uit->getLinkURL());
        }
    }
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