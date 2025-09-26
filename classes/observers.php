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
 * Event observers.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_forumpostratelimit;

/**
 * Event observers.
 */
class observers {
    /**
     * Delete the newly created post if it exceeds the configured rate limit.
     * @param \mod_forum\event\post_created $event
     * @return void
     */
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

    /**
     * Delete the newly created discussion if it exceeds the configured rate limit.
     * @param \mod_forum\event\discussion_created $event
     * @return void
     */
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
