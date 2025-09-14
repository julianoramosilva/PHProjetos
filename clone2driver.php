<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clone Driver2File</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Clone Driver2File</h1>

        <!-- 
        <div id="folders"></div>
        <div id="files"></div>
        -->
       
        <form id="uploadForm" enctype="multipart/form-data">
            <div class="form-group">
                <label for="file">Choose File</label>
                <input type="file" class="form-control-file" id="file" name="file">
            </div>
            <button type="submit" class="btn btn-primary">Upload</button>
        </form>
        <?php

        include('../config/authenticate.php'); 
        include('../config/conexao.php'); 
        include('function_clone2Viewer.php');
        ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script>
        $(document).ready(function () {
            refreshFolders();

            $('#uploadForm').submit(function (e) {
                e.preventDefault();
                var formData = new FormData(this);
                $.ajax({
                    url: 'function_upload2driver.php',
                    type: 'POST',
                    data: formData,
                    success: function (data) {
                        refreshFolders();
                        alert('File uploaded successfully');
                    },
                    error: function () {
                        alert('Error uploading file');
                    },
                    cache: false,
                    contentType: false,
                    processData: false
                });
            });

            $(document).on('click', '.folder', function () {
                var folderPath = $(this).data('path');
                $.ajax({
                    url: 'function_viewer2driver.php',
                    type: 'POST',
                    data: { folder: folderPath },
                    success: function (data) {
                        $('#folders').html('');
                        $('#files').html(data);
                    }
                });
            });

            function refreshFolders() {
                $.ajax({
                    url: 'function_viewer2driver.php',
                    type: 'POST',
                    data: { folder: '' },
                    success: function (data) {
                        $('#gallery').html(data);
                        
                        
                        //$('#files').html();
                    }
                });
            }
        });
    </script>
</body>
</html>
