<?php
require('inc/dbPlayer.php');
require('inc/sessionManager.php');
$msg="";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST["btnLogin"])) {
        $db = new \dbPlayer\dbPlayer();
        $msg = $db->open();
        if ($msg == "true") {
            $userPass = md5("hms2015".$_POST['password']);
            $loginId = $_POST["email"];
            $query = "select loginId,userGroupId,password,name,userId from users 
                      where loginId='" . $loginId . "' and password='" . $userPass . "'";
            $result = $db->getData($query);
            $info = array();
            while ($row = mysqli_fetch_assoc($result)) {
                array_push($info, $row['loginId']);
                array_push($info, $row['userGroupId']);
                array_push($info, $row['password']);
                array_push($info, $row['name']);
                array_push($info, $row['userId']);
            }
            $ses = new \sessionManager\sessionManager();
            $ses->start();
            $ses->Set("loginId", $info[0]);
            $ses->Set("userGroupId", $info[1]);
            $ses->Set("name", $info[3]);
            $ses->Set("userIdLoged", $info[4]);

            if (is_null($info[0])) {
                $msg = "Login Id or Password Wrong!";
            }

            if($info[1]=="UG004") {
                header('Location: http://localhost/hms/sdashboard.php');
            } elseif($info[1]=="UG003") {
                header('Location: http://localhost/hms/edashboard.php');
            } else {
                header('Location: http://localhost/hms/dashboard.php');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Hostel Management System - Login</title>

    <!-- Bootstrap Core CSS -->
    <link href="./dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="./dist/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body, html {
            height: 100%;
            font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f5f6fa;
            overflow-x: hidden;
        }

        .main-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .login-wrapper {
            width: 100%;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            overflow: hidden;
            display: flex;
            min-height: 500px;
        }

        .left-panel {
            flex: 1;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-image: url('./dist/images/bg.png');
            background-size: contain;
            background-position: center center;
            background-repeat: no-repeat;
            position: relative;
            min-width: 400px;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
        }

        .right-panel {
            flex: 1;
            padding: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: white;
        }

        .login-form-container {
            width: 100%;
            max-width: 400px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
            color: #2c3e50;
            font-weight: 600;
            margin-bottom: 10px;
            font-size: 28px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 16px;
        }

        .logo {
            max-width: 120px;
            margin: 0 auto 20px;
            display: block;
        }

        .form-group {
            margin-bottom: 20px;
            position: relative;
        }

        .form-control {
            height: 50px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            padding: 0 15px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .form-control:focus {
            border-color: #667eea;
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .checkbox-wrapper {
            display: flex;
            align-items: center;
        }

        .checkbox-wrapper input[type="checkbox"] {
            margin-right: 8px;
        }

        .forgot-link {
            color: #e74c3c;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .forgot-link:hover {
            color: #c0392b;
            text-decoration: underline;
        }

        .btn-login {
            width: 100%;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .error-message {
            background: #fee;
            color: #e74c3c;
            padding: 10px 15px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: center;
            border-left: 4px solid #e74c3c;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .main-container {
                padding: 20px;
            }

            .login-wrapper {
                flex-direction: column;
                margin: 0;
                border-radius: 10px;
            }

            .left-panel {
                min-height: 250px;
                order: 1;
                min-width: auto;
                background-size: contain;
            }

            .right-panel {
                padding: 30px 20px;
                order: 2;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }

        @media (max-width: 480px) {
            .right-panel {
                padding: 20px 15px;
            }

            .form-control {
                height: 45px;
                font-size: 15px;
            }

            .btn-login {
                height: 45px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="main-container">
        <div class="login-wrapper">
            <!-- Left Panel with Background Image -->
            <div class="left-panel"></div>

            <!-- Right Panel with Login Form -->
            <div class="right-panel">
                <div class="login-form-container">
                    <div class="login-header">
                        <img src="./dist/images/logo3.png" alt="HMS Logo" class="logo">
                        <h2>Welcome Back</h2>
                        <p>Hostel Management System</p>
                    </div>

                    <?php if (!empty($msg) && $msg != "true"): ?>
                        <div class="error-message">
                            <i class="fa fa-exclamation-triangle"></i> <?php echo htmlspecialchars($msg); ?>
                        </div>
                    <?php endif; ?>

                    <form name="login" action="index.php" method="post">
                        <div class="form-group">
                            <input 
                                class="form-control" 
                                placeholder="Enter your email or login ID" 
                                name="email" 
                                type="text" 
                                required
                                autocomplete="username"
                            >
                        </div>

                        <div class="form-group">
                            <input 
                                class="form-control" 
                                placeholder="Enter your password" 
                                name="password" 
                                type="password" 
                                required
                                autocomplete="current-password"
                            >
                        </div>

                        <div class="form-options">
                            <div class="checkbox-wrapper">
                                <input name="remember" type="checkbox" id="remember">
                                <label for="remember">Remember Me</label>
                            </div>
                            <a href="forget.html" class="forgot-link">Forgot Password?</a>
                        </div>

                        <button type="submit" name="btnLogin" class="btn-login">
                            <i class="fa fa-sign-in"></i>
                            Sign In
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="./dist/js/jquery.min.js"></script>
    <script src="./dist/js/bootstrap.min.js"></script>
</body>
</html>