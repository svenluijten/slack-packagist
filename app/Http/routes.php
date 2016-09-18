<?php

$app->get('hook', 'SlackController@hook');
$app->get('auth', 'SlackController@auth');

$app->get('/', 'PagesController@home');
$app->get('installed', 'PagesController@installed');
$app->get('privacy', 'PagesController@privacy');
$app->get('support', 'PagesController@support');