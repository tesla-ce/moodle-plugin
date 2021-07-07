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

require_once(dirname(__FILE__) . '/../../../config.php');
require_login();

$event = \core\event\course_updated::create(array('contextid' => 2));
$event = \core\event\course_updated::create(array(
    'objectid' => 2,
    'context' => context_course::instance(2),
    'other' => array('shortname' => 'test2',
        'fullname' => 'test2 fullname',
        'updatedfields' => array('category' => 1))
));

// ... code that may add some record snapshots
$event->trigger();
//$eventdata = new stdClass();
//events_trigger("\core\event\course_updated", $eventdata);
