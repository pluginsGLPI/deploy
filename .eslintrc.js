/**
 * -------------------------------------------------------------------------
 * Deploy plugin for GLPI
 * -------------------------------------------------------------------------
 *
 * LICENSE
 *
 * This file is part of Deploy.
 *
 * Deploy is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * Deploy is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Deploy. If not, see <http://www.gnu.org/licenses/>.
 * -------------------------------------------------------------------------
 * @copyright Copyright (C) 2022-2024 by Deploy plugin team.
 * @license   GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link      https://github.com/pluginsGLPI/deploy
 * -------------------------------------------------------------------------
 */

module.exports = {
    "root": true,
    "ignorePatterns": [
        "/lib/*",
        "/vendor/*",
    ],
    "env": {
        "browser": true,
        "es6": true,
        "jquery": true,
    },
    "extends": "eslint:recommended",
    "globals": {
        "CFG_GLPI": true,
        "GLPI_PLUGINS_PATH": true,
        "__": true,
        "_n": true,
        "_x": true,
        "_nx": true
    },
    "parserOptions": {
        "ecmaVersion": 8,
    },
    "plugins": [
        "@stylistic/js",
    ],
    "rules": {
        "no-console": ["error", {"allow": ["warn", "error"]}],
        "no-unused-vars": ["error", {"vars": "local"}],
        "@stylistic/js/eol-last": ["error", "always"],
        "@stylistic/js/indent": ["error", 4],
        "@stylistic/js/linebreak-style": ["error", "unix"],
        "@stylistic/js/semi": ["error", "always"],
    },
    "overrides": [
        {
            "files": [".eslintrc.js", ".stylelintrc.js"],
            "env": {
                "node": true
            }
        },
    ],
};
