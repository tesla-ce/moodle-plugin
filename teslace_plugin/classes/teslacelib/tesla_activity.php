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

class TeslaActivity
{
    private $client = null;
    public function __construct($client, $common)
    {
        $this->client = $client;
        $this->common = $common;
    }

    public function create_or_update($event)
    {
        global $DB;
        $course_module = $DB->get_record('course_modules', array('id'=>$event->objectid), '*', MUST_EXIST);
        $mod_name = $DB->get_field('modules', 'name', array('id' =>$course_module->module));
        $activity = $DB->get_record($mod_name, array('id' =>$course_module->instance), '*', MUST_EXIST);

        $vle_id = $this->client->getVleId();
        $vle_course_id = $activity->course;
        $course = $this->client->getCourse()->getByVleCourseId($vle_id, $vle_course_id);

        if ($course['headers']['http_code'] == 200) {
            $course_id = $course['content']['results'][0]['id'];
        } else {
            // course not exist, create it.
        }

        $vle_activity_id = $activity->id;
        $vle_activity_type = $mod_name;
        $activity_name = $activity->name;
        $activity_description = $activity->intro;
        $activity_tesla = $this->client->getActivity()->getByVleActivityIdAndType($vle_id, $course_id, $vle_activity_id, $vle_activity_type);

        $start = null;
        $end = null;
        $startField = 'nonDefinedField';
        $endField = 'nonDefinedField';
        switch ($vle_activity_type) {
            case 'survey':
                // no date in survey
                break;
            case 'quiz':
            case 'assign':
                $startField = 'timeopen';
                $endField = 'timeclose';
                break;
            case 'forum':
                // $startField = 'timeopen';
                $endField = 'duedate';
                break;
        }

        if (property_exists($activity, $startField)) {
            $start = new \Datetime('@'.$activity->$startField);
        }
        if (property_exists($activity, $endField)) {
            $end = new \Datetime('@'.$activity->$endField);
        }

        if ($activity_tesla['headers']['http_code'] == 200 && count($activity_tesla['content']['results']) > 0) {
            $activity_id = $activity_tesla['content']['results'][0]['id'];

            $result = $this->client->getActivity()->update(
                $vle_id,
                $course_id,
                $activity_id,
                $vle_activity_type,
                $vle_activity_id,
                $activity_name,
                $activity_description,
                $start,
                $end
            );
        } else {
            // create activity, because in TeSLA this activity not exists.
            $result = $this->client->getActivity()->create(
                $vle_id,
                $course_id,
                $vle_activity_type,
                $vle_activity_id,
                $activity_name,
                $activity_description,
                $start,
                $end
            );
        }
    }

    public function queue_submission($info, $activityType, $activityId, $courseId)
    {
        global $DB;
        global $USER;

        // check if $user is student
        if ($this->common->is_user_with_role($courseId, Common::ROLE_STUDENT, $USER->id) === true) {
            // check if this submission is in queue
            $params = array(
                'info'=>$DB->sql_compare_text($info),
                'vle_course_id'=>$DB->sql_compare_text($courseId),
                'vle_learner_id'=>$DB->sql_compare_text($USER->email),
                'vle_activity_id'=>$DB->sql_compare_text($activityId),
                'vle_activity_type'=>$DB->sql_compare_text($activityType),
                'status'=>$DB->sql_compare_text('PENDING')
            );

            $select = 'info = :info AND vle_course_id = :vle_course_id AND vle_learner_id = :vle_learner_id AND 
            vle_activity_id = :vle_activity_id AND vle_activity_type = :vle_activity_type AND status = :status';
            $old_requests = $DB->get_records_select('local_teslace_pend_requests', $select, $params, '', '*');

            if ($old_requests && count($old_requests) > 0) {
                foreach ($old_requests as $old_request) {
                    $conditions = array(
                        'id' => $old_request->id
                    );
                    $DB->delete_records('local_teslace_pend_requests', $conditions);
                }
            }

            $dataobject = new \stdClass();
            $dataobject->info = $info;
            $dataobject->vle_course_id = $courseId;
            $dataobject->vle_learner_id = $USER->email;
            $dataobject->vle_activity_id = $activityId;
            $dataobject->vle_activity_type = $activityType;
            $dataobject->status = 'PENDING';
            $dataobject->counter = 0;
            $dataobject->created = time();
            $dataobject->modified = time();
            return $DB->insert_record('local_teslace_pend_requests', $dataobject, $returnid = true, $bulk = false);
        }

        return null;
    }

