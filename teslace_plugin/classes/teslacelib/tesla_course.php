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

class TeslaCourse {
    private $client = null;
    public function __construct($client)
    {
        $this->client = $client;
    }

    public function create_or_update($eventdata) {//$shortname, $id, $fullname, $start, $end) {
        $vle_id = $this->client->getVleId();

        $start = new \Datetime('@'.$eventdata->startdate);
        $end = new \Datetime('@'.$eventdata->enddate);


        $course = $this->client->getCourse()->getByVleCourseId($vle_id, $eventdata->id);

        if ($course['headers']['http_code'] == 200) {
            $course_id = $course['content']['results'][0]['id'];
            $result = $this->client->getCourse()->update($vle_id, $course_id, $eventdata->shortname, $eventdata->id,
                $eventdata->fullname, $start, $end);

        } else {
            // create course, because in TeSLA this course not exists.
            $course_id = $course['content']['results'][0]['id'];
            $result = $this->client->getCourse()->create($vle_id, $eventdata->shortname, $eventdata->id,
                $eventdata->fullname, $start, $end);
        }
    }
}