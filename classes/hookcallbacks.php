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
 * Hook callbacks
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_forumpostratelimit;

defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/../lib.php');
require_once(__DIR__ . '/../classes/formdata.php');

/**
 * Hook callbacks
 */
class hookcallbacks {
    /**
     * Add configuration fields to course settings.
     * @param \core_course\hook\after_form_definition $payload
     * @return void
     */
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
        $previousconfigstringkey = \local_forumpostratelimit\checker::hassiteconfig() ? 'configuredlevelsite' : null;
        local_forumpostratelimit_applytoform($payload->mform, $record ? $record : null, $previousconfigstringkey);
    }

    /**
     * Apply configuration value to course.
     * @param \core_course\hook\after_form_submission $payload
     * @return void
     */
    public static function course_after_form_submission(\core_course\hook\after_form_submission $payload) {
        $data = $payload->get_data();
        $formdata = new \local_forumpostratelimit\formdata($data);
        $formdata->applytocourseid($data->id);
    }

    /**
     * In forum pages, where there are any form to create posts, disable the form if the rate limit has been exceeded.
     * @param \core\hook\output\before_http_headers $payload
     * @return void
     */
    public static function output_before_http_headers(\core\hook\output\before_http_headers $payload) {
        global $DB, $PAGE;
        /** @var \moodle_database $DB */
        $DB;
        /** @var \moodle_page $PAGE */
        $PAGE;
        /** @var \core\url $url */
        $url = $PAGE->url;
        if ($url->get_path() == '/mod/forum/view.php') {
            $forumid = optional_param('f', null, PARAM_INT);
            $checker = $forumid ? checker::fromforumid($forumid) : checker::fromcmid(required_param('id', PARAM_INT));
            if ($checker->limitexceeded()) {
                $PAGE->requires->js_call_amd(
                    'local_forumpostratelimit/view',
                    'disableforumview',
                    [$checker->getlimitexceededstring()]
                );
            }
        } else if ($url->get_path() == '/mod/forum/discuss.php') {
            $discussionid = required_param('d', PARAM_INT);
            $discussion = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);
            $checker = checker::fromforumid($discussion->forum);
            if ($checker->limitexceeded()) {
                $PAGE->requires->js_call_amd(
                    'local_forumpostratelimit/view',
                    'disableforumdiscuss',
                    [$checker->getlimitexceededstring()]
                );
            }
        } else if ($url->get_path() == '/mod/forum/post.php') {
            $forum = optional_param('forum', 0, PARAM_INT);
            $reply = optional_param('reply', 0, PARAM_INT);
            $checker = null;
            if ($forum) {
                $checker = checker::fromforumid($forum);
            } else if ($reply) {
                $discussion = $DB->get_record_sql(
                    'SELECT d.* FROM {forum_discussions} d JOIN {forum_posts} p ON d.id = p.discussion WHERE p.id = ?',
                    [$reply]
                );
                $checker = checker::fromforumid($discussion->forum);
            }
            if (!is_null($checker) && $checker->limitexceeded()) {
                throw new \core\exception\moodle_exception(
                    $checker->getlimitexceededstring(),
                    'local_forumpostratelimit'
                );
            }
        }
    }
}
