<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign up - EDM</title>
    <link rel="stylesheet" href="../login_logout/Login.css">
    <style>
        #role {
            width: 55%;
            padding: 6px;
            border: 1px solid #ddd;
            border-radius: 7px;
            background-color: #f4f7f6;
            font-size: 18px;
        }
    </style>
</head>

<body>
    <nav>
        <div>
            <h2 class="header-name">Sign up for EDM</h2>
        </div>
    </nav>

    <main class="body-div">
        <form action="sign_up.php" method="post">
            <div class="Login-div">
                <h1 class="Login-pgh">Sign up</h1>

                <div class="login-form">

                    <label for="username">Full Name</label>
                    <input type="text" name="fullname" id="reg-fullname">

                    <label for="username">Phone Number</label>
                    <input type="text" name="phone_num" id="reg-phone_num">

                    <label for="username">Email</label>
                    <input type="text" name="email" id="reg-email">

                    <label for="username">Username</label>
                    <input type="text" name="username" id="reg-username">

                    <label for="password">Password </label>
                    <input type="password" name="password" id="reg-password">

                    <label for="role">Role</label>

                    <select id="role" name="role" onchange="toggleSecurityKey()">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>

                    <div id="security-key-group" class="form-group" style="display: none;">
                        <label for="security_key">Security Key</label>
                        <input type="password" id="security_key" name="security_key" placeholder="Enter Admin Key">
                    </div>

                </div>

                <button id="btnSignUp" name="signup" type="submit">Sign up</button>

                <div class="selection-btm">

                    <p>Aready have an account?</p>

                    <a href="../login_logout/Login.php"><button type="button">Login</button></a>

                </div>
            </div>
        </form>
    </main>
    <script>
        function toggleSecurityKey() {
            const roleSelect = document.getElementById('role');
            const securityGroup = document.getElementById('security-key-group');

            // Show the field only if 'admin' is selected
            if (roleSelect.value === 'admin') {
                securityGroup.style.display = 'block';
            } else {
                securityGroup.style.display = 'none';
            }
        }
    </script>
    <?php
    include("../DB/database.php");

    if ($_SERVER["REQUEST_METHOD"] == "POST") {

        $username = $_POST["username"];
        $pswd = $_POST["password"];
        $email = $_POST["email"];
        $fullname = $_POST["fullname"];
        $phone_num = $_POST["phone_num"];
        $hash = password_hash($pswd, PASSWORD_DEFAULT);

        if (empty($username)) {
            echo "<script> alert('enter username')</script>";
        } else if (empty($pswd)) {
            echo "<script> alert('enter password')</script>";
        } else if (empty($email)) {
            echo "<script> alert('enter email')</script>";
        } else if (empty($fullname)) {
            echo "<script> alert('enter fullname')</script>";
        } else if (empty($phone_num)) {
            echo "<script> alert('enter phone number')</script>";
        } else {
            $_SESSION["username"] = $username;
            $_SESSION["password"] = $hash;
            $_SESSION["email"] = $email;
            $_SESSION["fullname"] = $fullname;
            $_SESSION["phone_num"] = $phone_num;
            $role = $_POST['role'];
            $security_key = $_POST['security_key'] ?? '';

            if ($role === 'admin' && $security_key !== 'Admin123') {
                echo "<script>alert('Invalid Admin Key');
                window.location.href='sign_up.php';</script>";
                exit();
            }

            try {
                $sql_user = "INSERT INTO users (email, username, passwd, phone_num, role, status) 
                 VALUES ('$email', '$username', '$hash', '$phone_num', '$role', 1)";

                if (mysqli_query($conn, $sql_user)) {
                    echo "<script>alert('Account created successfully!'); window.location.href='../login_logout/Login.php';</script>";
                } else {
                    if (mysqli_errno($conn) == 1062) {
                        echo "<script>alert('Duplicate Error: The Username or Email already exists.');</script>";
                    } else {
                        echo "Error creating user account: " . mysqli_error($conn);
                    }
                }
            } catch (Exception $e) {
                echo "An unexpected error occurred: " . $e->getMessage();
            }
        }
    }

    ?>

</body>

</html>