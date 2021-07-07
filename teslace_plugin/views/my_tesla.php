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

$t_lib = TeSLACELib::getInstance();

$learner = $t_lib->common->get_learner_info();
$base_endpoint_api = get_config('local_teslace', 'base_url');
$url = $base_endpoint_api."/api/v2/vle/{$t_lib->client->getVleId()}/launcher/";

$response = $t_lib->client->getVle()->getLauncher($t_lib->client->getVleId(), $learner['email']);
//$redirect_url = "&redirect_url=".urlencode("/course/1&institution_id=1");
// $redirect_url = "&redirect_url=/course/1";
$redirect_url = null;
if (isset($response['content']['url'])) {
    header("Location: {$response['content']['url']}{$redirect_url}");
    exit();
}

echo "My TeSLA is not available.";
die();
