<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['load'] = "main/load";
$route['new_message'] = "main/new_message";

$route['default_controller'] = 'main/start';
$route['join_room'] = 'main/join_room';
$route['leave_room'] = "main/leave_room";
$route['room/(:any)'] = 'main/room/$1';

$route['error'] = 'main/error';
$route['about'] = 'main/main';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;