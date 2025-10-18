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

    case '/hr/attendancereport':
        require __DIR__ . '/pages/HR/attendancereport.php';
        break;

    case '/hr/employeedb':
        require __DIR__ . '/pages/HR/employeedb.php';
        break;        

    case '/hr/myaccount':
        require __DIR__ . '/pages/HR/myaccount.php';
        break;  
    
    case '/hr/generatepayslip':
        require __DIR__ . '/pages/HR/generatepayslip.php';
        break; 

    case '/admin/dashboard':
        require __DIR__ . '/pages/Admin/dashboard.php';
        break;

    case '/admin/employeetrack':
        require __DIR__ . '/pages/Admin/employeetracking.php';
        break;

    case '/admin/allusers':
        require __DIR__ . '/pages/Admin/allusers.php';
        break;
    case '/admin/payslipdata':
        require __DIR__ . '/pages/Admin/payslipdata.php';
        break;
  
    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        break;
}
