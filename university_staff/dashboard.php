<?php
require_once __DIR__ . '/../includes/auth.php';
app_require_role(['staff'], '../login.php');
$dashboardRoles = ['staff'];
require_once __DIR__ . '/../student/dashboard.php';
