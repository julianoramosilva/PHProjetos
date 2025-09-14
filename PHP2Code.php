<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>

<?php
// Diretório base onde arquivos podem ser salvos
$baseDir = __DIR__ . '/';

// Função para listar os diretórios dentro da pasta base
function listarDiretorios($path) {
    $diretorios = [];
    foreach (scandir($path) as $item) {
        if ($item === '.' || $item === '..') continue;
        if (is_dir($path . '/' . $item)) {
            $diretorios[] = $item;
        }
    }
    return $diretorios;
}

// Lógica de salvamento
$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dir = basename($_POST['diretorio']);
    $nomeArquivo = basename($_POST['nome_arquivo']);
    $conteudo = $_POST['conteudo'];

    // Validação
    if (empty($dir) || empty($nomeArquivo)) {
        $msg = "Diretório e nome do arquivo são obrigatórios.";
    } elseif (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $nomeArquivo)) {
        $msg = "Nome do arquivo inválido.";
    } else {
        $caminhoCompleto = "$baseDir/$dir/$nomeArquivo";
        if (!is_dir("$baseDir/$dir")) {
            $msg = "Diretório não existe.";
        } else {
            file_put_contents($caminhoCompleto, $conteudo);
            $msg = "Arquivo salvo com sucesso em <strong>$dir/$nomeArquivo</strong>!";
        }
    }
}

$diretorios = listarDiretorios($baseDir);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Salvar Arquivo</title>
  <style>
    body {
      background-color: #1e1e1e;
      color: #f0f0f0;
      font-family: Arial, sans-serif;
      padding: 2rem;
    }
    h1 {
      color: #00bfff;
    }
    label {
      display: block;
      margin: 1rem 0 0.3rem;
    }
    select, input[type="text"], textarea {
      width: 100%;
      padding: 0.5rem;
      background-color: #2b2b2b;
      border: 1px solid #444;
      color: #fff;
      border-radius: 5px;
    }
    textarea {
      height: 200px;
      font-family: monospace;
    }
    button {
      margin-top: 1rem;
      background-color: #00bfff;
      border: none;
      padding: 0.6rem 1.2rem;
      color: #000;
      font-weight: bold;
      border-radius: 5px;
      cursor: pointer;
    }
    .mensagem {
      margin-top: 1rem;
      padding: 0.8rem;
      border-radius: 5px;
      background-color: #2b2b2b;
      border: 1px solid #444;
    }
  </style>
</head>
<body>

  <h1>Salvar Projeto: Root Folder</h1>

  <?php if ($msg): ?>
    <div class="mensagem"><?= $msg ?></div>
  <?php endif; ?>

  <form method="POST">
    <label for="diretorio">Selecione o diretório: Em root folder:</label>
    <select name="diretorio" required>
      <option value="">-- Escolha um diretório --</option>
      <?php foreach ($diretorios as $dir): ?>
        <option value="<?= htmlspecialchars($dir) ?>"><?= htmlspecialchars($dir) ?></option>
      <?php endforeach; ?>
    </select>

    <label for="nome_arquivo">Nome do Arquivo (ex: index.php):</label>
    <input type="text" name="nome_arquivo" required placeholder="ex: nova_pagina.php">

    <label for="conteudo">Conteúdo do Arquivo:</label>
    <textarea name="conteudo" placeholder="Digite o código aqui..."></textarea>

    <button type="submit">Salvar Arquivo</button>
  </form>

</body>
</html>
