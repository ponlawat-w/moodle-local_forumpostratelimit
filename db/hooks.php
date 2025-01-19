<?php

defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => core_course\hook\after_form_definition::class,
        'callback' => [local_forumpostratelimit\hookcallbacks::class, 'course_after_form_definition']
    ],
    [
        'hook' => core_course\hook\after_form_submission::class,
        'callback' => [local_forumpostratelimit\hookcallbacks::class, 'course_after_form_submission']
    ]
];
