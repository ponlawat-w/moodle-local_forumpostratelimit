<?php

namespace local_forumpostratelimit;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');

class hookcallbacks {
    public static function course_after_form_definition(\core_course\hook\after_form_definition $event) {
        local_forumpostratelimit_applytoform($event->mform);
    }
}
