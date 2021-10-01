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

$learner = $t_lib->common->get_learner_info();
$base_endpoint_api = get_config('local_teslace', 'base_url');
$url = $base_endpoint_api."/api/v2/vle/{$t_lib->client->getVleId()}/launcher/";

$response = $t_lib->client->getVle()->getLauncher($t_lib->client->getVleId(), $learner['email']);

if (!isset($response['content']['url'])) {
    echo "My TeSLA is not available.";
    die();
}

$redirect_url = null;

$url = $response['content']['url'];
$params = array(
    'id'=>$response['content']['id'],
    'token'=>$response['content']['token'],
    'redirect_uri'=>$CFG->wwwroot.'course/view.php?id='.$_GET['course_id'],
    'institution_id'=>$t_lib->client->getModule()['vle']['institution']
);

// extract dashboard URL
$url_explode = explode('auth', $url);
$base_url_dashboard = $url_explode[0];

switch($_GET['context']) {
    case 'my_tesla':
        break;
    case 'activity':
        $url = $base_url_dashboard.'plugin/activity/configuration';
        $params['activity_id'] = $_GET['instance_id'];
        $params['course_id'] = $_GET['course_id'];
        break;
    case 'course':
        $url = $base_url_dashboard."plugin/course/report";
        $params['course_id'] = $_GET['course_id'];
        break;
    case 'test_page':
        $url = $base_url_dashboard.'plugin/test-page';
        break;
    case 'enrolment':
        $url = $base_url_dashboard.'plugin/enrolment';
        break;
    case 'informed-consent':
        $url = $base_url_dashboard.'plugin/ic';
        break;
}


$url = $url.'?'.http_build_query($params,'', '&');

header("Location: {$url}");
exit();


