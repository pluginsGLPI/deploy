<?php

namespace GlpiPlugin\Deploy;

use CommonITILObject;
use Glpi\Http\Response;
use TaskCategory;

include("../../../inc/includes.php");

\Session::checkLoginUser();

// Mandatory parameter: tasktemplates_id
$alerttemplates_id = $_POST['alerttemplate_id'] ?? null;
if ($alerttemplates_id === null) {
    Response::sendError(400, "Missing or invalid parameter: 'alerttemplates_id'");
} else if ($alerttemplates_id == 0) {
   // Reset form
    echo json_encode([
        'content' => ""
    ]);
    die;
}

// Load task template
$template = new UserInteractionTemplate();
if (!$template->getFromDB($alerttemplates_id)) {
    Response::sendError(400, "Unable to load template: $tasktemplates_id");
}

// Return json response with the template fields
echo json_encode($template->fields);
