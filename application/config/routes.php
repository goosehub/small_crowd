<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['message/load'] = "message/load";
$route['message/new_message'] = "message/new_message";

$route['default_controller'] = 'main/start';
$route['join_room'] = 'main/join_room';
$route['room/(:any)'] = 'main/room/$1';

$route['error'] = 'main/error';
$route['about'] = 'main/main';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;