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

defined('MOODLE_INTERNAL') or die;

require_once('classes/teslacelib/tesla_ce_lib.php');

use tesla_ce_lib\TeSLACELib;

/**
 * Display the TeSLA CE Links in settings block.
 *
 * @param  settings_navigation $nav     The settings navigatin object
 * @param  stdclass            $context Course context
 */

function local_teslace_extend_settings_navigation(settings_navigation $nav, $context) {
    $t_lib = TeSLACELib::getInstance();
    if ($t_lib != null) {
        return $t_lib->local_teslace_extend_settings_navigation($nav, $context);
    }
}

/**
 * Inject TeSLA javascripts if context is module. This scripts catpure all the data and they send it to TeSLA system.
 */
function local_teslace_before_footer() {
    $t_lib = TeSLACELib::getInstance();
    if ($t_lib != null) {
        return $t_lib->local_teslace_before_footer();
    }
}
