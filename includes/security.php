<?php
function csrf_token(): string { if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token']=bin2hex(random_bytes(32)); return $_SESSION['csrf_token']; }
function csrf_field(): string { return '<input type="hidden" name="csrf_token" value="'.htmlspecialchars(csrf_token(), ENT_QUOTES, 'UTF-8').'">'; }
function csrf_verify(?string $token): bool { return !empty($_SESSION['csrf_token']) && !empty($token) && hash_equals($_SESSION['csrf_token'],$token); }
function csrf_protect(): void { if ($_SERVER['REQUEST_METHOD']==='POST' && !csrf_verify($_POST['csrf_token']??null)) { http_response_code(403); exit('Invalid or missing security token. Please refresh the page and try again.'); } }
function e(?string $value): string { return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8'); }
