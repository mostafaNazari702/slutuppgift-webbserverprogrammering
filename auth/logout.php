<?php
require_once __DIR__ . '/../includes/auth.php';
logout_user();
session_start();
flash('info', 'Du är utloggad.');
redirect('/index.php');
