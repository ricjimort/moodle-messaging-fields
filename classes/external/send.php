<?php
namespace local_pmerge\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;
use context_system;

class send extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid'  => new external_value(PARAM_INT, 'ID destino'),
            'message' => new external_value(PARAM_RAW, 'Mensaje con placeholders'),
            'courseid'=> new external_value(PARAM_INT, 'Curso (opcional)', VALUE_DEFAULT, 0),
            'subject' => new external_value(PARAM_TEXT, 'Asunto (opcional)', VALUE_DEFAULT, '')
        ]);
    }

    public static function execute($userid, $message, $courseid = 0, $subject = '') {
        global $DB, $USER;
        self::validate_context(context_system::instance());
        self::require_capability('local/pmerge:send', context_system::instance());

        $params = self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
            'message' => $message,
            'courseid' => $courseid,
            'subject' => $subject
        ]);

        $touser = $DB->get_record('user', ['id' => $params['userid'], 'deleted' => 0], '*', MUST_EXIST);
        $course = $params['courseid'] ? $DB->get_record('course', ['id' => $params['courseid']], '*', IGNORE_MISSING) : null;

        $text = \local_pmerge\local\tokens::replace($params['message'], $touser, $course);

        $msg = new \core\message\message();
        $msg->component         = 'moodle';
        $msg->name              = 'instantmessage';
        $msg->userfrom          = $USER;
        $msg->userto            = $touser;
        $msg->subject           = $params['subject'];
        $msg->fullmessage       = $text;
        $msg->fullmessageformat = FORMAT_PLAIN;
        $msg->fullmessagehtml   = '';
        $msg->smallmessage      = '';
        $msg->notification      = 0;

        \core_message\api::send_message($msg);

        return ['status' => 'ok'];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Resultado'),
        ]);
    }
}
