<?php

/**
 * -------------------------------------------------------------------------
 * deploy plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * MIT License
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022 by the deploy plugin team.
 * @license   MIT https://opensource.org/licenses/mit-license.php
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
 */

namespace GlpiPlugin\Deploy;

use Migration;
use Session;

class Repository
{

    public function AddFileFromComputer(): Repository_File
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

        if (false !== ($file = new Repository_File(
            $filename,
            $tmp_name,
            (int) $filesize,
            $mimetype,
        ))) {
            $file->addToRepository();
        }
        return $file;
    }

    public function addFileFromServer(string $path = ""): Repository_File
    {
        $tmp_name = GLPI_UPLOAD_DIR.$path;
        if (false !== ($file = new Repository_File(
            basename($tmp_name),
            $tmp_name,
            filesize($tmp_name),
            mime_content_type($tmp_name),
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
            $part_relative_dir = Repository_File::getRelativePathBySha512($part_sha512, false);
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
