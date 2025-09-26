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
 * Post rate limit configuration form data.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_forumpostratelimit;

/**
 * Post rate limit configuration form data.
 */
class formdata {
    /** @var int|null $postratelimit Value of post rate limit. */
    private ?int $postratelimit = null;
    /** @var int|null $timespan Value of timespan. */
    private ?float $timespan = null;
    /** @var int|null $timespanunit Unit of timespan. */
    private ?int $timespanunit = null;

    /**
     * Constructor
     * @param \stdClass $data Form data form method get_data()
     */
    public function __construct(\stdClass $data) {
        if (!isset($data->local_forumpostratelimit_enabled) || !$data->local_forumpostratelimit_enabled) {
            return;
        }
        $this->postratelimit = isset($data->local_forumpostratelimit_postratelimit) ?
            $data->local_forumpostratelimit_postratelimit : null;
        $this->timespan = isset($data->local_forumpostratelimit_timespan) ?
            $data->local_forumpostratelimit_timespan : null;
        $this->timespanunit = isset($data->local_forumpostratelimit_timespanunit) ?
            $data->local_forumpostratelimit_timespanunit : null;
    }

    /**
     * Test if the configuration value is empty.
     * @return bool
     */
    public function isempty() {
        return $this->timespan <= 0;
    }

    /**
     * Apply configuration value to the object.
     * @param \stdClass $obj Object to be assigned.
     * @return void
     */
    public function applytoobject(\stdClass $obj) {
        $obj->postratelimit = $this->postratelimit;
        $obj->timespan = $this->timespan;
        $obj->timespanunit = $this->timespanunit;
    }

    /**
     * Apply configuration value to context ID.
     * @param int $contextid
     * @return void
     */
    public function applytocontextid($contextid) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;

        $record = $DB->get_record('local_forumpostratelimit_configs', ['context' => $contextid]);
        if ($record) {
            if ($this->isempty()) {
                $DB->delete_records('local_forumpostratelimit_configs', ['id' => $record->id]);
                return;
            }
            $this->applytoobject($record);
            $DB->update_record('local_forumpostratelimit_configs', $record);
            return;
        }
        if ($this->isempty()) {
            return;
        }
        $record = new \stdClass();
        $record->context = $contextid;
        $this->applytoobject($record);
        $DB->insert_record('local_forumpostratelimit_configs', $record);
    }

    /**
     * Apply configuration value to course ID.
     * @param int $courseid
     * @return void
     */
    public function applytocourseid($courseid) {
        $this->applytocontextid(\core\context\course::instance($courseid)->id);
    }

    /**
     * Apply configuration value to module ID.
     * @param int $cmid
     * @return void
     */
    public function applytomoduleid($cmid) {
        $this->applytocontextid(\core\context\module::instance($cmid)->id);
    }

    /**
     * Apply configuration value to forum ID.
     * @param int $forumid
     * @return void;
     */
    public function applytoforumid($forumid) {
        $this->applytocontextid(\core\context\module::instance(
            get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST)->id
        )->id);
    }
}
