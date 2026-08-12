<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';

logout_user();

// Start a fresh session just to carry the flash message.
session_start();
set_flash('success', 'You have been logged out.');
redirect('login.php');