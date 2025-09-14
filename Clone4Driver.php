<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/PHP2Manager/menu.html'); ?>
<?php
// CONFIGURAÇÃO
$baseDir = realpath(__DIR__ . '/'); // Substitua por seu diretório
$currentDir = isset($_GET['dir']) ? realpath($baseDir . '/' . $_GET['dir']) : $baseDir;

// Impede acesso fora do diretório base
if (strpos($currentDir, $baseDir) !== 0) {
    $currentDir = $baseDir;
}

// Criar pasta
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['nova_pasta'])) {
    $novaPasta = basename($_POST['nova_pasta']);
    mkdir("$currentDir/$novaPasta");
    header("Location: ?dir=" . urlencode(str_replace($baseDir, '', $currentDir)));
    exit;
}

// Excluir arquivo ou pasta
if (isset($_GET['excluir'])) {
    $target = realpath($currentDir . '/' . $_GET['excluir']);
    if ($target && strpos($target, $baseDir) === 0) {
        if (is_file($target)) unlink($target);
        if (is_dir($target)) rmdir($target); // Não remove se estiver cheia
    }
    header("Location: ?dir=" . urlencode(str_replace($baseDir, '', $currentDir)));
    exit;
}

// Listar conteúdo
$itens = scandir($currentDir);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Gerenciador de Arquivos</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <style>
        body {
            background-color: #121212;
            color: #f0f0f0;
        }
        .table th, .table td {
            color: #e0e0e0;
        }
        .colorful {
            color: #4dd0e1;
        }
        a {
            color: #81d4fa;
        }
        a:hover {
            color: #29b6f6;
        }
    </style>
</head>
<body class="p-4">
    <div class="container">
        <form class="mb-4 d-flex gap-2" method="post">
            <input class="form-control bg-dark text-light" type="text" name="nova_pasta" placeholder="Nome da nova pasta" required>
            <button class="btn btn-success">Criar Pasta</button>
        </form>

        <?php if ($currentDir != $baseDir): ?>
            <a class="btn btn-secondary mb-3" href="?dir=<?= urlencode(dirname(str_replace($baseDir, '', $currentDir))) ?>">⬅ Voltar</a>
        <?php endif; ?>

        <table id="fileTable" class="table table-dark table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Tipo</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($itens as $item):
                    if ($item === '.') continue;
                    if ($item === '..' && $currentDir === $baseDir) continue;

                    $path = "$currentDir/$item";
                    $relPath = str_replace($baseDir . '/', '', $path);
                ?>
                <tr>
                    <td>
                        <?php if (is_dir($path)): ?>
                            <a href="?dir=<?= urlencode($relPath) ?>"><?= htmlspecialchars($item) ?></a>
                        <?php else: ?>
                            <?= htmlspecialchars($item) ?>
                        <?php endif; ?>
                    </td>
                    <td><?= is_dir($path) ? 'Pasta' : 'Arquivo' ?></td>
                    <td>
                        <a class="btn btn-danger btn-sm" href="?dir=<?= urlencode(str_replace($baseDir, '', $currentDir)) ?>&excluir=<?= urlencode($item) ?>" onclick="return confirm('Tem certeza?')">Excluir</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function () {
            $('#fileTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/pt-BR.json"
                }
            });
        });
    </script>
</body>
</html>
