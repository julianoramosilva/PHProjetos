<?php

include('../config/authenticate.php'); 

$folder = isset($_POST['folder']) ? $_POST['folder'] : '';
$folder = '../../uploads/' . $folder;
$files = scandir($folder);
foreach ($files as $file) {
    if ($file != '.' && $file != '..') {
        if (is_dir($folder . '/' . $file)) {
            echo "<div class='folder' data-path='$file'>$file</div>";
            //echo '<img height="100" width="100" src="'.$file.'"/>';
            echo '<div class="col-lg-3 col-md-4 col-xs-6 thumb gallery-item">';
            echo '<a href="' . $file . '" class="fancybox" rel="ligthbox">';
            echo '<img src="' . $file . '" class="img-fluid" alt="Image">';
            echo '</a>';
            echo '</div>';
        } else {
            echo "<div>$file</div>";
        }
    }
}
?>
