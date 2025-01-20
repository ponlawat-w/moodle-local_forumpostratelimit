<?php

namespace local_forumpostratelimit;

require_once(__DIR__ . '/../lib.php');

class checker {
    private $postratelimit = null;
    private $timespan = null;
    private $timespanunit = null;

    public function __construct($moduleid) {
        $context = \core\context\module::instance($moduleid);
        $moduleconfig = self::getconfig($context->id);
        if ($moduleconfig) {
            return $this->assignrecord($moduleconfig);
        }
        $courseconfig = self::getconfig($context->get_course_context(true)->id);
        if ($courseconfig) {
            return $this->assignrecord($courseconfig);
        }
        $this->assignrecord(self::getsiteconfig());
    }

    public function limitexceeded($userid = null) {
        global $USER, $DB;
        /** @var \moodle_database $DB */
        $DB;

        if (is_null($this->timespan) || $this->timespan <= 0) {
            return false;
        }

        $userid = is_null($userid) ? $USER->id : $userid;
        $count = $DB->count_records_sql('SELECT COUNT(*) FROM {forum_posts} WHERE userid = ? AND created >= ?', [$userid, $this->getmintimestamp()]);
        return $count >= $this->postratelimit;
    }

    private function getmintimestamp() {
        if ($this->timespanunit == LOCAL_FORUMPOSTRATELIMIT_SECONDS) {
            return time() - $this->timespan;
        }
        if ($this->timespanunit == LOCAL_FORUMPOSTRATELIMIT_MINUTES) {
            return time() - ($this->timespan * 60);
        }
        if ($this->timespanunit == LOCAL_FORUMPOSTRATELIMIT_HOURS) {
            return time() - ($this->timespan * 3_600);
        }
        if ($this->timespanunit == LOCAL_FORUMPOSTRATELIMIT_DAYS) {
            return time() - ($this->timespan * 86_400);
        }
        return time();
    }

    private function assignrecord(\stdClass $record) {
        $this->postratelimit = $record->postratelimit;
        $this->timespan = $record->timespan;
        $this->timespanunit = $record->timespanunit;
    }

    private static function getconfig($contextid) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        return $DB->get_record('local_forumpostratelimit_configs', ['context' => $contextid]);
    }

    private static function getsiteconfig() {
        $record = new \stdClass();
        $record->postratelimit = get_config('local_forumpostratelimit', 'limit');
        $record->timespan = get_config('local_forumpostratelimit', 'timespan');
        $record->timespanunit = get_config('local_forumpostratelimit', 'timespanunit');
        return $record;
    }
}
