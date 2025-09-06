<?php
/*
|--------------------------------------------------------------------------
| SUPER SIMPLE APP ROUTER - BYPASSES LARAVEL COMPLETELY!
|--------------------------------------------------------------------------
| This works directly with Apache/MAMP without any Laravel routing issues
*/

// Just redirect to the login page for now - KISS principle!
header('Location: /hospital/public/login.php');
exit;
