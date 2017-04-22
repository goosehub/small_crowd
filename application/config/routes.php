<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$route['load'] = "main/load";
$route['users_load'] = "main/users_load";
$route['new_message'] = "main/new_message";

$route['default_controller'] = 'main/start';
$route['join_start/(:any)'] = 'main/join_start/$1';
$route['join_room/(:any)'] = 'main/join_room/$1';
$route['join_room'] = 'main/join_room';
$route['room/(:any)'] = 'main/room/$1';

$route['cron/(:any)'] = "main/cron/$1";

$route['about'] = 'main/about';

$route['error'] = 'main/error';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;