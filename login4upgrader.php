<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>

<?php
session_start();
$host = 'localhost';
$db = 'u281407572_titansperdidos';
$user = 'u281407572_julianoramos';
$pass = 'Jr#125690';
$conn = new mysqli($host, $user, $pass, $db);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    $stmt = $conn->prepare("SELECT id, senha, grupo_id FROM usuarios WHERE email=?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $senha_hash, $grupo_id);
        $stmt->fetch();

        if (password_verify($senha, $senha_hash)) {
            $_SESSION['user_id'] = $id;
            $_SESSION['grupo_id'] = $grupo_id;
            header("Location: painel.php");
            exit();
        } else {
            $erro = "Senha incorreta.";
        }
    } else {
        $erro = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Titãs Perdidos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #121212;
      color: #00ffc8;
    }
    .login-box {
      max-width: 400px;
      margin: 100px auto;
      background-color: #1f1f1f;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 0 10px #00ffc8;
    }
    .form-control, .btn {
      background-color: #1a1a1a;
      color: #00ffc8;
      border-color: #00ffc8;
    }
  </style>
</head>
<body>
  <div class="login-box">
    <h3 class="text-center">🔐 Login</h3>
    <?php if (isset($erro)) echo "<div class='alert alert-danger'>$erro</div>"; ?>
    <form method="POST">
      <div class="mb-3">
        <input type="email" name="email" class="form-control" placeholder="Email" required />
      </div>
      <div class="mb-3">
        <input type="password" name="senha" class="form-control" placeholder="Senha" required />
      </div>
      <button class="btn btn-outline-info w-100">Entrar</button>
    </form>
  </div>
</body>
</html>
