<?php
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ✅ Define routes
switch ($request) {
    case '/':
    case '/login':
        require __DIR__ . '/router.php';
        break;

    case '/register':
        require __DIR__ . '/pages/register.php';
        break;

    case '/employee/dashboard':
        require __DIR__ . '/pages/Employee/dashboard.php';
        break;

    case '/employee/myaccount':
        require __DIR__ . '/pages/Employee/myaccount.php';
        break;    

     case '/employee/attendancereport':
        require __DIR__ . '/pages/Employee/attendancereport.php';
        break;   

    case '/hr/dashboard':
        require __DIR__ . '/pages/HR/dashboard.php';
        break;

    case '/admin/dashboard':
        require __DIR__ . '/pages/Admin/dashboard.php';
        break;
  
    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        break;
}
