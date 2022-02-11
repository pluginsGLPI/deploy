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

class PluginDeployRepository
{

    public function AddFileFromComputer(): PluginDeployRepository_File
    {
        $filename = $_FILES['file']['name']     ?? "";
        $tmp_name = $_FILES['file']['tmp_name'] ?? UPLOAD_ERR_NO_FILE;
        $error    = $_FILES['file']['error']    ?? "";
        $filesize = $_FILES['file']['size']     ?? 0;
        $mimetype = $_FILES['file']['type']     ?? "";

        if ($error != UPLOAD_ERR_OK) {
            switch ($error) {
                case UPLOAD_ERR_INI_SIZE:
                    $message = __('The uploaded file exceeds the upload_max_filesize directive in php.ini', 'deploy');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $message = __('The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form', 'deploy');
                    break;
                case UPLOAD_ERR_PARTIAL:
                    $message = __('The uploaded file was only partially uploaded', 'deploy');
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $message = __('No file was uploaded', 'deploy');
                    break;
                case UPLOAD_ERR_NO_TMP_DIR:
                    $message = __('Missing a temporary folder', 'deploy');
                    break;
                case UPLOAD_ERR_CANT_WRITE:
                    $message = __('Failed to write file to disk', 'deploy');
                    break;
                case UPLOAD_ERR_EXTENSION:
                    $message = __('File upload stopped by extension', 'deploy');
                    break;
                default:
                    $message = __('Unknown upload error', 'deploy');
                    break;
            }
            Session::addMessageAfterRedirect($message, false, ERROR);
            return false;
        }

        if (false !== ($file = new PluginDeployRepository_File(
            $filename,
            $tmp_name,
            (int) $filesize,
            $mimetype,
        ))) {
            $file->addToRepository();
        }
        return $file;
    }

    public function deleteFile(string $sha512 = ""): bool
    {
        $manifest_path = PLUGIN_DEPLOY_MANIFESTS_PATH . "/$sha512";

        // file already removed
        if (!file_exists($manifest_path)) {
            return true;
        }

        // remove parts
        $parts_sha512 = file($manifest_path);
        foreach ($parts_sha512 as $part_sha512) {
            $part_relative_dir = PluginDeployRepository_File::getRelativePathBySha512($part_sha512, false);
            $part_absolute_dir = PLUGIN_DEPLOY_PARTS_PATH . "/$part_relative_dir";
            $part_parent_dir   = dirname($part_absolute_dir);
            $part_path         = trim($part_absolute_dir . $part_sha512);

            // remove part
            unlink($part_path);

            // remove part directory if empty
            if (is_dir($part_absolute_dir)) {
                $nb_files_in_dir = count(scandir($part_absolute_dir)) - 2;
                if ($nb_files_in_dir === 0) {
                    rmdir($part_absolute_dir);
                }
            }

            // remove parent directory if empty
            if (is_dir($part_parent_dir)) {
                $nb_folder_in_dir = count(scandir($part_parent_dir)) - 2;
                if ($nb_folder_in_dir === 0) {
                    rmdir($part_parent_dir);
                }
            }

        }

        // remove manifest
        return unlink($manifest_path);
    }


    public static function install(Migration $migration)
    {
        if (!is_dir(PLUGIN_DEPLOY_REPOSITORY_PATH)) {
            mkdir(PLUGIN_DEPLOY_REPOSITORY_PATH, 0755, true);
        }
        if (!is_dir(PLUGIN_DEPLOY_PARTS_PATH)) {
            mkdir(PLUGIN_DEPLOY_PARTS_PATH, 0755, true);
        }
        if (!is_dir(PLUGIN_DEPLOY_MANIFESTS_PATH)) {
            mkdir(PLUGIN_DEPLOY_MANIFESTS_PATH, 0755, true);
        }
    }


    public static function uninstall(Migration $migration)
    {
        if (!is_dir(PLUGIN_DEPLOY_PARTS_PATH)) {
            rmdir(PLUGIN_DEPLOY_PARTS_PATH);
        }
        if (!is_dir(PLUGIN_DEPLOY_MANIFESTS_PATH)) {
            rmdir(PLUGIN_DEPLOY_MANIFESTS_PATH);
        }
        if (!is_dir(PLUGIN_DEPLOY_REPOSITORY_PATH)) {
            rmdir(PLUGIN_DEPLOY_REPOSITORY_PATH);
        }
    }
}
