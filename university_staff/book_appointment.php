<?php
require_once __DIR__ . '/../includes/auth.php';
app_require_role(['staff'], '../login.php');
$bookingRoles = ['staff'];
require_once __DIR__ . '/../student/book_appointment.php';
