<?php

namespace local_forumpostratelimit;

class formdata {
    private ?int $postratelimit = null;
    private ?float $timespan = null;
    private ?int $timespanunit = null;

    public function __construct(\stdClass $data) {
        $this->postratelimit = isset($data->local_forumpostratelimit_postratelimit) ?
            $data->local_forumpostratelimit_postratelimit : null;
        $this->timespan = isset($data->local_forumpostratelimit_timespan) ?
            $data->local_forumpostratelimit_timespan : null;
        $this->timespanunit = isset($data->local_forumpostratelimit_timespanunit) ?
            $data->local_forumpostratelimit_timespanunit : null;
    }

    public function isempty() {
        return $this->timespan <= 0;
    }

    public function applytoobject(\stdClass $obj) {
        $obj->postratelimit = $this->postratelimit;
        $obj->timespan = $this->timespan;
        $obj->timespanunit = $this->timespanunit;
    }

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
        if ($this->isempty()) return;
        $record = new \stdClass();
        $record->context = $contextid;
        $this->applytoobject($record);
        $DB->insert_record('local_forumpostratelimit_configs', $record);
    }

    public function applytocourseid($courseid) {
        return $this->applytocontextid(\core\context\course::instance($courseid)->id);
    }

    public function applytomoduleid($moduleid) {
        return $this->applytocontextid(\core\context\module::instance($moduleid)->id);
    }

    public function applytoforumid($forumid) {
        return $this->applytocontextid(\core\context\module::instance(
            get_coursemodule_from_instance('forum', $forumid, 0, false, MUST_EXIST)->id
        )->id);
    }
}
