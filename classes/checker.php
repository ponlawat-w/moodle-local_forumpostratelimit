<?php

namespace local_forumpostratelimit;

require_once(__DIR__ . '/../lib.php');

class checker {
    private $contextid = null;
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

    public function limitexceeded($userid = null, $excludepostid = 0) {
        global $USER, $DB;
        /** @var \moodle_database $DB */
        $DB;

        if (is_null($this->timespan) || $this->timespan <= 0) {
            return false;
        }
        $userid = is_null($userid) ? $USER->id : $userid;

        $count = 0;
        if ($this->contextid) {
            $context = \core\context::instance_by_id($this->contextid);
            if ($context instanceof \core\context\module) {
                $coursemodule = get_coursemodule_from_id('forum', $context->instanceid);
                $forumid = $coursemodule->instance;
                $count = $DB->count_records_sql(
                    'SELECT COUNT(*) FROM {forum_posts} p JOIN {forum_discussions} d ON d.id = p.discussion WHERE d.forum = ? AND p.userid = ? AND p.created >= ? AND p.id != ?',
                    [$forumid, $userid, $this->getmintimestamp(), $excludepostid]
                );
            } else if ($context instanceof \core\context\course) {
                $courseid = $context->instanceid;
                $count = $DB->count_records_sql(
                    'SELECT COUNT(*) FROM {forum_posts} p JOIN {forum_discussions} d ON d.id = p.discussion WHERE d.course = ? AND p.userid = ? AND p.created >= ? AND p.id != ?',
                    [$courseid, $userid, $this->getmintimestamp(), $excludepostid]
                );
            }
        } else {
            $count = $DB->count_records_sql('SELECT COUNT(*) FROM {forum_posts} WHERE userid = ? AND created >= ? AND id != ?', [$userid, $this->getmintimestamp(), $excludepostid]);
        }
        return $count >= $this->postratelimit;
    }

    public function getlimitexceededstring() {
        if (!$this->timespan || !$this->timespanunit) {
            return '';
        }
        $a = [
            'limit' => is_null($this->postratelimit) ? 0 : $this->postratelimit,
            'timespan' => $this->timespan,
            'timespanunit' => get_string('timespanunit_' . $this->timespanunit, 'local_forumpostratelimit'),
        ];
        return get_string('limitexceeded', 'local_forumpostratelimit', $a);
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
        $this->contextid = $record->context;
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
        $record->context = null;
        $record->postratelimit = get_config('local_forumpostratelimit', 'limit');
        $record->timespan = get_config('local_forumpostratelimit', 'timespan');
        $record->timespanunit = get_config('local_forumpostratelimit', 'timespanunit');
        return $record;
    }

    public static function hassiteconfig() {
        return get_config('local_forumpostratelimit', 'limit')
            && get_config('local_forumpostratelimit', 'timespan')
            && get_config('local_forumpostratelimit', 'timespanunit');
    }

    public static function fromforumid($forumid) {
        $coursemodule = get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST);
        return new self($coursemodule->id);
    }

    public static function fromcmid($cmid) {
        return new self($cmid);
    }
}
