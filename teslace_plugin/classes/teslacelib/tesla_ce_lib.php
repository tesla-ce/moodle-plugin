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

require_once(dirname(__FILE__).'/../../vendor/autoload.php');

require_once(dirname(__FILE__).'/lti.php');
require_once(dirname(__FILE__).'/common.php');
require_once(dirname(__FILE__).'/tesla_course.php');
require_once(dirname(__FILE__).'/tesla_activity.php');

use tesla_ce\client\Client;
use tesla_ce\client\exceptions\ResponseError;
use tesla_ce_lib\LTI as TeSLACELibLTI;
use tesla_ce_lib\Common as TeSLACELibCommon;

use settings_navigation;
use moodle_url;
use get_config;
use cache;


class TeSLACELib{
    public $client = null;
    public $common = null;
    public $lti = null;
    private static $instance = null;
    private $initialized = false;
    private $teslaCourse = null;
    private $teslaActivity = null;

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new TeSLACELib();

            if (self::$instance->init() === false) {
                static::$instance = null;
            }
        }

        return static::$instance;
    }

    private function __construct() {}
    private function __clone(){}

    private function init(){
        $role = get_config('local_teslace', 'role');
        $secret = get_config('local_teslace', 'secret');
        $base_url = get_config('local_teslace', 'base_url');
        $debug = boolval(get_config('local_teslace', 'debug'));
        $this->common = new TeSLACELibCommon();

        if ($this->common->is_enable() === false) {
            return $this->initialized;
        }

        try {
            $tesla_cache = new \tesla_ce\client\Cache(cache::make('local_teslace', 'tesla_ce_sdk'));

            $this->client = new Client($role, $secret, $base_url, !$debug, $tesla_cache);

            $this->lti = new TeSLACELibLTI($this->client, $this->common);
            $this->teslaCourse = new TeslaCourse($this->client);
            $this->teslaActivity = new TeslaActivity($this->client, $this->common);

            $this->initialized = true;
        } catch (ResponseError $err) {
            $this->initialized = false;
            if ($debug === true) {
                mtrace("local_tesla_ce: ".$err->getMessage());
            }
        }

        return $this->initialized;
    }

    public function getTeSLACourse() {
        if ($this->initialized === true) {
            return $this->teslaCourse;
        }
        return null;
    }

    public function getTeSLAActivity()
    {
        if ($this->initialized === true) {
            return $this->teslaActivity;
        }
        return null;
    }
    /**
     * @param settings_navigation $nav
     * @param $context
     *
     */
    public function local_teslace_extend_settings_navigation(settings_navigation $nav, $context) {
        if ($this->common->is_enable() === false || $this->initialized !== true) {
            return;
        }

        global $USER;
        global $PAGE;

        $course_id = null;
        $branch = null;
        $instance_id = $context->instanceid;

        if ($context->contextlevel == CONTEXT_COURSE) {
            $course_id = $context->instanceid;
            $branch = $nav->get('courseadmin');

        } else if ($context->contextlevel == CONTEXT_MODULE) {
            $course_id = $PAGE->__get('course')->id;
            $branch = $nav->get('modulesettings');
        }

        if ($course_id === null) {
            return;
        }

        if ($this->common->is_enable() === false) {
            return;
        }

        if ($branch) {
            $my_tesla_url = $this->generate_url_dashboard(null, 'my_tesla', null);
            $branch->add($this->common->get_string('my_tesla_title'), $my_tesla_url, $nav::TYPE_CONTAINER,
                null, 'tesla_ce_my_tesla_' . $course_id);

            $vle_id = $this->client->getVleId();
            $course = $this->common->get_course_info();

            $tesla_course_id = $this->get_or_create_course($vle_id, $course);
            $this->add_user_to_course($course_id, $vle_id, $tesla_course_id);

            $is_student = $this->common->is_user_with_role($course_id, TeSLACELibCommon::ROLE_STUDENT, $USER->id);

            if (!$is_student) {
                // Add TeSLA course link inside context course
                if ($context->contextlevel == CONTEXT_COURSE) {
                    // $data_url['context'] = 'course';
                    // $my_tesla_url = new moodle_url('/local/teslace/views/lti_tesla.php', $data_url);
                    $my_tesla_url = $this->generate_url_dashboard(null, 'course', $tesla_course_id);
                    $branch->add($this->common->get_string('tesla_course'), $my_tesla_url, $nav::TYPE_CONTAINER,
                        null, 'tesla_ce_my_tesla_course_' . $course_id);
                }

                // Add TeSLA activity link inside context activity
                if ($context->contextlevel == CONTEXT_MODULE) {
                    // $data_url['context'] = 'activity';
                    // $my_tesla_url = new moodle_url('/local/teslace/views/lti_tesla.php', $data_url);
                    $activity = $this->common->get_activity_info_by_instance_id($PAGE->context->instanceid);
                    $response = $this->client->getActivity()->getByVleActivityIdAndType($vle_id, $tesla_course_id, $activity['id'], $activity['type']);

                    if ($response['headers']['http_code'] == 200 && count($response['content']['results']) > 0) {
                        $tesla_activity_id = $response['content']['results'][0]['id'];
                        $my_tesla_url = $this->generate_url_dashboard($tesla_activity_id, 'activity', $tesla_course_id);
                        $branch->add($this->common->get_string('tesla_activity'), $my_tesla_url, $nav::TYPE_CONTAINER,
                            null, 'tesla_ce_my_tesla_activity_' . $course_id);
                    }
                }
            }
        }
    }

    public function local_teslace_before_footer() {
        if ($this->common->is_enable() === false || $this->initialized !== true) {
            return;
        }
        global $PAGE;
        global $USER;

        if ($this->common->is_enable() === true) {
            // is activity context?
            if ($PAGE->context->contextlevel == CONTEXT_MODULE) {
                $course = $this->common->get_course_info();
                $activity = $this->common->get_activity_info_by_instance_id($PAGE->context->instanceid);
                $learner = $this->common->get_learner_info();

                $vle_id = $this->client->getVleId();
                // get course
                $course_id = $this->get_or_create_course($vle_id, $course);
                $redirect_reject_url = null;
                $locale = $this->common->get_current_language();

                $this->add_user_to_course($course['id'], $vle_id, $course_id);

                // get activity, if tesla active continue, if not return null;
                $response = $this->client->getActivity()->getByVleActivityIdAndType($vle_id, $course_id, $activity['id'], $activity['type']);

                if ($response['headers']['http_code'] != 200 || $response['content']['count'] == 0 ||
                    $response['content']['results'][0]['enabled'] != true)  {
                    return null;
                }

                // if user is student then inject capture JS
                if ($this->common->is_user_with_role($course['id'], TeSLACELibCommon::ROLE_STUDENT, $USER->id) === true) {
                    $response = $this->client->getLearner()->getByUid($vle_id, $course_id, $learner['email']);

                    // check informed consent
                    $ic_status = $response['content']['results'][0]['ic_status'];
                    if (substr($ic_status, 0, 6) != 'VALID_') {
                        return $this->lti->lti_go_to('informed_consent', $PAGE->context->instanceid, $course['id'], array());
                    }

                    $session_id = $this->common->getAssessmentId($activity);
                    $reject_message = null;
                    $max_ttl = intval(get_config('local_teslace', 'max_ttl'));

                    $floating_menu_initial_pos = get_config('local_teslace', 'floating_menu_initial_pos');

                    $response = $this->client->getAssessment()->create($vle_id, $course['id'], $activity['id'],
                        $activity['type'], $learner['email'], $max_ttl, $redirect_reject_url, $reject_message, $locale,
                        $session_id, $floating_menu_initial_pos);

                    // check if session is not closed
                    if ($response['headers']['http_code'] == 400)  {
                        // assume that session_id is closed, we need another session_id to send data.
                        // Create new assessment without session_id
                        $response = $this->client->getAssessment()->create($vle_id, $course['id'], $activity['id'],
                            $activity['type'], $learner['email'], $max_ttl, $redirect_reject_url, $reject_message,
                            $locale, null, $floating_menu_initial_pos);
                    }

                    // check enrolment status
                    if (isset($response['content']['status']) && $response['content']['status'] == 4) {
                        // missing enrolment
                        $instruments = array();

                        if ($response['content']['enrolments']['instruments'] && count($response['content']['enrolments']['instruments']) > 0) {

                            foreach ($response['content']['enrolments']['instruments'] as $instrument) {
                                $instruments[] = $instrument['instrument_id'];
                            }
                        }
                        return $this->go_to_url_dashboard($PAGE->context->instanceid, 'enrolment', $course['id']);

                        // return $this->lti->lti_go_to('enrolment', $PAGE->context->instanceid, $course['id'], $instruments);
                    }

                    $assessment_id = $response['content']['id'];
                    $this->common->setAssessmentId($activity, $assessment_id);
                    
                    $connector_url = $response['content']['data']['connector'];

                    if ($connector_url != '') {
                        $script = "var tesla_c = document.createElement('script'); tesla_c.setAttribute('type','text/javascript'); tesla_c.setAttribute('src', '" . $connector_url . "'); document.body.appendChild(tesla_c);";
                        $PAGE->requires->js_init_code($script);
                    }
                }
            }
        }
        return null;
    }

    private function get_or_create_course($vle_id, $course) {
        $response = $this->client->getCourse()->getByVleCourseId($vle_id, $course['id']);

        if ($response['headers']['http_code'] == 200 && $response['content']['count'] == 0) {
            $response = $this->client->getCourse()->create($vle_id, $course['name'], $course['id'],
                $course['description'], $course['start_at'], $course['end_at'] );
            $tesla_course_id = $response['content']['id'];
        } else {
            $tesla_course_id = $response['content']['results'][0]['id'];
        }

        return $tesla_course_id;
    }

    private function add_user_to_course($course_id, $vle_id, $tesla_course_id) {
        global $USER;

        $learner = $this->common->get_learner_info();

        // if user is teacher  and configuration auto-enrol-instructor is enable
        if (boolval(get_config('local_teslace', 'auto_enrol_instructor')) === true &&
            $this->common->is_user_with_role($course_id, TeSLACELibCommon::ROLE_INSTRUCTOR, $USER->id) === true) {
            $this->client->getCourse()->addInstructor($vle_id, $tesla_course_id, $learner['email'],
                $learner['name'], $learner['surname'], $learner['email']);
        }
        if (boolval(get_config('local_teslace', 'auto_enrol_learner')) === true &&
            $this->common->is_user_with_role($course_id, TeSLACELibCommon::ROLE_LEARNER, $USER->id) === true) {
            $this->client->getCourse()->addLearner($vle_id, $tesla_course_id, $learner['email'],
                $learner['name'], $learner['surname'], $learner['email']);
        }
    }

    private function generate_url_dashboard($instance_id, $context, $course_id) {
        $data_url = array('instance_id' => $instance_id, 'context' => $context, 'course_id'=>$course_id);
        // Add My TeSLA link in the settings menu
        return new moodle_url('/local/teslace/views/my_tesla.php', $data_url);
    }

    private function go_to_url_dashboard($instance_id, $context, $course_id) {
        header("Location:".htmlspecialchars_decode($this->generate_url_dashboard($instance_id, $context, $course_id)->out()));
        die();
    }
    

    public function report_exception() {
        // Sentry\init(['dsn' => 'https://a4de765bb10b4972986beb6e17dfd4ab@sentry.sunai.uoc.edu/6' ]);
        /*
        $dsn = getenv('SENTRY_DSN');

        $true_values = array(true, '1', 1, 'true');
        $sentry_enabled = in_array(getenv('SENTRY_ENABLED'), $true_values);

        if ($sentry_enabled === true) {
            Sentry\init([
                'dsn'=>$dsn,
                'max_breadcrumbs' => 50
            ]);
        }
        */
    }

}
