<?php
namespace local_pmerge\external;

defined('MOODLE_INTERNAL') || die();

use external_api;
use external_function_parameters;
use external_value;
use external_single_structure;

class send extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'userid'   => new external_value(PARAM_INT, 'ID del usuario destinatario'),
            'message'  => new external_value(PARAM_RAW, 'Mensaje con placeholders'),
            'courseid' => new external_value(PARAM_INT, 'ID del curso (opcional)', VALUE_DEFAULT, 0),
        ]);
    }

    public static function execute($userid, $message, $courseid = 0) {
        global $USER;

        self::validate_parameters(self::execute_parameters(), [
            'userid' => $userid,
            'message' => $message,
            'courseid' => $courseid
        ]);

        require_capability('local/pmerge:send', \context_system::instance());

        $user = \core_user::get_user($userid, '*', MUST_EXIST);
        $course = $courseid ? get_course($courseid) : null;

        $message = \local_pmerge\local\tokens::replace($message, $user, $course);

        $msg = new \core\message\message();
        $msg->component         = 'moodle';
        $msg->name              = 'instantmessage';
        $msg->userfrom          = $USER;
        $msg->userto            = $user;
        $msg->subject           = '';
        $msg->fullmessage       = $message;
        $msg->fullmessageformat = FORMAT_HTML;
        $msg->fullmessagehtml   = $message;
        $msg->smallmessage      = '';
        $msg->notification      = '0';

        \core_message\api::send_message($msg);

        return ['status' => 'ok'];
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Resultado'),
        ]);
    }
}
