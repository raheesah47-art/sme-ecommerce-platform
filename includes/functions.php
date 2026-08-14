<?php
declare(strict_types=1);
function e(?string $v):string{return htmlspecialchars($v??'',ENT_QUOTES,'UTF-8');}
function redirect(string $path):never{header('Location: '.SITE_URL.'/'.ltrim($path,'/'));exit;}
function set_flash(string $type,string $message):void{$_SESSION['flash']=['type'=>$type,'message'=>$message];}
function get_flash():?array{$f=$_SESSION['flash']??null;unset($_SESSION['flash']);return $f;}
function render_flash():void{$f=get_flash();if($f)echo '<div class="flash-message flash-'.e($f['type']).'">'.e($f['message']).'</div>';}
function clean_input(?string $v):?string{$v=trim((string)$v);return $v===''?null:$v;}
function is_valid_email(string $v):bool{return filter_var($v,FILTER_VALIDATE_EMAIL)!==false;}
function is_valid_password(string $v):bool{return strlen($v)>=8&&preg_match('/[A-Za-z]/',$v)&&preg_match('/[0-9]/',$v);}
function is_valid_phone(string $v):bool{return (bool)preg_match('/^(\+230)?[0-9]{7,8}$/',str_replace(' ','',$v));}
function cart_count(PDO $pdo):int{if(empty($_SESSION['cart']))return 0;$ids=array_keys($_SESSION['cart']);return array_sum($_SESSION['cart']);}
