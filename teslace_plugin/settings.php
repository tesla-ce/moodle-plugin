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

global $ADMIN;

require_once(dirname(__FILE__).'/classes/teslacelib/tesla_ce_lib.php');

use tesla_ce_lib\TeSLACELib;

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_teslace', get_string('pluginname', 'local_teslace'));
    $ADMIN->add('localplugins', $settings);

    $tesla_status = TeSLACELib::getStatus();

    $settings->add(
        new admin_setting_description('local_teslace/tesla_status',
            get_string('tesla_status', 'local_teslace'),
            $tesla_status
        )
    );

    // Adding the standard "usetesla" field.
    $settings->add(
        new admin_setting_configcheckbox('local_teslace/usetesla', get_string('usetesla', 'local_teslace'),
            get_string('usetesla_help', 'local_teslace'), '1'));

    // Adding the standard "enabletesladefault" field.
    $settings->add(
        new admin_setting_configcheckbox('local_teslace/enabletesladefault', get_string('enabletesladefault', 'local_teslace'),
            get_string('enabletesladefault_help', 'local_teslace'), '1'));

    $settings->add(
        new admin_setting_configcheckbox('local_teslace/verifyssl', get_string('verifyssl', 'local_teslace'),
            get_string('verifyssl_help', 'local_teslace'), '1'));

    // Adding the standard "role" field.
    $settings->add(
        new admin_setting_configtext('local_teslace/role', get_string('role', 'local_teslace'),
            get_string('role_help', 'local_teslace'), getenv('ROLE_ID'), PARAM_TEXT));

    // Adding the standard "secret" field.
    $settings->add(
        new admin_setting_configtext('local_teslace/secret', get_string('secret', 'local_teslace'),
            get_string('secret_help', 'local_teslace'), getenv('SECRET_ID'), PARAM_TEXT));

    // Adding the standard "api_url" field.
    $settings->add(
        new admin_setting_configtext('local_teslace/base_url', get_string('base_url', 'local_teslace'),
            get_string('base_url_help', 'local_teslace'), getenv('API_URL'), PARAM_URL));

    // Adding the standard "max_ttl" field.
    $settings->add(
        new admin_setting_configtext('local_teslace/max_ttl', get_string('max_ttl', 'local_teslace'),
            get_string('max_ttl_help', 'local_teslace'), '360'));

    // Adding the standard "debug" field.
    $settings->add(
        new admin_setting_configcheckbox('local_teslace/debug', get_string('debug', 'local_teslace'),
            get_string('debug_help', 'local_teslace'), '0'));

    // Adding the autoenrol leaners and instructors field.
    $settings->add(
        new admin_setting_configcheckbox('local_teslace/auto_enrol_learner',
            get_string('auto_enrol_learner', 'local_teslace'),
            get_string('auto_enrol_learner_help', 'local_teslace'), '1'));

    $settings->add(
        new admin_setting_configcheckbox('local_teslace/auto_enrol_instructor',
            get_string('auto_enrol_instructor', 'local_teslace'),
            get_string('auto_enrol_instructor_help', 'local_teslace'), '1'));

    $settings->add(
        new admin_setting_configselect('local_teslace/floating_menu_initial_pos',
            get_string('floating_menu_initial_pos', 'local_teslace'),
            get_string('floating_menu_initial_pos_help', 'local_teslace'),
            'top-right',
        array(
            'top-right'=>'Top Right',
            'top-left'=>'Top Left',
            'bottom-right'=>'Bottom Right',
            'bottom-left'=>'Bottom Left',
        ))
    );

    $settings->add(
        new admin_setting_configcheckbox('local_teslace/only_manager_create_courses',
            get_string('only_manager_create_courses', 'local_teslace'),
            get_string('only_manager_create_courses_help', 'local_teslace'), '0'));



}
