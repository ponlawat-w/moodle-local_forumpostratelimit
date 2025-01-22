<?php
defined('MOODLE_INTERNAL') or die();

$observers = [
    [
        'eventname' => '\mod_forum\event\post_created',
        'callback' => '\local_forumpostratelimit\observers::forumpostcreated'
    ],
    [
        'eventname' => '\mod_forum\event\discussion_created',
        'callback' => '\local_forumpostratelimit\observers::forumdiscussioncreated'
    ]
];
