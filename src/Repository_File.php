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

class Repository_File
{
    private $max_part_size = 1024 * 1024;

    private $name         = "";
    private $path         = "";
    private $size         = "";
    private $mimetype     = "";
    private $sha512       = "";
    private $short_sha512 = "";
    private $parts_sha512 = [];


    public function __construct(string $name = "", string $path = "", int $size = 0, string $mimetype = "", string $sha512 = "")
    {
        $this->name         = $name;
        $this->path         = $path;
        $this->size         = $size;
        $this->mimetype     = $mimetype;

        if (strlen($path) > 0) {
            $this->sha512       = hash_file('sha512', $path);
            $this->short_sha512 = substr($this->sha512, 0, 8);
        } else if (strlen($sha512) > 0) {
            $this->sha512       = $sha512;
            $this->short_sha512 = substr($this->sha512, 0, 8);
        } else {
            trigger_error(
                'Repository_File __construct expects to get \'path\' or \'sha512\' arguments, both are missing !!',
                E_USER_WARNING
            );
        }
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

    public function isFileExists(): bool {
        // check a filename with sha512 exist in manifest path
        if (!file_exists(PLUGIN_DEPLOY_MANIFESTS_PATH . "/{$this->sha512}")) {
            return false;
        }

        // check each parts saved in manifest file
        $all_parts_ok = true;
        $nb_parts = 0;
        $manifest = fopen(PLUGIN_DEPLOY_MANIFESTS_PATH . "/{$this->sha512}", "r");
        $this->parts_sha512 = [];
        while (($part_sha512 = fgets($manifest)) !== false) {
            $nb_parts++;
            $this->parts_sha512[] = $part_sha512;
            $part_path = self::getRelativePathBySha512($part_sha512);

            //Check part exists
            if (!file_exists(PLUGIN_DEPLOY_PARTS_PATH . "/$part_path")) {
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

        $tmp_part_path = tempnam(GLPI_TMP_DIR, '/plugin_deploy_part_');
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
        $part_basedir = PLUGIN_DEPLOY_PARTS_PATH . "/" . $this->getRelativePathBySha512($part_sha512, false);
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
            PLUGIN_DEPLOY_MANIFESTS_PATH . "/{$this->sha512}",
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


    public static function getRelativePathBySha512(string $sha512 = "", bool $with_filename = true): string
    {
        $first  = substr($sha512, 0, 1);
        $second = substr($sha512, 0, 2);

        return "$first/$second/" . ($with_filename ? trim($sha512, "\n") : "");
    }


    public function getFilePath()
    {
        $path = [];
        $manifest = fopen(PLUGIN_DEPLOY_MANIFESTS_PATH . "/{$this->sha512}", "r");
        $this->parts_sha512 = [];
        while (($part_sha512 = fgets($manifest)) !== false) {
            $this->parts_sha512[] = $part_sha512;
            $path[] = PLUGIN_DEPLOY_PARTS_PATH . "/" . self::getRelativePathBySha512($part_sha512);
        }

        return $path;
    }
}
