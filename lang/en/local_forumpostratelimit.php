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
 * Plugin strings are defined here.
 *
 * @package     local_forumpostratelimit
 * @category    string
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['configuredlevelcourse'] = 'Post rate is limited in course level. Enabling these settings will override the current configuration.';
$string['configuredlevelsite'] = 'Post rate is globally limited in site level. Enabling these settings will override the current configuration.';
$string['days'] = 'Days';
$string['enabled'] = 'Enabled';
$string['hours'] = 'Hours';
$string['limitexceeded'] = 'You have exceeded post limit of {$a->limit} post(s) within {$a->timespan} {$a->timespanunit}(s).';
$string['minutes'] = 'Minutes';
$string['pluginname'] = 'Forum Post Rate Limit';
$string['postratelimit'] = 'Post Rate Limit';
$string['postratelimit_help'] = 'Maximum number of posts a user can make within the specified timespan.';
$string['privacy:metadata'] = 'This plugin does not store privacy data.';
$string['seconds'] = 'Seconds';
$string['timespan'] = 'Time Span';
$string['timespan_help'] = 'Duration of time to apply the post rate limit.';
$string['timespanunit'] = 'Time Span Unit';
$string['timespanunit_1'] = 'second';
$string['timespanunit_2'] = 'minute';
$string['timespanunit_3'] = 'hour';
$string['timespanunit_4'] = 'day';
$string['timespanunit_help'] = 'Unit of the time duration to apply the post rate limit.';
