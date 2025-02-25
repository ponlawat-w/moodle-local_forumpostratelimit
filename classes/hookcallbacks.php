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
                $PAGE->requires->js_call_amd('local_forumpostratelimit/view', 'disableforumview', [$checker->getlimitexceededstring()]);
            }
        } else if ($url->get_path() == '/mod/forum/discuss.php') {
            $discussionid = required_param('d', PARAM_INT);
            $discussion = $DB->get_record('forum_discussions', ['id' => $discussionid], '*', MUST_EXIST);
            $checker = checker::fromforumid($discussion->forum);
            if ($checker->limitexceeded()) {
                $PAGE->requires->js_call_amd('local_forumpostratelimit/view', 'disableforumdiscuss', [$checker->getlimitexceededstring()]);
            }
        } else if ($url->get_path() == '/mod/forum/post.php') {
            $forum = optional_param('forum', 0, PARAM_INT);
            $reply = optional_param('reply', 0, PARAM_INT);
            $checker = null;
            if ($forum) {
                $checker = checker::fromforumid($forum);
            } else if ($reply) {
                $discussion = $DB->get_record_sql('SELECT d.* FROM {forum_discussions} d JOIN {forum_posts} p ON d.id = p.discussion WHERE p.id = ?', [$reply]);
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
