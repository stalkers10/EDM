
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - EDM</title>
  <link rel="stylesheet" href="Login.css">
</head>

<body>
  <nav>
    <h2 class="header-name">EDM login</h2>
  </nav>

  <div class="body-div">
    <form action="Login.php" method="post">
      <div class="Login-div">
        <p class="Login-pgh">Login</p>

        <div class="login-form">

          <label for="username">Username</label>

          <input type="text" name="username" id="log-username">

          <label for="password">Password </label>

          <input type="password" name="password" id="log-password">

        </div>

        <button id="btn-Login" name="login" type="submit">Login</button>

        <div class="selection-btm">

          <p>Don't have an account yet?</p>
          <a href="sign_up.php"><button type="button">Sign up</button></a>

        </div>

      </div>

    </form>
  </div>
</body>

<?php
include('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  $username = $_POST["username"];
  $pswd = $_POST["password"];
  $hash = password_hash($pswd, PASSWORD_DEFAULT);

  if (empty($username)) {
    echo "enter username <br>";
  } else if (empty($pswd)) {
    echo "enter password <br>";
  } else {
    $_SESSION["username"] = $username;
    $_SESSION["password"] = $hash;

    $stmt = $conn->prepare("select * from users where username =?");
    $stmt ->bind_param("s", $username);
    $stmt->execute();

    header("Location: admin_dash.php");
  }
}

?>

</html>