    public function send_submission($request)
    {
        $result = array(
            'result'=>true,
            'message'=>null
        );

        if (isset($this->client->getModule()['institution_id'])) {
            $institution_id = $this->client->getModule()['institution_id'];
        } else {
            $result['result'] = false;
            $result['message'] = 'INSTITUTION_ID_NOT_FOUND';
            return $result;
        }

        $vle_id = $this->client->getVleId();

        // get course ID
        $vle_course_id = $request->vle_course_id;
        $course = $this->client->getCourse()->getByVleCourseId($vle_id, $vle_course_id);

        if ($course['headers']['http_code'] !== 200) {
            $result['result'] = false;
            $result['message'] = 'COURSE_NOT_FOUND';
            return $result;
        }

        $course_id = $course['content']['results'][0]['id'];

        // get activity ID
        $activity = $this->client->getActivity()->getByVleActivityIdAndType(
            $vle_id,
            $course_id,
            $request->vle_activity_id,
            $request->vle_activity_type
        );

        if ($activity['headers']['http_code'] !== 200 || count($activity['content']['results']) == 0) {
            $result['result'] = false;
            $result['message'] = 'ACTIVITY_NOT_FOUND';
            return $result;
        }

        // check if activity is TeSLA enabled
        if ($activity['content']['results'][0]['enabled'] !== true) {
            $result['result'] = false;
            $result['message'] = 'ACTIVITY_NOT_ENABLED';
            return $result;
        }

        $activity_id = $activity['content']['results'][0]['id'];

        // get learner_id
        $learner = $this->client->getLearner()->getByUid($vle_id, $course_id, $request->vle_learner_id);
        if ($learner['headers']['http_code'] !== 200) {
            $result['result'] = false;
            $result['message'] = 'LEARNER_NOT_FOUND';
            return $result;
        }

        $learner_id = $learner['content']['results'][0]['learner_id'];

        // check if consent is VALID_
        $ic_status = $learner['content']['results'][0]['ic_status'];

        if (substr($ic_status, 0, 6) != 'VALID_') {
            $result['result'] = false;
            $result['message'] = 'LEARNER_ERROR_VALID_CONSENT';
            return $result;
        }

        $instruments = $this->client->getVerification()->canSend($vle_id, $course_id, $activity_id, $learner_id);

        if (!$instruments || ($instruments['content'] && count($instruments['content'])) == 0) {
            $result['result'] = false;
            $result['message'] = 'ACTIVITY_WITHOUT_INSTRUMENTS';
            return $result;
        }

        foreach ($instruments['content'] as $instrument) {
            $instrument_ids[] = $instrument['instrument'];
        }

        $data = $this->get_raw_data_metadata($request);

        if ($data == null) {
            $result['result'] = false;
            $result['message'] = 'DATA_METADATA_NOT_VALID';
            return $result;
        }

        $response = $this->client->getVerification()->send(
            $institution_id,
            $learner_id,
            $data['data'],
            $instrument_ids,
            $course_id,
            $activity_id,
            null,
            $data['metadata']
        );

        if (isset($response['result'])) {
            $result['result'] = $response['result'];
        }

        return $result;
    }

    private function get_raw_data_metadata($request)
    {
        switch ($request->vle_activity_type) {
            case 'forum':
                return $this->get_raw_data_metadata_forum_post($request);
            case 'assign':
                return $this->get_raw_data_metadata_assign_submission($request);
            case 'quiz':
                return $this->get_raw_data_metadata_quiz_attempt($request);
        }

        return null;
    }

    private function get_raw_data_metadata_quiz_attempt($request)
    {
        global $DB;

        $quiz_attempt = $DB->get_record('quiz_attempts', array('id'=>$request->info));
        $question_attempts = $DB->get_records('question_attempts', array('questionusageid'=>$quiz_attempt->uniqueid));

        $quiz_content = [];
        $attachments = array();
        foreach ($question_attempts as $question_attempt) {
            // get answer
            $answer = [];
            $question_attempt_steps = $DB->get_records('question_attempt_steps', array('questionattemptid'=>$question_attempt->id));
            foreach ($question_attempt_steps as $question_attempt_step) {
                $question_attempt_step_data_values = $DB->get_records('question_attempt_step_data', array('attemptstepid'=>$question_attempt_step->id));
                foreach ($question_attempt_step_data_values as $question_attempt_step_data_value) {
                    // register only 'answer' or 'sub%' or 'p%'
                    if ($question_attempt_step_data_value->name === 'answer' ||
                        strpos($question_attempt_step_data_value->name, 'sub') === 0 ||
                        strpos($question_attempt_step_data_value->name, 'p') === 0
                    ) {
                        $answer[] = $question_attempt_step_data_value->value;
                    } elseif ($question_attempt_step_data_value->name === 'attachments') {
                        $params = array(
                            'component'=>'question',
                            'filearea'=>'response_attachments',
                            'itemid'=>$question_attempt_step_data_value->attemptstepid
                        );
                        $attachments = $this->get_files($params);
                    }

                }
            }

            $question = $DB->get_record('question', array('id'=>$question_attempt->questionid));

            $open_text = false;
            $open_text_question_types = array('essay', 'shortanswer');

            if (in_array($question->qtype, $open_text_question_types) === true) {
                $open_text = true;
            }

            $quiz_content [] = array(
                'question'=>$question->name,
                'question_description'=>$question->questiontext,
                'question_type'=>$question->qtype,
                'open_text'=>$open_text,
                'answer'=>$answer
            );
        }

        $metadata = array(
            'mimetype'=>'application/zip',
            'context'=>array(
                'type'=>'quiz_attempt',
                'version'=>'1.0.0',
                'vle_id'=>$this->client->getVleId(),
            )
        );
        print_r($metadata);

        return array(
            'data'=>$this->generate_zip(json_encode($quiz_content), $attachments),
            'metadata'=>$metadata
        );

    }

