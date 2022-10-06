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

namespace local_teslace;

require_once(dirname(__FILE__).'/teslacelib/tesla_ce_lib.php');

use tesla_ce_lib\TeSLACELib;

class observers
{
    public static function course_updated($event)
    {
        $eventdata = $event->get_record_snapshot('course',$event->objectid);
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $t_lib->getTeSLACourse()->create_or_update($eventdata);
    }

    public static function course_created($event)
    {
        $eventdata = $event->get_record_snapshot('course',$event->objectid);
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $t_lib->getTeSLACourse()->create_or_update($eventdata);
    }

    public static function course_deleted($event)
    {
        // var_dump("COURSE deleted");
    }

    public static function course_completed($event)
    {
        // var_dump("COURSE completed");
    }

    public static function course_restored($event)
    {
        // var_dump("COURSE restored");
    }

    public static function activity_updated($event)
    {
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }

        $t_lib->getTeSLAActivity()->create_or_update($event);
    }

    public static function activity_created($event)
    {
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $t_lib->getTeSLAActivity()->create_or_update($event);
    }

    public static function activity_deleted($event)
    {
        // var_dump("ACTIVITY deleted");
    }

    public static function activity_completed($event)
    {
        // var_dump("ACTIVITY completed");
    }

    public static function user_updated($event)
    {
        // var_dump("USER updated");
    }

    public static function user_created($event)
    {
        // var_dump("USER created");
    }

    public static function user_deleted($event)
    {
        // var_dump("USER deleted");
    }

    public static function user_loggedin($event)
    {
        // var_dump("USER loggedin");
    }

    public static function user_enrolment_created($event)
    {
        // var_dump("USER enrolment created");
    }

    public static function user_enrolment_deleted($event)
    {
        // var_dump("USER enrolment deleted");
    }

    public static function user_enrolment_updated($event)
    {
        // var_dump("USER enrolment updated");
    }

    # mod assign
    public static function mod_assign_submission_created($event)
    {
        $eventdata = $event->get_record_snapshot('assignsubmission_file', $event->objectid);
        $assign_id = $eventdata->assignment;

        $t_lib = TeSLACELib::getInstance();

        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->submission, 'assign', $assign_id,
            $event->courseid);
    }

    public static function mod_assign_submission_updated($event)
    {
        $eventdata = $event->get_record_snapshot('assignsubmission_file', $event->objectid);
        $assign_id = $eventdata->assignment;

        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->submission, 'assign', $assign_id,
            $event->courseid);
    }

    public static function mod_assign_submission_online_created($event)
    {
        $eventdata = $event->get_record_snapshot('assignsubmission_onlinetext', $event->objectid);
        $assign_id = $eventdata->assignment;

        $t_lib = TeSLACELib::getInstance();

        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->submission, 'assign', $assign_id,
            $event->courseid);
    }

    public static function mod_assign_submission_online_updated($event)
    {
        $eventdata = $event->get_record_snapshot('assignsubmission_onlinetext', $event->objectid);
        $assign_id = $eventdata->assignment;

        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->submission, 'assign', $assign_id,
            $event->courseid);
    }

    # Mod forum
    public static function mod_forum_discussion_created($event)
    {
        $eventdata = $event->get_record_snapshot('forum_discussions',$event->objectid);
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->firstpost, 'forum', $event->other['forumid'], $event->courseid);
    }
    public static function mod_forum_post_created($event)
    {
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($event->objectid, 'forum', $event->other['forumid'], $event->courseid);
    }

    public static function mod_forum_post_updated($event)
    {
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($event->objectid, 'forum', $event->other['forumid'], $event->courseid);
    }

    # Mod quiz
    public static function mod_quiz_attempt_started($event)
    {
        // var_dump("mod_quiz_attempt_started");
        //die('aaaaaa');
    }

    public static function mod_quiz_attempt_submitted($event)
    {
        $eventdata = $event->get_record_snapshot('quiz_attempts',$event->objectid);
        $t_lib = TeSLACELib::getInstance();
        if ($t_lib === null) {
            $debug = boolval(get_config('local_teslace', 'debug'));
            if ($debug == true) {
                mtrace("Moodle can not connect to TeSLA infrastructure");
            }
            return;
        }
        $insertId = $t_lib->getTeSLAActivity()->queue_submission($eventdata->id, 'quiz', $eventdata->quiz, $event->courseid);
    }
}
