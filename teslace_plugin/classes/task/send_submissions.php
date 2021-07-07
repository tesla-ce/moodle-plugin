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

namespace local_teslace\task;

require_once(dirname(__FILE__).'/../teslacelib/tesla_ce_lib.php');
use tesla_ce_lib\TeSLACELib;

/**
 * Send submissions task to TeSLA Infrastructure.
 */
class send_submissions extends \core\task\scheduled_task
{
    const MAX_SUBMISSIONS_SENT_PER_CRON = 50;
    /**
     * Return the task's name as shown in admin screens.
     *
     * @return string
     */
    public function get_name()
    {
        return get_string('send_pending_submissions', 'local_teslace');
    }

    /**
     * Execute the task.
     */
    public function execute()
    {
        global $DB;
        $t_lib = TeSLACELib::getInstance();

        if ($t_lib === null) {
            mtrace("Moodle can not connect to TeSLA infrastructure");
            return;
        }

        // get 1 pending request
        $params =  array('PENDING');

        $submissions = $DB->get_records_select('local_teslace_pend_requests', 'status = ?', $params, '', '*', 0, 1);
        $submission = array_pop($submissions);
        $processed_ids = [];
        $i = 0;

        while ($submission !== null && $i < send_submissions::MAX_SUBMISSIONS_SENT_PER_CRON) {
            $result = $t_lib->getTeSLAActivity()->send_submission($submission);

            if ($result['result'] === true) {
                // mark request as sent
                $submission->status = 'SENT';
            } else {
                // result false
                $submission->counter++;
                $submission->observations = $result['message'];

                if ($submission->counter > 2) {
                    $submission->status = 'ERROR';
                }
            }

            $DB->update_record('local_teslace_pend_requests', $submission, false);

            $processed_ids[] = $submission->id;

            // get another 1 pending request
            $select = 'status = ? AND id NOT IN(?)';
            $params =  array(
                'PENDING',
                implode(',', $processed_ids)
            );
            $submissions = $DB->get_records_select('local_teslace_pend_requests', $select, $params, '', '*', 0, 1);
            $submission = null;
            if ($submissions) {
                $submission = array_pop($submissions);
            }
            $i++;
        }
    }
}
