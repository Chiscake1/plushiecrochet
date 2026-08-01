<?php
session_start();

// Si ya está logueado, redirigir al panel
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: /sistemainterno');
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login Administrativo | Plushie Crochet</title>
  <link rel="icon" href="img/logonew.png" type="image/png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --primary-color: #fca311;
      --secondary-color: #ff914d;
      --bg-color: #fdfdfd;
      --text-color: #333;
    }
    body {
      font-family: 'Poppins', sans-serif;
      background: var(--bg-color);
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      margin: 0;
      color: var(--text-color);
    }
    .login-container {
      background: white;
      padding: 40px;
      border-radius: 20px;
      box-shadow: 0 10px 30px rgba(0,0,0,0.05);
      text-align: center;
      max-width: 400px;
      width: 90%;
      border: 1px solid #eee;
    }
    .login-logo {
      width: 100px;
      height: 100px;
      border-radius: 50%;
      object-fit: cover;
      margin-bottom: 20px;
      border: 3px solid var(--primary-color);
    }
    h2 {
      margin-top: 0;
      margin-bottom: 10px;
      font-size: 1.8rem;
    }
    p {
      color: #777;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
      text-align: left;
    }
    label {
      display: block;
      margin-bottom: 8px;
      font-weight: 500;
    }
    input[type="password"] {
      width: 100%;
      padding: 12px 15px;
      border: 2px solid #eee;
      border-radius: 10px;
      font-size: 1rem;
      font-family: inherit;
      box-sizing: border-box;
      transition: border-color 0.3s;
    }
    input[type="password"]:focus {
      outline: none;
      border-color: var(--primary-color);
    }
    .btn {
      background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
      color: white;
      border: none;
      padding: 14px 20px;
      font-size: 1.1rem;
      font-weight: 600;
      border-radius: 10px;
      cursor: pointer;
      width: 100%;
      transition: transform 0.2s, box-shadow 0.2s;
    }
    .btn:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(252, 163, 17, 0.3);
    }
    .error-msg {
      color: #e74c3c;
      background: #fcebeb;
      padding: 10px;
      border-radius: 8px;
      margin-bottom: 20px;
      font-size: 0.9rem;
      display: none;
    }
  </style>
</head>
<body>

  <div class="login-container">
    <img src="img/logonew.png" alt="Logo" class="login-logo">
    <h2>Sistema Interno</h2>
    <p>Ingresa la contraseña maestra para continuar</p>

    <div id="error-message" class="error-msg"></div>

    <form id="login-form">
      <div class="form-group">
        <label for="password">Contraseña</label>
        <input type="password" id="password" name="password" required autofocus placeholder="••••••••">
      </div>
      <button type="submit" class="btn">Acceder</button>
    </form>
  </div>

  <script>
    document.getElementById('login-form').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const pwd = document.getElementById('password').value;
      const formData = new FormData();
      formData.append('action', 'login');
      formData.append('password', pwd);

      fetch('api_admin.php', {
        method: 'POST',
        body: formData
      })
      .then(res => res.json())
      .then(data => {
        if (data.success) {
          window.location.href = '/sistemainterno';
        } else {
          const err = document.getElementById('error-message');
          err.textContent = data.message;
          err.style.display = 'block';
          document.getElementById('password').value = '';
        }
      })
      .catch(err => {
        const errorDiv = document.getElementById('error-message');
        errorDiv.textContent = 'Error de conexión.';
        errorDiv.style.display = 'block';
      });
    });
  </script>
</body>
</html>
