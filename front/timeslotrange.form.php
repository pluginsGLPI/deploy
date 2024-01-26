<?php

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

if (isset($_POST['timeslot']) && !empty($_POST['timeslot'])) {
    $_POST['timeslot'] = json_decode($_POST['timeslot'], true);
    $_POST = TimeslotRange::cleanInput($_POST);
    foreach ($_POST as $timeslot) {
        TimeslotRange::chooseRequestType($timeslot);
    }
    Session::addMessageAfterRedirect(__('Range options saved', 'deploy'), true, INFO);
}
Html::back();
