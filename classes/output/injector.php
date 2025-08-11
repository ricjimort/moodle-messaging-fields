<?php
namespace local_pmerge\output;
defined('MOODLE_INTERNAL') || die();

class injector {
    public static function require_js(): void {
        global $PAGE;
        $PAGE->requires->js_call_amd('local_pmerge/pmerge', 'init', []);
    }
}
