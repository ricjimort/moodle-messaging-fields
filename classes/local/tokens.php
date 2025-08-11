<?php
namespace local_pmerge\local;

defined('MOODLE_INTERNAL') || die();

class tokens {
    public static function replace($text, $user, $course = null) {
        $text = (string)$text;

        $firstname = isset($user->firstname) ? $user->firstname : '';
        if (function_exists('fullname')) {
            $fullname = fullname($user);
        } else {
            $fullname = trim(($user->firstname ?? '') . ' ' . ($user->lastname ?? ''));
        }
        $coursename = ($course && isset($course->fullname)) ? $course->fullname : '';

        $text = str_replace(['{{firstname}}','{{fullname}}','{{coursename}}'],
                            [$firstname,       $fullname,     $coursename], $text);

        return $text;
    }
}
