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
 * Checker class for testing if the configured rate has been exceeded.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_forumpostratelimit;

defined('MOODLE_INTERNAL') || die();
require_once(__DIR__ . '/../lib.php');

/**
 * Checker class for testing if the configured rate has been exceeded.
 */
class checker {
    /** @var int $contextid Context ID */
    private $contextid = null;
    /** @var int $postratelimit Number of limited post rate */
    private $postratelimit = null;
    /** @var int $timespan Timespan to check */
    private $timespan = null;
    /** @var int $timespanunit Unit of timespan (enum) */
    private $timespanunit = null;

    /**
     * Constructor
     * Set the configuration value from module-level configration.
     * If not exists, get from course level.
     * If not exists, get from site level.
     * @param int $cmid Module instance ID
     */
    public function __construct($cmid) {
        $context = \core\context\module::instance($cmid);
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

    /**
     * Returns true if limit has been exceeded.
     * @param int|null $userid User ID, null if current user.
     * @param int $excludepostid Post ID to be excluded.
     * @return bool
     */
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
                    <<<SQL
                        SELECT COUNT(*)
                        FROM {forum_posts} p
                        JOIN {forum_discussions} d ON d.id = p.discussion
                        WHERE d.forum = ?
                            AND p.userid = ?
                            AND p.created >= ?
                            AND p.id != ?
                    SQL,
                    [$forumid, $userid, $this->getmintimestamp(), $excludepostid]
                );
            } else if ($context instanceof \core\context\course) {
                $courseid = $context->instanceid;
                $count = $DB->count_records_sql(
                    <<<SQL
                        SELECT COUNT(*)
                        FROM {forum_posts} p
                        JOIN {forum_discussions} d ON d.id = p.discussion
                        WHERE d.course = ?
                            AND p.userid = ?
                            AND p.created >= ?
                            AND p.id != ?
                    SQL,
                    [$courseid, $userid, $this->getmintimestamp(), $excludepostid]
                );
            }
        } else {
            $count = $DB->count_records_sql(
                'SELECT COUNT(*) FROM {forum_posts} WHERE userid = ? AND created >= ? AND id != ?',
                [$userid, $this->getmintimestamp(), $excludepostid]
            );
        }
        return $count >= $this->postratelimit;
    }

    /**
     * Get limit exceeded message.
     * @return string
     */
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

    /**
     * Get the minimum timestamp relatively to now, calculated by the timespan and the unit defined in the configuration.
     * @return int
     */
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

    /**
     * Overwrite the configuration object.
     * @param \stdClass $record
     * @return void
     */
    private function assignrecord(\stdClass $record) {
        $this->contextid = $record->context;
        $this->postratelimit = $record->postratelimit;
        $this->timespan = $record->timespan;
        $this->timespanunit = $record->timespanunit;
    }

    /**
     * Get configuration object from context ID.
     * @param int $contextid
     * @return \stdClass|false
     */
    private static function getconfig($contextid) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        return $DB->get_record('local_forumpostratelimit_configs', ['context' => $contextid]);
    }

    /**
     * Get site-level configuration object.
     * @return \stdClass
     */
    private static function getsiteconfig() {
        $record = new \stdClass();
        $record->context = null;
        $record->postratelimit = get_config('local_forumpostratelimit', 'limit');
        $record->timespan = get_config('local_forumpostratelimit', 'timespan');
        $record->timespanunit = get_config('local_forumpostratelimit', 'timespanunit');
        return $record;
    }

    /**
     * Test if there is configuration in site level.
     * @return bool
     */
    public static function hassiteconfig() {
        return get_config('local_forumpostratelimit', 'limit')
            && get_config('local_forumpostratelimit', 'timespan')
            && get_config('local_forumpostratelimit', 'timespanunit');
    }

    /**
     * Create a checker instance from forum instance ID.
     * @param int $forumid
     * @return \local_forumpostratelimit\checker
     */
    public static function fromforumid($forumid) {
        $coursemodule = get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST);
        return new self($coursemodule->id);
    }

    /**
     * Create a checker instance from course module ID.
     * @param int $cmid
     * @return \local_forumpostratelimit\checker
     */
    public static function fromcmid($cmid) {
        return new self($cmid);
    }
}
