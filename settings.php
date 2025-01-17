<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Plugin administration pages are defined here.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/lib.php');

if ($hassiteconfig) {
    /** @var admin_root $ADMIN */
    $ADMIN;
    /** @var admin_settingpage $modsettingforum */
    $modsettingforum = $ADMIN->locate('modsettingforum');
    $modsettingforum->add(new admin_setting_heading(
        'local_forumpostratelimit',
        get_string('pluginname', 'local_forumpostratelimit'),
        ''
    ));
    $modsettingforum->add(new admin_setting_configtext(
        'local_forumpostratelimit/limit',
        get_string('postratelimit', 'local_forumpostratelimit'),
        get_string('postratelimit_help', 'local_forumpostratelimit'),
        null,
        PARAM_INT
    ));
    $modsettingforum->add(new admin_setting_configtext(
        'local_forumpostratelimit/timespan',
        get_string('timespan', 'local_forumpostratelimit'),
        get_string('timespan_help', 'local_forumpostratelimit'),
        null,
        PARAM_FLOAT
    ));
    $modsettingforum->add(new admin_setting_configselect(
        'local_forumpostratelimit/timespanunit',
        get_string('timespanunit', 'local_forumpostratelimit'),
        get_string('timespanunit_help', 'local_forumpostratelimit'),
        null,
        local_forumpostratelimit_getunitoptions()
    ));
}
