<?php

use PhpWaf\Wafcode;

//过滤危险内容
Wafcode::valid();

$configs = array(
  'template' =>
  array(
    'cmf_admin_default_theme' => 'admin_simpleboot3',
  ),
);

$configs["before_hosts"] = "http://localhost:3012";
$configs["Access-Control-Allow-Headers"] = "Origin,X-Requested-With,Content-Type,Accept";

return $configs;
