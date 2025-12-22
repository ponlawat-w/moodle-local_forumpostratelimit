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
 * Restore definition.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Plugin restore definition.
 */
class restore_local_forumpostratelimit_plugin extends restore_local_plugin {
    /**
     * Definition of course restoration.
     *
     * @return \restore_path_element[]
     */
    protected function define_course_plugin_structure() {
        return [
            new restore_path_element(
                'settings',
                $this->get_pathfor('/settings')
            ),
        ];
    }

    /**
     * Definition of module restoration.
     *
     * @return \restore_path_element[]
     */
    protected function define_module_plugin_structure() {
        return $this->define_course_plugin_structure();
    }

    /**
     * Process the restoration of an entry.
     *
     * @param array $data
     * @return void
     */
    public function process_settings($data) {
        global $DB;
        /** @var \moodle_database $DB */
        $DB;
        $record = (object)$data;
        $record->context = $this->get_task()->get_contextid();
        $DB->insert_record('local_forumpostratelimit_configs', $record);
    }
}
