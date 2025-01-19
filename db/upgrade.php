<?php

function xmldb_local_forumpostratelimit_upgrade(int $oldversion): bool {
    global $DB;
    /** @var moodle_database $DB */
    $DB;

    if ($oldversion < 2025011800) {
        $dbmanager = $DB->get_manager();
        $dbmanager->install_from_xmldb_file(__DIR__ . '/install.xml');
        upgrade_plugin_savepoint(true, 2025011800, 'local', 'forumpostratelimit');
    }

    return true;
}
