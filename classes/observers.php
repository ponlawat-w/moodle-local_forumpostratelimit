<?php

namespace local_forumpostratelimit;

class observers {
    public static function forumpostcreated(\mod_forum\event\post_created $event) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        $data = $event->get_data();
        $forumid = $data['other']['forumid'];
        $checker = checker::fromforumid($forumid);
        if ($checker->limitexceeded(null, $data['objectid'])) {
            $discussion = $DB->get_record('forum_discussions', ['id' => $data['other']['discussionid']], '*', MUST_EXIST);
            $forum = $DB->get_record('forum', ['id' => $discussion->forum], '*', MUST_EXIST);
            $post = $DB->get_record('forum_posts', ['id' => $data['objectid']], '*', MUST_EXIST);
            forum_delete_post(
                $post,
                true,
                get_course($discussion->course),
                get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST),
                $forum
            );
        }
    }

    public static function forumdiscussioncreated(\mod_forum\event\discussion_created $event) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        $data = $event->get_data();
        $forumid = $data['other']['forumid'];
        $checker = checker::fromforumid($forumid);
        $discussion = $DB->get_record('forum_discussions', ['id' => $data['objectid']], '*', MUST_EXIST);
        if ($checker->limitexceeded(null, $discussion->firstpost)) {
            $forum = $DB->get_record('forum', ['id' => $discussion->forum], '*', MUST_EXIST);
            forum_delete_discussion(
                $discussion,
                false,
                get_course($discussion->course),
                get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST),
                $forum
            );
        }
    }
}
