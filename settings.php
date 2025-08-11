<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_pmerge', get_string('pluginname', 'local_pmerge'));

    $settings->add(new admin_setting_configcheckbox(
        'local_pmerge/enable',
        get_string('enable', 'local_pmerge'),
        get_string('enable_desc', 'local_pmerge'),
        1
    ));

    $settings->add(new admin_setting_configtext(
        'local_pmerge/batchlimit',
        get_string('batchlimit', 'local_pmerge'),
        get_string('batchlimit_desc', 'local_pmerge'),
        200,
        PARAM_INT
    ));

    $settings->add(new admin_setting_configselect(
        'local_pmerge/scope',
        get_string('scope', 'local_pmerge'),
        get_string('scope_desc', 'local_pmerge'),
        'courseonly',
        [
            'courseonly' => get_string('scope_courseonly', 'local_pmerge'),
            'everywhere' => get_string('scope_everywhere', 'local_pmerge')
        ]
    ));

    $ADMIN->add('localplugins', $settings);
}
