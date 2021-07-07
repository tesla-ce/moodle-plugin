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

$observers = array(
    array(
        'eventname'   => '\core\event\course_updated',
        'callback'    => '\local_teslace\observers::course_updated',
    ),
    array(
        'eventname'   => '\core\event\course_created',
        'callback'    => '\local_teslace\observers::course_created',
    ),
    array(
        'eventname'   => '\core\event\course_deleted',
        'callback'    => '\local_teslace\observers::course_deleted',
    ),
    array(
        'eventname'   => '\core\event\course_completed',
        'callback'    => '\local_teslace\observers::course_completed',
    ),
    array(
        'eventname'   => '\core\event\course_restored',
        'callback'    => '\local_teslace\observers::course_restored',
    ),
    array(
        'eventname'   => '\core\event\course_module_updated',
        'callback'    => '\local_teslace\observers::activity_updated',
    ),
    array(
        'eventname'   => '\core\event\course_module_created',
        'callback'    => '\local_teslace\observers::activity_created',
    ),
    array(
        'eventname'   => '\core\event\course_module_deleted',
        'callback'    => '\local_teslace\observers::activity_deleted',
    ),
    array(
        'eventname'   => '\core\event\course_module_completion_updated',
        'callback'    => '\local_teslace\observers::activity_completed',
    ),
    array(
        'eventname'   => '\core\event\user_updated',
        'callback'    => '\local_teslace\observers::user_updated',
    ),
    array(
        'eventname'   => '\core\event\user_created',
        'callback'    => '\local_teslace\observers::user_created',
    ),
    array(
        'eventname'   => '\core\event\user_deleted',
        'callback'    => '\local_teslace\observers::user_deleted',
    ),
    array(
        'eventname'   => '\core\event\user_loggedin',
        'callback'    => '\local_teslace\observers::user_loggedin',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_created',
        'callback'    => '\local_teslace\observers::user_enrolment_created',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_deleted',
        'callback'    => '\local_teslace\observers::user_enrolment_deleted',
    ),
    array(
        'eventname'   => '\core\event\user_enrolment_updated',
        'callback'    => '\local_teslace\observers::user_enrolment_updated',
    ),
    # Mod assign
    array(
        'eventname'   => '\assignsubmission_file\event\submission_created',
        'callback'    => '\local_teslace\observers::mod_assign_submission_created',
    ),
    array(
        'eventname'   => '\assignsubmission_file\event\submission_updated',
        'callback'    => '\local_teslace\observers::mod_assign_submission_updated',
    ),
    array(
        'eventname'   => '\assignsubmission_onlinetext\event\submission_created',
        'callback'    => '\local_teslace\observers::mod_assign_submission_online_created',
    ),
    array(
        'eventname'   => '\assignsubmission_onlinetext\event\submission_updated',
        'callback'    => '\local_teslace\observers::mod_assign_submission_online_updated',
    ),
    # Mod forum
    array(
        'eventname'   => '\mod_forum\event\post_created',
        'callback'    => '\local_teslace\observers::mod_forum_post_created',
    ),
    array(
        'eventname'   => '\mod_forum\event\post_updated',
        'callback'    => '\local_teslace\observers::mod_forum_post_updated',
    ),
    array(
        'eventname'   => '\mod_forum\event\discussion_created',
        'callback'    => '\local_teslace\observers::mod_forum_discussion_created',
    ),
    array(
        'eventname'   => '\mod_forum\event\discussion_updated',
        'callback'    => '\local_teslace\observers::mod_forum_post_updated',
    ),
    # Mod quiz
    array(
        'eventname'   => 'mod_quiz\event\attempt_submitted',
        'callback'    => '\local_teslace\observers::mod_quiz_attempt_submitted',
    ),

);
