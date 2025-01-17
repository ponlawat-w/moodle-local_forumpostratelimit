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
 * Plugin library
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

const LOCAL_FORUMPOSTRATELIMIT_SECONDS = 1;
const LOCAL_FORUMPOSTRATELIMIT_MINUTES = 2;
const LOCAL_FORUMPOSTRATELIMIT_HOURS = 3;
const LOCAL_FORUMPOSTRATELIMIT_DAYS = 4;

function local_forumpostratelimit_getunitoptions() {
    return [
        LOCAL_FORUMPOSTRATELIMIT_SECONDS => get_string('seconds', 'local_forumpostratelimit'),
        LOCAL_FORUMPOSTRATELIMIT_MINUTES => get_string('minutes', 'local_forumpostratelimit'),
        LOCAL_FORUMPOSTRATELIMIT_HOURS => get_string('hours', 'local_forumpostratelimit'),
        LOCAL_FORUMPOSTRATELIMIT_DAYS => get_string('days', 'local_forumpostratelimit')
    ];
}

function local_forumpostratelimit_applytoform(\MoodleQuickForm $mform) {
    $mform->addElement('header', 'local_forumpostratelimit', get_string('postratelimit', 'local_forumpostratelimit'));

    $mform->addElement(
        'text',
        'local_forumpostratelimit_postratelimit',
        get_string('postratelimit', 'local_forumpostratelimit')
    );
    $mform->setType('local_forumpostratelimit_postratelimit', PARAM_INT);
    $mform->addHelpButton(
        'local_forumpostratelimit_postratelimit',
        'postratelimit',
        'local_forumpostratelimit'
    );

    $mform->addElement(
        'text',
        'local_forumpostratelimit_timespan',
        get_string('timespan', 'local_forumpostratelimit')
    );
    $mform->setType('local_forumpostratelimit_timespan', PARAM_FLOAT);
    $mform->addHelpButton(
        'local_forumpostratelimit_timespan',
        'timespan',
        'local_forumpostratelimit'
    );

    $mform->addElement(
        'select',
        'local_forumpostratelimit_timespanunit',
        get_string('timespanunit', 'local_forumpostratelimit'),
        local_forumpostratelimit_getunitoptions()
    );
    $mform->setType('local_forumpostratelimit_timespanunit', PARAM_INT);
    $mform->addHelpButton(
        'local_forumpostratelimit_timespanunit',
        'timespanunit',
        'local_forumpostratelimit'
    );
}

function local_forumpostratelimit_coursemodule_standard_elements(moodleform_mod $form, MoodleQuickForm $mform) {
    local_forumpostratelimit_applytoform($mform);
}
