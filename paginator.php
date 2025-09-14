<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>

<?php


    $pagina = $_GET['pagina'];

    // Usa um comando switch para redirecionar com base no valor do parâmetro
    switch ($pagina) {
        case 'X1fe98fye87ryer876d7f8sdyhb7s97f89ds7f-d67ss7fysduifyusdiyfiu':
            // Redireciona para a página1.php
            header('Location: titansperdidos.com.br/titansperdidos.html');
            exit();
        case 'X1ferftge87ryer876d7f8sdf78ds97f89ds7f-d67ss7fysduifyusdiyfiu':
            // Redireciona para a página1.php
            header('Location: titansperdidos.com.br/titansperdidos2.html');
            exit();
        case 'X1fe98fye87ryer876d7f8sdf78ds97f89ds7f-d67ss7fysduifyusdiyfiu':
            // Redireciona para a página1.php
            header('Location: titansperdidos.com.br/publicacao.html');
            exit();
        case 'X1fe98fye87ryer876d7f8sdf78ds97f89ds65456465t4456y56y65y65t54':
            // Redireciona para a pagina2.php
            header('Location: titansperdidos.com.br/publisher.php');
            exit();
        case 'X1fe98fye87ryer876d7f8sdf78d=8u7y76td67ts67add787u778jujh777u':
            // Redireciona para a pagina3.php
            header('Location: titansperdidos.com.br/publisher2version.php');
            exit();
        case 'X1fe98fye87ryer876d7f999f78d=8u7y76td67ts67add787u778jujh333u':
            //Redireciona para uma página de erro ou página padrão
            header('Location: titansperdidos.com.br/titans-perdidos-era-de-caos.php');
            exit();
        default:
            //Redireciona para uma página de erro ou página padrão
            header('Location: titansperdidos.com.br/publisher4version.php');
            exit();
    }

?>
