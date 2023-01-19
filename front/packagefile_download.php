<?php
/*
 -------------------------------------------------------------------------
 package_file plugin for GLPI
 Copyright (C) 2022 by the package_file Development Team.

 https://github.com/pluginsGLPI/package_file
 -------------------------------------------------------------------------

 LICENSE

 This file is part of package_file.

 package_file is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 package_file is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with package_file. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

use GlpiPlugin\Deploy\Package_File;
use GlpiPlugin\Deploy\Repository_File;

include("../../../inc/includes.php");

Session::checkRight("entity", UPDATE);

session_write_close(); // unlock session to ensure GLPI is still usable while huge file downloads is done in background

$file_id = (int)($_GET['file_id'] ?? 0);

$package_file = new Package_File();
if ($file_id > 0 && $package_file->getFromDB($file_id)) {

    $mimetype = $package_file->fields['mimetype'];
    $filesize = $package_file->fields['filesize'];
    $filename = $package_file->fields['filename'];
    $sha512 = $package_file->fields['sha512'];

    $repository = new Repository_File(
        $filename,
        "",
        $filesize,
        $mimetype,
        $sha512
    );

    if ($repository->isFileExists()) {
        //get all repository file path
        $part_path = $repository->getFilePath();
        if ($filename != '' && $part_path !== false && count($part_path)) {
            // Make sure there is nothing in the output buffer (In case stuff was added by core or misbehaving plugin).
            // If there is any extra data, the sent file will be corrupted.
            // 1. Turn off any extra buffering level. Keep one buffering level if PHP output_buffering directive is not "off".
            $ob_config = ini_get('output_buffering');
            $max_buffering_level = $ob_config !== false && (strtolower($ob_config) === 'on' || (is_numeric($ob_config) && (int)$ob_config > 0))
                ? 1
                : 0;
            while (ob_get_level() > $max_buffering_level) {
                ob_end_clean();
            }
            // 2. Clean any buffered output in remaining level (output_buffering="on" case).
            if (ob_get_level() > 0) {
                ob_clean();
            }

            header('Content-Description: File Transfer');
            header('Content-Type: ' . ($mimetype ?: 'application/octet-stream'));
            header('Content-Disposition: attachment; filename=' . basename($filename));
            header('Content-Transfer-Encoding: binary');
            header_remove('Pragma');
            header('Cache-Control: no-store');
            header('Content-Length: ' . $filesize);

            Toolbox::logDebug($part_path);
            foreach ($part_path as $key => $path) {
                readgzfile($path);
            }
        } else {
            Html::displayErrorAndDie(__('An error occurs', 'deploy'), true);
        }
    } else {
        Html::displayErrorAndDie(__('File not found', 'deploy'), true); // Not found
    }
} else {
    Html::displayErrorAndDie(__('File not found', 'deploy'), true); // Not found
}
