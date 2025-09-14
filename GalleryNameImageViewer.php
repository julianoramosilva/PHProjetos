
<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>

<?php

include('header.php');

// Função para verificar se o diretório contém pelo menos uma imagem
function containsImage($dir) {
    $images = preg_grep('/\.(jpg|jpeg|png|gif)$/i', scandir($dir));
    return count($images) > 0;
}

// Define o diretório raiz das imagens
$rootDir = __DIR__ ; // Ajuste este caminho conforme necessário
//$rootDir = __DIR__ . '/images'; // Ajuste este caminho conforme necessário

// Obter o caminho atual dentro do diretório de imagens
$currentPath = $_GET['path'] ?? '';
$fullPath = realpath($rootDir . '/' . $currentPath);

// Verifica se o caminho atual ainda está dentro do diretório de imagens
if (strpos($fullPath, realpath($rootDir)) !== 0 || !is_dir($fullPath)) {
    $currentPath = '';
    $fullPath = $rootDir;
}

// Lista os diretórios e arquivos
$dirs = [];
$files = [];
if ($handle = opendir($fullPath)) {
    while (false !== ($entry = readdir($handle))) {
        if ($entry == '.' || $entry == '..') {
            continue;
        }

        $filePath = $fullPath . '/' . $entry;
        if (is_dir($filePath) && containsImage($filePath)) {
            $dirs[] = $entry;
        } elseif (preg_match('/\.(jpg|jpeg|png|gif)$/i', $entry)) {
            $files[] = $entry;
        }
    }
    closedir($handle);
}

// Função para criar links seguros
function safeLink($path) {
    return '?path=' . urlencode($path);
}

?>

<!DOCTYPE html>
<html lang="pt">
<head>
    <meta charset="UTF-8">
    <title>Galeria de Imagens</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    

    <!--
    
    



    <style>
        #imageViewer {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            z-index: 1000;
            padding: 20px;
            background-color: white;
            border: 2px solid black;
            display: none; /* Inicialmente escondido */
        }
    </style>

    
    <script>
    $(document).ready(function() {
        // Supondo que suas imagens tenham a classe 'imageItem'
        $('.imageItem').click(function() {
            var imageSrc = $(this).attr('src'); // Obtém o SRC da imagem clicada
            $('#imageToShow').attr('src', imageSrc); // Define o SRC no visualizador
            $('#imageViewer').show(); // Mostra o visualizador de imagens
        });

        // Opção para fechar o visualizador ao clicar nele
        $('#imageViewer').click(function() {
            $(this).hide(); // Esconde o visualizador
        });
    });

</script>
-->
</head>



<body>

<h1>Galeria de Imagens</h1>

<?php if ($currentPath): ?>
    <a href="<?= safeLink(dirname($currentPath)) ?>">Voltar</a>
<?php endif; ?>

<h2>Diretórios</h2>
<ul>
    <?php foreach ($dirs as $dir): ?>
        <li><a href="<?= safeLink($currentPath . '/' . $dir) ?>"><?= htmlspecialchars($dir) ?></a></li>
    <?php endforeach; ?>
</ul>


<!-- Componente do visualizador de imagens -->
<div id="imageViewer" style="display:none;">
  <img id="imageToShow" src="" alt="Visualizador de imagem" style="max-width: 100%; height: auto;"/>
</div>



<h2>Imagens</h2>
<div >
    <?php foreach ($files as $file): ?>
        <div>
            <img height="42" width="42" class="imageItem" src="<?= 'imagens/' . htmlspecialchars($currentPath) . '/' . htmlspecialchars($file) ?>" alt="<?= htmlspecialchars($file) ?>" style="width: 100px; height: 100px;">
        </div>
        <!--
        <div class="row">
            <div class="col-xs-6 col-md-3">
                <a href="#" class="thumbnail">
               <img height="400" width="400" src="<?= 'imagens/' . htmlspecialchars($currentPath) . '/' . htmlspecialchars($file) ?>" />
              </a>
            </div>
        </div>-->
    <?php endforeach; ?>
</div>



</body>
</html>
