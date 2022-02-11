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

class PluginDeployRepository_File
{
    private $max_part_size = 1024 * 1024;

    private $name         = "";
    private $path         = "";
    private $size         = "";
    private $sha512       = "";
    private $short_sha512 = "";
    private $parts_sha512 = [];


    public function __construct(string $name = "", string $path = "", int $size = 0, string $mimetype = "")
    {
        $this->name         = $name;
        $this->path         = $path;
        $this->size         = $size;
        $this->mimetype     = $mimetype;
        $this->sha512       = hash_file('sha512', $path);
        $this->short_sha512 = substr($this->sha512, 0, 8);
    }

    public function addToRepository(): bool {
        if (!$this->isFileExists()) {
            if (!$this->saveParts() || !$this->savemanifest()) {
                return false;
            }
        }

        return true;
    }

    public function getDefinition(): array {
        return [
            'filename'    => $this->name,
            'filesize'    => $this->size,
            'mimetype'    => $this->mimetype,
            'sha512'      => $this->sha512,
            'shortsha512' => $this->short_sha512,
        ];
    }

    private function isFileExists(): bool {
        // check a filename with sha512 exist in manifest path
        if (!file_exists(PLUGIN_DEPLOY_MANIFESTS_PATH . $this->sha512)) {
            return false;
        }

        // check each parts saved in manifest file
        $all_parts_ok = true;
        $nb_parts = 0;
        $manifest = fopen(PLUGIN_DEPLOY_MANIFESTS_PATH . $this->sha512, "r");
        $this->parts_sha512 = [];
        while (($part_sha512 = fgets($manifest)) !== false) {
            $nb_parts++;
            $this->parts_sha512[] = $part_sha512;
            $part_path = $this->getRelativePathBySha512($part_sha512);

            //Check part exists
            if (!file_exists(PLUGIN_DEPLOY_PARTS_PATH . $part_path)) {
                $all_parts_ok = false;
                break;
            }
        }

        // we must have at least one part registered in manifest and its corresponding file exists
        return $nb_parts > 0 && $all_parts_ok;
    }


    private function saveParts(): bool {
        if (!($file_handle = fopen($this->path, 'rb'))) {
            return false;
        }

        $tmp_part_path = tempnam(GLPI_TMP_DIR, 'plugin_deploy_part_');
        $this->parts_sha512 = [];
        do {
            if (feof($file_handle) || filesize($tmp_part_path) >= $this->max_part_size) {
                $part_sha512 = $this->saveOnePart($tmp_part_path);
                unlink($tmp_part_path);

                $this->parts_sha512[] = $part_sha512;
            }

            if (feof($file_handle)) {
                break;
            }

            $data        = fread($file_handle, $this->max_part_size);
            $part_handle = gzopen($tmp_part_path, 'a');
            gzwrite($part_handle, $data, strlen($data));
            gzclose($part_handle);
        } while (1);

        return true;
    }


    public function saveOnePart(string $tmp_part_path = ""): string
    {
        $part_sha512  = hash_file('sha512', $tmp_part_path);
        $part_basedir = PLUGIN_DEPLOY_PARTS_PATH . $this->getRelativePathBySha512($part_sha512, false);
        $part_path    = $part_basedir . '/' . $part_sha512;

        if (!file_exists($part_path)) {
            mkdir($part_basedir, 0777, true);
            copy($tmp_part_path, $part_path);
        }

        return $part_sha512;
    }


    public function saveManifest(): bool
    {
        $handle = fopen(
            PLUGIN_DEPLOY_MANIFESTS_PATH . $this->sha512,
            "w+"
        );
        if ($handle) {
            foreach ($this->parts_sha512 as $part_sha512) {
                fwrite($handle, $part_sha512 . "\n");
            }
            fclose($handle);
        }

        return true;
    }


    private function getRelativePathBySha512(string $sha512 = "", bool $with_filename = true): string
    {
        $first  = substr($sha512, 0, 1);
        $second = substr($sha512, 0, 2);

        return "$first/$second/" . ($with_filename ? trim($sha512, "\n") : "");
    }
}
