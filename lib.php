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

function local_forumpostratelimit_applytoform(\MoodleQuickForm $mform, ?\stdClass $default = null, ?string $previousconfigstringkey = null) {
    $mform->addElement('header', 'local_forumpostratelimit', get_string('postratelimit', 'local_forumpostratelimit'));

    if (!is_null($previousconfigstringkey)) {
        $mform->addElement(
            'html',
            \core\output\html_writer::tag(
                'div',
                get_string($previousconfigstringkey, 'local_forumpostratelimit'),
                ['class' => 'alert alert-warning']
            )
        );
    }

    $mform->addElement('checkbox', 'local_forumpostratelimit_enabled', get_string('enabled', 'local_forumpostratelimit'));
    $mform->setDefault('local_forumpostratelimit_enabled', !is_null($default));

    $mform->addElement(
        'text',
        'local_forumpostratelimit_postratelimit',
        get_string('postratelimit', 'local_forumpostratelimit')
    );
    $mform->setType('local_forumpostratelimit_postratelimit', PARAM_INT);
    $mform->setDefault('local_forumpostratelimit_postratelimit', is_null($default) ? null : $default->postratelimit);
    $mform->disabledIf('local_forumpostratelimit_postratelimit', 'local_forumpostratelimit_enabled');
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
    $mform->setDefault('local_forumpostratelimit_timespan', is_null($default) ? null : $default->timespan);
    $mform->disabledIf('local_forumpostratelimit_timespan', 'local_forumpostratelimit_enabled');
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
    $mform->setDefault('local_forumpostratelimit_timespanunit', is_null($default) ? null : $default->timespanunit);
    $mform->disabledIf('local_forumpostratelimit_timespanunit', 'local_forumpostratelimit_enabled');
    $mform->addHelpButton(
        'local_forumpostratelimit_timespanunit',
        'timespanunit',
        'local_forumpostratelimit'
    );
}

function local_forumpostratelimit_coursemodule_standard_elements(moodleform_mod $form, MoodleQuickForm $mform) {
    global $DB;
    /** @var moodle_database $DB */
    $DB;
    $add = optional_param('add', null, PARAM_TEXT);
    if (!is_null($add) && $add != 'forum') {
        return;
    }
    $coursemodule = get_coursemodule_from_id('forum', optional_param('update', 0, PARAM_INT));
    $record = null;
    if (is_null($add) && !$coursemodule) {
        return;
    }
    $previousconfigstringkey = null;
    if ($coursemodule) {
        [$course] = get_course_and_cm_from_cmid($coursemodule);
        $context = core\context\module::instance($coursemodule->id);
        $record = $DB->get_record('local_forumpostratelimit_configs', ['context' => $context->instanceid]);
        if ($DB->get_record('local_forumpostratelimit_configs', ['context' => core\context\course::instance($course->id)->id])) {
            $previousconfigstringkey = 'configuredlevelcourse';
        }
    }
    if (is_null($previousconfigstringkey) && local_forumpostratelimit\checker::hassiteconfig()) {
        $previousconfigstringkey = 'configuredlevelsite';
    }
    local_forumpostratelimit_applytoform($mform, $record ? $record : null, $previousconfigstringkey);
}

function local_forumpostratelimit_coursemodule_edit_post_actions($moduleinfo, $course) {
    if ($moduleinfo->modulename != 'forum') {
        return $moduleinfo;
    }
    $data = new stdClass();
    $data->local_forumpostratelimit_postratelimit = optional_param('local_forumpostratelimit_postratelimit', null, PARAM_INT);
    $data->local_forumpostratelimit_timespan = optional_param('local_forumpostratelimit_timespan', null, PARAM_FLOAT);
    $data->local_forumpostratelimit_timespanunit = optional_param('local_forumpostratelimit_timespanunit', null, PARAM_INT);
    $formdata = new local_forumpostratelimit\formdata($data);
    $formdata->applytoforumid($moduleinfo->id);
    return $moduleinfo;
}
