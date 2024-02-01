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

require('@fullcalendar/core');

const CustomViewConfig = {
    classNames: [ 'custom-view' ],
    content: function(props) {
        let segs = sliceEvents(props, true); // allDay=true
        let html =
        '<div class="view-title">' +
            props.dateProfile.currentRange.start.toUTCString() +
        '</div>' +
        '<div class="view-events">' +
            segs.length + ' events' +
        '</div>'

        return { html: html }
    }
}

export default createPlugin({
    views: {
        custom: CustomViewConfig
    }
});