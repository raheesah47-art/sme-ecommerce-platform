<?php
declare(strict_types=1);
const SITE_NAME='SMEConnect';const DEV_MODE=true;const DB_HOST='localhost';const DB_NAME='smeconnect';const DB_USER='root';const DB_PASS='';const DB_CHARSET='utf8mb4';
define('MAURITIUS_DISTRICTS',['Port Louis','Plaines Wilhems','Moka','Flacq','Grand Port','Pamplemousses','Riviere du Rempart','Savanne','Black River','Rodrigues']);
$scheme=(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off')?'https':'http';$host=$_SERVER['HTTP_HOST']??'localhost';$script=str_replace('\\','/',$_SERVER['SCRIPT_NAME']??'');$base=preg_replace('#/(pages|sme|admin)(/.*)?$#','',$script);$base=rtrim(str_replace('/index.php','',$base),'/');define('SITE_URL',$scheme.'://'.$host.$base);
if(session_status()!==PHP_SESSION_ACTIVE){session_set_cookie_params(['httponly'=>true,'samesite'=>'Lax','secure'=>!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off']);session_start();}
