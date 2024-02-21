<?php

namespace GlpiPlugin\Deploy;

use Html;
use Session;

include('../../../inc/includes.php');

Session::checkRight("entity", UPDATE);

if (!isset($_GET["id"])) {
    $_GET["id"] = "";
}

$package_timeslot = new PackageTimeslot();

if (isset($_POST['time_end']) && isset($_POST['time_start'])) {
    $_POST['weekday'] = PackageTimeslot::getDayNumber($_POST['weekday']);
    PackageTimeslot::chooseUpdateOrAdd($_POST);
}
Html::back();
