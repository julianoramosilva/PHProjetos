

<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>


<?php
$directory = './'; // Diretório atual
$files = scandir($directory);

// Remove . e ..
$files = array_diff($files, array('..', '.'));

echo json_encode(array_values($files));
?>


