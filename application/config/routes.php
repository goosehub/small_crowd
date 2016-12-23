<?php
defined('BASEPATH') OR exit('No direct script access allowed');

// Chat functions
$route['chat/load'] = "chat/load";
$route['chat/new_chat'] = "chat/new_chat";

$route['default_controller'] = 'main/start';
$route['new'] = 'main/new_room';
$route['room/(:any)'] = 'main/room/$1';

$route['error'] = 'main/error';
$route['about'] = 'main/main';

$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;