<?php
$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// ✅ Define routes
switch ($request) {
    case '/':
    case '/timein':
        require __DIR__ . '/router.php';
        break;

    case '/register':
        require __DIR__ . '/pages/register.php';
        break;

    case '/employee/dashboard':
        require __DIR__ . '/pages/Employee/dashboard.php';
        break;

    case '/employee/paysliprecord':
        require __DIR__ . '/pages/Employee/paysliprecord.php';
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

    case '/hr/hraccount':
        require __DIR__ . '/pages/HR/hraccount.php';
        break;  
    
    case '/hr/generatepayslip':
        require __DIR__ . '/pages/HR/generatepayslip.php';
        break; 

    case '/hr/employeetracking':
        require __DIR__ . '/pages/HR/employeetracking.php';
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

    case '/admin/generatepayslip':
        require __DIR__ . '/pages/Admin/generatepayslip.php';
        break;


    case '/logout':
        require __DIR__ . '/pages/logout.php';
        break;
    
    case '/timeout':
        require __DIR__ . '/timeout.php';
        break;

    case '/breaktime':
        require __DIR__ . '/break.php';
        break;

    case '/login':
        require __DIR__ . '/login.php';
        break;
  
    default:
        http_response_code(404);
        echo "<h1>404 - Page Not Found</h1>";
        break;
}
