<?php

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$trange = new TimeslotRange();

if (isset($_POST['timeslot']) && !empty($_POST['timeslot'])) {
    $_POST['timeslot'] = json_decode($_POST['timeslot'], true);
    TimeslotRange::cleanOldData($_POST);
    $_POST = TimeslotRange::cleanInput($_POST);
    foreach ($_POST as $timeslot) {
        foreach ($timeslot as $range) {
            if ($range['is_enable'] == true) {
                $trange->add($range);
            }
        }
    }
    Session::addMessageAfterRedirect(__('Range options saved', 'deploy'), true, INFO);
}
Html::back();
