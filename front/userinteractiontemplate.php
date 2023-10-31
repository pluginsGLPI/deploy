<?php
namespace GlpiPlugin\Deploy;

use Html;
use Search;
use Session;

include('../../../inc/includes.php');

Session::checkRight("dashboard", UPDATE);

Html::header(
    Package::getTypeName(Session::getPluralNumber()),
    '',
    'tools',
    'glpiplugin\deploy\menu',
    'userinteractiontemplate'
);

$uit = new UserInteractionTemplate();
if ($uit->canView()) {
    Search::show(UserInteractionTemplate::class);
} else {
    Html::displayRightError();
}

Html::footer();
