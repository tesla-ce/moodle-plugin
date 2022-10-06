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

require_once(dirname(__FILE__).'/../classes/teslacelib/tesla_ce_lib.php');
use tesla_ce_lib\TeSLACELib;
global $CFG;

$t_lib = TeSLACELib::getInstance();

switch($_GET['type']) {
    case 'course':
        $course = unserialize($_GET['course']);
        $vle_id = $_GET['vle_id'];
        $t_lib->get_or_create_course($vle_id, $course, true);
        break;
    case 'activity':
        $event = new stdClass();
        $event->objectid = $_GET['instance_id'];
        $t_lib->getTeSLAActivity()->create_or_update($event, true);
        break;
}
header("Location: {$_SERVER['HTTP_REFERER']}");
exit();