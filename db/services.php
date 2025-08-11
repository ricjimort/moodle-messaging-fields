<?php
defined('MOODLE_INTERNAL') || die();

$functions = [
    'local_pmerge_send' => [
        'classname'   => 'local_pmerge\external\send',
        'methodname'  => 'execute',
        'description' => get_string('ws:senddesc', 'local_pmerge'),
        'type'        => 'write',
        'ajax'        => true,
        'capabilities'=> 'local/pmerge:send'
    ],
];
