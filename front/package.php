<?php
/*
 -------------------------------------------------------------------------
 Deploy plugin for GLPI
 Copyright (C) 2022 by the Deploy Development Team.

 https://github.com/pluginsGLPI/deploy
 -------------------------------------------------------------------------

 LICENSE

 This file is part of Deploy.

 Deploy is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Deploy is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

namespace GlpiPlugin\Deploy;

use Html;
use Search;
use Session;

include ('../../../inc/includes.php');

Session::checkRight("dashboard", UPDATE);

Html::header(
    Package::getTypeName(Session::getPluralNumber()),
   '',
   'tools',
   'glpiplugin\deploy\menu',
   'package'
);

$package = new Package();
if ($package->canView()) {
   Search::show('GlpiPlugin\Deploy\Package');
} else {
   Html::displayRightError();
}

Html::footer();
