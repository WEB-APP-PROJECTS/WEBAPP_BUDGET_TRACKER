<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Budget Tracker – Signup</title>
  <link rel="stylesheet" href="styles3.css"/>
</head>
<body>
  <div class="container">
    <div class="login-box">
      <h2>Budget Tracker</h2>
      <h3>Sign Up</h3>

      <?php if (!empty($_GET['error'])): ?>
        <p class="error"><?= htmlspecialchars($_GET['error']) ?></p>
      <?php endif; ?>

      <form action="process_register.php" method="POST">
        <div class="input-box">
          <input type="text" name="username" required />
          <label>User Name</label>
        </div>
        <div class="input-box">
          <input type="email" name="email" required />
          <label>Email</label>
        </div>
        <div class="input-box">
          <input type="password" name="password" required />
          <label>Password</label>
        </div>
        <div class="input-box">
          <input type="password" name="confirm_password" required />
          <label>Confirm Password</label>
        </div>

        <button type="submit" class="btn">Signup</button>
        <div class="signup-link">
          <a href="index.php">Already have an account? Login</a>
        </div>
      </form>
    </div>
  </div>
</body>
</html>