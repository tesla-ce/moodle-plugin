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

namespace tesla_ce_lib;
use moodle_url;
use tesla_ce\client\Client;


class LTI {
    private $client = null;
    private $common = null;

    public function __construct(Client $client, Common $common) {
        $this->client = $client;
        $this->common = $common;
    }

    public function lti_go_to($where, $instance_id, $course_id, $instruments) {
        $_GET['context'] = $where;
        $_GET['instance_id'] = $instance_id;
        $_GET['course_id'] = $course_id;
        $_GET['instruments'] = $instruments;

        echo $this->create_lti_request();
        return null;
    }

    public function create_lti_request() {
        if (!isset($_GET['context'])) {
            throw \Exception("Context iss not defined");
        }

        //global $PAGE;
        global $CFG;
        require_once($CFG->dirroot . '/mod/lti/locallib.php');
        $debug = boolval(get_config('local_teslace', 'debug'));

        $module = $this->client->getModule();
        $module_type = $this->client->getModuleType();

        $context = null;

        if (isset($_GET['context'])) {
            $context = $_GET['context'];
        }

        $instance_id = null;
        if (isset($_GET['instance_id'])) {
            $instance_id = $_GET['instance_id'];
        } else {
            die('Required parameter id');
        }

        if (isset($_GET['course_id'])) {
            $course_id = $_GET['course_id'];
        } else {
            die('Required parameter course_id');
        }

        $instruments = array();
        if (isset($_GET['instruments'])) {
            $instruments = $_GET['instruments'];
        }

        $custom_params = array(
            'tesla_url_callback'=>null,
            'tesla_activity_id'=>null,
            'tesla_activity_type'=>null,
            'tesla_instruments'=>implode(',', $instruments),
            'tesla_vle_id'=>1
        );

        $base_endpoint = get_config('local_teslace', 'lti_url');
        switch($context) {
            case 'informed_consent':
                // todo: this case it will be redefined in future.
                $endpoint = $base_endpoint.'/informed_consent';
                $learner = $this->common->get_learner_info();
                $custom_params['tesla_learner_id'] = $learner['email'];
                $custom_params['tesla_url_callback'] = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $instance_id));
                break;
            case 'enrolment':
                $endpoint = $base_endpoint.'/enrolment/';
                $learner = $this->common->get_learner_info();
                $custom_params['tesla_learner_id'] = $learner['email'];
                $custom_params['tesla_url_callback'] = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $instance_id));
                break;
            case 'activity':
                $endpoint = $base_endpoint.'/instructor/';
                $activity = $this->common->get_activity_info_by_instance_id($instance_id);

                $custom_params['tesla_course_id'] = $course_id;
                $custom_params['tesla_activity_id'] =$activity['id'];
                $custom_params['tesla_activity_type'] = $activity['type'];
                $custom_params['tesla_activity_title'] = $activity['name'];
                $custom_params['tesla_activity_description'] = $activity['description'];
                $custom_params['tesla_url_callback'] = new moodle_url($CFG->wwwroot.'/mod/'.$activity['type'].'/view.php', array('id' => $instance_id));
                break;
            case 'course':
                $endpoint = $base_endpoint.'/instructor/';
                $custom_params['tesla_course_id'] = $course_id;
                $custom_params['tesla_url_callback'] = new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $instance_id));
                break;
            default:
                throw \Exception("Context is not valid");
        }

        $custom_params_str = "";
        if($custom_params && count($custom_params) > 0) {
            foreach($custom_params as $key => $value) {
                $custom_params_str .= "\r\n".$key.'='.$value;
            }
        }

        $instance = new \stdClass();
        $instance->id = null;
        $instance->typeid = null;
        $instance->ltiversion = LTI_VERSION_1;

        $instance->course = $course_id;
        $instance->instructorchoicesendname = LTI_SETTING_ALWAYS;
        $instance->instructorcustomparameters = $custom_params_str;
        $instance->instructorchoiceacceptgrades = LTI_SETTING_NEVER;
        $instance->instructorchoiceallowroster = LTI_SETTING_ALWAYS;
        $instance->instructorchoicesendemailaddr = false;
        $instance->organizationid = null;
        $instance->toolproxyid = null;
        $instance->resourcekey = $module[$module_type]['lti']['consumer_key'];
        $instance->password = $module[$module_type]['lti']['consumer_secret'];
        $instance->forcessl = true;

        $instance->toolurl = $endpoint;
        $instance->securetoolurl = $endpoint;

        if ($debug == true) {
            $instance->forcessl = false;
        }

        $instance->customparameters = $custom_params_str;
        $instance->launchcontainer = LTI_LAUNCH_CONTAINER_WINDOW;

        list($endpoint, $params) = lti_get_launch_data($instance);

        // add debug send button, to make life easy
        $debug_button = '';
        if ($debug == true) {
            $debug_button = '<button type="button" onclick="document.getElementById(\'ltiLaunchForm\').submit()">Submit</button>';
        }

        return lti_post_launch_html($params, $endpoint, $debug).$debug_button;
    }
}
