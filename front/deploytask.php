<?php

namespace GlpiPlugin\Deploy;

use Session;

include('../../../inc/includes.php');
$SECURITY_STRATEGY = 'no_check';

// Action executed by agent
echo Task::collectTask($_GET);
