<?php
/*
 * Copyright (c) 2020 Roger Muñoz Bernaus
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

defined('MOODLE_INTERNAL') || die;

function xmldb_local_teslace_upgrade($oldversion) {
    global $CFG;
    global $DB;
    $dbman = $DB->get_manager();

    $result = TRUE;

    // Insert PHP code from XMLDB Editor here
    if ($oldversion < 202103180930000) {

        // Define table local_teslace_pend_requests to be created.
        $table = new xmldb_table('local_teslace_pend_requests');

        // Adding fields to table local_teslace_pend_requests.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('vle_course_id', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('vle_activity_id', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('vle_activity_type', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('vle_learner_id', XMLDB_TYPE_CHAR, '512', null, null, null, null);
        $table->add_field('status', XMLDB_TYPE_CHAR, '255', null, null, null, 'PENDING');
        $table->add_field('counter', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');
        $table->add_field('info', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('observations', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('created', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('modified', XMLDB_TYPE_INTEGER, '10', null, null, null, null);


        // Adding keys to table local_teslace_pend_requests.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);

        // Conditionally launch create table for local_teslace_pend_requests.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Teslace savepoint reached.
        upgrade_plugin_savepoint(true, 202103180930000, 'local', 'teslace');
    }

    return $result;
}
?>
