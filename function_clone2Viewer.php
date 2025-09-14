<?php

include('../config/authenticate.php'); 

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Galeria de Imagens</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .gallery {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-between;
        }
        .gallery-item {
            margin-bottom: 15px;
            position: relative;
        }
        .gallery-item img {
            width: 100%;
            height: auto;
            display: block;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="my-4">Galeria de Imagens</h1>
        <div class="row gallery">
            <?php
            $directory = 'uploads/';
            $images = glob($directory . "/*.{jpg,jpeg,png,gif,jfif,webp}", GLOB_BRACE);

            foreach($images as $image) {
                echo '<div class="col-lg-2 col-md-3 col-xs-5 thumb gallery-item">';
                echo '<a href="' . $image . '" class="fancybox" rel="ligthbox">';
                echo '<img src="' . $image . '" class="img-fluid" alt="Image">';
                echo '</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
</body>
</html>
