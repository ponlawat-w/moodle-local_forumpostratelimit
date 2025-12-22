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
 * Backup definition.
 *
 * @package     local_forumpostratelimit
 * @copyright   2025 Ponlawat WEERAPANPISIT <ponlawat_w@outlook.co.th>
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Plugin backup definition.
 */
class backup_local_forumpostratelimit_plugin extends backup_local_plugin {
    /**
     * Definition of plugin backup in course level.
     *
     * @return \backup_plugin_element
     */
    protected function define_course_plugin_structure() {
        $plugin = $this->get_plugin_element();
        $wrapper = new backup_nested_element($this->get_recommended_name());
        $settings = new backup_nested_element(
            'settings',
            ['id'],
            [
                'postratelimit',
                'timespan',
                'timespanunit',
            ]
        );
        $plugin->add_child($wrapper);
        $wrapper->add_child($settings);
        $settings->set_source_table(
            'local_forumpostratelimit_configs',
            ['context' => backup::VAR_CONTEXTID]
        );
        return $plugin;
    }

    /**
     * Definition of plugin backup in module level.
     *
     * @return \backup_plugin_element
     */
    protected function define_module_plugin_structure() {
        return $this->define_course_plugin_structure();
    }
}
