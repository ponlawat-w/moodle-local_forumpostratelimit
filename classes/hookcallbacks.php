<?php

namespace local_forumpostratelimit;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../classes/formdata.php');

class hookcallbacks {
    public static function course_after_form_definition(\core_course\hook\after_form_definition $payload) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        $record = null;
        $course = $payload->formwrapper->get_course();
        if (isset($course->id)) {
            $context = \core\context\course::instance($course->id);
            $record = $DB->get_record('local_forumpostratelimit_configs', ['context' => $context->id]);
        }
        local_forumpostratelimit_applytoform($payload->mform, $record ? $record : null);
    }

    public static function course_after_form_submission(\core_course\hook\after_form_submission $payload) {
        $data = $payload->get_data();
        $formdata = new \local_forumpostratelimit\formdata($data);
        $formdata->applytocourseid($data->id);
    }
}
