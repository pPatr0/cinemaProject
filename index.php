<?php
$uri = $_SERVER['REQUEST_URI'];                
$root = '/cinemaProject-main/views';           
$new = $root . $uri;                           

header('Location: ' . $new, true, 302);
exit;