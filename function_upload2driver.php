<?php

include('../config/authenticate.php'); 

// Função para listar os arquivos e diretórios de forma recursiva
function listarArquivos($diretorio) {
    $arquivos = scandir($diretorio);
    echo '<ul>';
    foreach ($arquivos as $arquivo) {
        if ($arquivo != '.' && $arquivo != '..') {
            echo '<li>' . $arquivo;
            if (is_dir($diretorio . '/' . $arquivo)) {
                listarArquivos($diretorio . '/' . $arquivo);
            }
            echo '</li>';
        }
    }
    echo '</ul>';
}

// Diretório onde os uploads serão salvos
$diretorioUpload = 'uploads';

// Se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $arquivoTemp = $_FILES['file']['tmp_name'];
    $arquivoNome = $_FILES['file']['name'];
    $caminhoDestino = $diretorioUpload . '/' . $arquivoNome;
    
    // Move o arquivo para o diretório de uploads
    if (move_uploaded_file($arquivoTemp, $caminhoDestino)) {
        echo '<div class="alert alert-success" role="alert">Arquivo enviado com sucesso!</div>';
    } else {
        echo '<div class="alert alert-danger" role="alert">Erro ao enviar o arquivo!</div>';
    }
}

// Lista a estrutura de diretórios e arquivos no servidor
listarArquivos($diretorioUpload);
