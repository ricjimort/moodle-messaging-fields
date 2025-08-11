<?php
defined('MOODLE_INTERNAL') || die();

function local_pmerge_extend_navigation(global_navigation $nav) {
    if (empty(get_config('local_pmerge', 'enable'))) { return; }
    if (!isloggedin() || CLI_SCRIPT) { return; }

    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    if (strpos($script, '/admin/') === 0 || strpos($script, '/login/') === 0) { return; }

    $scope = get_config('local_pmerge', 'scope') ?: 'courseonly';
    $uri = $_SERVER['REQUEST_URI'] ?? '';
    $allow = false;

    if ($scope === 'everywhere') {
        $allow = true;
    } else {
        if (strpos($uri, '/course/view.php') !== false ||
            strpos($uri, '/user/index.php') !== false ||
            strpos($uri, '/message/') !== false) {
            $allow = true;
        }
    }
    if (!$allow) { return; }

    \local_pmerge\output\injector::require_js();
}
