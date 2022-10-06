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
use context_course;

class Common {
    const ROLE_STUDENT = 'student';
    const ROLE_LEARNER = 'student';
    const ROLE_INSTRUCTOR = 'teacher';
    const ROLE_ADMIN = 'manager';

    public function get_string($key) {
        return get_string($key, 'local_teslace');
    }

    public function is_enable() {
        //local_tesla/usetesla
        $use_tesla = boolval(get_config('local_teslace', 'usetesla'));

        if (!$use_tesla) {
            return false;
        }

        return true;
    }

    public function is_user_with_role($course_id, $role_name, $user_id = 0) {
        $result = false;

        $roles = get_user_roles(context_course::instance($course_id), $user_id, false);

        foreach ($roles as $role) {
            if (strpos($role->shortname, $role_name) !== false) {
                $result = true;
                break;
            }
        }
        return $result;
    }

    public function get_course_info() {
        global $PAGE;
        $start_at = null;
        $end_at = null;

        return array(
            'id' => $PAGE->course->id,
            'name' => $PAGE->course->shortname,
            'description' => $PAGE->course->fullname,
            'start_at'=>$start_at,
            'end_at'=>$end_at
        );
    }

    public function get_activity_info_by_instance_id($instance_id) {
        global $DB;
        $course_module = $DB->get_record('course_modules', array('id'=>$instance_id), '*', MUST_EXIST);
        $mod_name = $DB->get_field('modules','name',array('id' =>$course_module->module));
        $activity = $DB->get_record($mod_name, array('id' =>$course_module->instance), '*', MUST_EXIST);

        return array(
            'id' => $activity->id,
            'name' => $activity->name,
            'description'=> $activity->intro,
            'type' => $mod_name
        );
    }

    public function get_learner_info() {
        global $USER;

        return array(
            'id' => $USER->id,
            'name' => $USER->firstname,
            'surname' => $USER->lastname,
            'email' => $USER->email
        );

    }

    public function setAssessmentId($activity, $assessment_id) {
        global $SESSION;
        $SESSION->{'tesla_assessment_id_'.$activity['id'].'-'.$activity['type']} = $assessment_id;
    }

    public function getAssessmentId($activity) {
        global $SESSION;

        if (property_exists($SESSION, 'tesla_assessment_id_' . $activity['id'] . '-' . $activity['type']) === true) {
            return $SESSION->{'tesla_assessment_id_' . $activity['id'] . '-' . $activity['type']};
        }
        return null;
    }

    public function clearAssessmentId($activity) {
        global $SESSION;
        $this->setAssessmentId($activity, null);
    }

    public function get_current_language() {
        global $SESSION;
        global $COURSE;
        global $USER;

        if ($COURSE->lang != '') {
            return substr($COURSE->lang, 0, 2);
        }

        if (property_exists($SESSION, 'lang') && $SESSION->lang != '') {
            return substr($SESSION->lang, 0, 2);
        }

        if (property_exists($USER, 'lang') && $USER->lang != '') {
            return substr($USER->lang, 0, 2);
        }

        return null;
    }
}
