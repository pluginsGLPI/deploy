<?php

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$package_timeslot = new Package_Timeslot();

if (isset($_POST['time_end']) && isset($_POST['time_start'])) {
    $_POST['weekday'] = Package_Timeslot::getDayNumber($_POST['weekday']);
    Package_Timeslot::chooseUpdateOrAdd($_POST);
}
Html::back();