    private function get_raw_data_metadata_forum_post($request)
    {
        global $DB;

        $forum_post = $DB->get_record('forum_posts', array('id'=>$request->info));

        $forum_content = json_encode(array(
            'subject'=>$forum_post->subject,
            'message'=>$forum_post->message,
        ));

        $metadata = array(
            'mimetype'=>'application/zip',
            'context'=>array(
                'wordcount' => $forum_post->wordcount,
                'charcount' => $forum_post->charcount,
                'type'=>'forum_post',
                'version'=>'1.0.0',
                'vle_id'=>$this->client->getVleId(),
            )
        );

        $attachments = array();

        if (boolval($forum_post->attachment) === true) {
            // Get file
            $params = array(
                'component'=>'mod_forum',
                'filearea'=>'attachment',
                'itemid'=>$forum_post->id
            );
            $attachments = $this->get_files($params);
        }

        return array(
            'data'=>$this->generate_zip($forum_content, $attachments),
            'metadata'=>$metadata
        );
    }

    private function get_raw_data_metadata_assign_submission($request)
    {
        // here
        global $DB;

        $assign_submission = $DB->get_record('assign_submission', array('id'=>$request->info));
        $assign_submission_online = $DB->get_record('assignsubmission_onlinetext',
            array('submission'=>$request->info));

        $assign_content = null;
        if ($assign_submission_online != null) {
            $assign_content = json_encode(array(
                'online_text' => $assign_submission_online->onlinetext
            ));
        }

        $metadata = array(
            'mimetype'=>'application/zip',
            'context'=>array(
                'type'=>($assign_submission_online == null ? 'assign' : 'assign_online'),
                'version'=>'1.0.0',
                'vle_id'=>$this->client->getVleId()
            ),
            'filename'=>'archive.zip'
        );

        // Get file
        $params = array(
            'component'=>'assignsubmission_file',
            'filearea'=>'submission_files',
            'itemid'=>$assign_submission->id
        );
        $attachments = $this->get_files($params);

        return array(
            'data'=>$this->generate_zip($assign_content, $attachments),
            'metadata'=>$metadata
        );
    }

    private function get_files($params)
    {
        $return_files = array();
        global $DB;

        $fs = get_file_storage();
        $filesinfo = $DB->get_records('files', $params, '', '*');

        if ($filesinfo && count($filesinfo) > 0) {
            foreach ($filesinfo as $fileinfo) {
                $file = $fs->get_file(
                    $fileinfo->contextid,
                    $fileinfo->component,
                    $fileinfo->filearea,
                    $fileinfo->itemid,
                    $fileinfo->filepath,
                    $fileinfo->filename
                );

                // Read contents
                if ($file) {
                    // file exists
                    $contents = $file->get_content();

                    if ($contents != '') {
                        // file is not empty
                        $return_files[] = array(
                            'content'=>$contents,
                            'filename'=>$fileinfo->filename,
                            'mimetype'=>$fileinfo->mimetype,
                            'size'=>$fileinfo->filesize,
                        );
                    }
                }
            }
        }

        return $return_files;
    }

    private function generate_zip($content, $attachments = array())
    {
        $zip = new \ZipArchive();

        // todo: is this correct place of temp file?
        $base_path =  __DIR__.'/../../tmp';

        if (!is_dir($base_path)) {
            mkdir($base_path, 0775, true);
        }
        $tmp_file = $base_path.'/archive.zip';

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }
        $res = $zip->open($tmp_file, \ZipArchive::CREATE);

        if ($content != null) {
            $zip->addFromString('content.txt', $content);
        }

        if ($attachments && count($attachments) > 0) {
            foreach ($attachments as $attachment) {
                $zip->addFromString('attachments/'.$attachment['filename'], $attachment['content']);
            }
        }

        $zip->close();

        $data_b64_content = base64_encode(file_get_contents($tmp_file));

        if (is_file($tmp_file)) {
            unlink($tmp_file);
        }

        return "data:application/zip;base64,{$data_b64_content}";
    }
}
