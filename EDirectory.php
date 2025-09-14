<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>

<!DOCTYPE html>
<!--
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">




    <link href="bootstrap-admin/css/bootstrap.css" rel="stylesheet">


    <link href="bootstrap-admin/css/sb-admin.css" rel="stylesheet">
    <link rel="stylesheet" href="bootstrap-admin/font-awesome/css/font-awesome.min.css">
  </head>

  <body>

    <div id="wrapper">

      <nav class="navbar navbar-inverse navbar-fixed-top" role="navigation">
        <div class="navbar-header">
          <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
          </button>
          <a class="navbar-brand" href="admin\Clone2Driver\clone2driver.php">Clone4Driver</a>
        </div>
        
        <ul class="nav navbar-nav navbar-right navbar-user">
        <li class="dropdown user-dropdown">
              <a href="#" class="dropdown-toggle" data-toggle="dropdown"><i class="fa fa-gear"></i> Portfolio Aplication  <class="caret"></b></a>
              <ul class="dropdown-menu">
                <li><a href="?pagina=1"><i class="fa fa-file"></i> 4Uploader</a></li>
                <li><a href="?pagina=2"><i class="fa fa-envelope"></i> Inbox <span class="badge">7</span></a></li>
                <li><a href="?pagina=3"><i class="fa fa-file"></i> PHP4Editor</a></li>
                <li><a href="?pagina=4"><i class="fa fa-money"></i> 2Calculator</a></li>
                <li><a href="?pagina=5"><i class="fa fa-calendar"></i> Calendario <span class="badge">7</span></a></li>
                <li><a href="?pagina=6"><i class="fa fa-file"></i> NoteWeb</a></li>
                <li><a href="?pagina=7"><i class="fa fa-truck"></i> Karros</a></li>
                <li class="divider"></li>
                <li><a href="?pagina=0"><i class="fa fa-power-off"></i> Log Out</a></li>
              </ul>
            </li>
        </ul></ul>
        
      </nav>-->
      
      


          
          
          
          
          

<?php

include('/backend/paginator.php');

// Função para obter um caminho seguro baseado no diretório atual e no input do usuário
function getRealPath($currentDir, $userPath) {
    $realBase = realpath($currentDir);
    $userPath = $realBase . '/' . $userPath;
    $realUserPath = realpath($userPath);

    if ($realUserPath === false || strpos($realUserPath, $realBase) !== 0) {
        return null;
    }

    return $realUserPath;
}

$currentDir = __DIR__;
if (isset($_GET['path']) && $_GET['path'] !== '') {
    $requestedPath = getRealPath($currentDir, $_GET['path']);

    // Verifica se o caminho é válido e está dentro do diretório base
    if ($requestedPath) {
        $currentDir = $requestedPath;
    } else {
        echo "<p>Operação não permitida.</p>";
        exit;
    }
}

$files = scandir($currentDir);

echo "<div class='row'>
          <div class='col-lg-8'>
            <ol class='breadcrumb'>
              <li><a href='index.php'><i class='icon-dashboard'></i> ElasticDirectory</a></li>
              <li class='active'>" . htmlspecialchars($currentDir) . "</li>
            </ol>
          </div>
        </div>";
        
        
        ?>
        



<!--  _________________________________________________________________________________________________________________________________________________________- -->
        <div class="col-lg-4">
            <div class="panel panel-primary">
              <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-clock-o"></i> Recent Activity</h3>
              </div>
              <div class="panel-body">
                <div class="list-group">
                    <?php
                
foreach ($files as $file) {
    // Ignora os diretórios . e ..
    if ($file === '.' || $file === '..') {
        continue;
    }

    // Caminho completo do arquivo/diretório
    $fullPath = realpath($currentDir . DIRECTORY_SEPARATOR . $file);

    // Verifica se é um diretório
    if (is_dir($fullPath)) {
        $urlPath = substr($fullPath, strlen(__DIR__));
        //echo "<li><a href='?path=" . urlencode($urlPath) . "'>" . htmlspecialchars($file) . "/</a></li>";
        //echo "<li><i class='fa fa-folder'></i><a href='?path="  . urlencode($urlPath) . "'>"  . htmlspecialchars($file) . "/</a>";
        echo "<a href='?path=". urlencode($urlPath) ."' class='list-group-item'> 
                <i class='fa fa-folder'> </i>  $file
              </a>";
    } else {
        //echo "<li>" . htmlspecialchars($file) . "</li>";
        echo "<li> <i class='fa fa-envelope'></i>" . htmlspecialchars($file) . "</li>"; 


    }
}

    ?>
    
                 </div>

              </div>
            </div>
          </div>
    <?php
    


// Link para voltar ao diretório pai
if ($currentDir !== __DIR__) {
    $parentDir = dirname($currentDir);
    $urlPath = substr($parentDir, strlen(__DIR__));
    //echo "<a href='?path=" . urlencode($urlPath) . "'>Voltar</a>";
    echo "<div class='col-lg-4'>
            <h2 id='pager'></h2>
            <div class='bs-example'>
              <ul class='pager'>
                <li><a href='?path=" . urlencode($urlPath) . "'>Voltar</a></li>
              </ul>
            </div>
          </div>
        </div>
    ";
}

?>
                </div>
              </div>
            </div>
          </div>
        </div><!-- /.row -->
        
        <!--  _________________________________________________________________________________________________________________________________________________________- -->
        

            
            
        
        
        
        



</div><!-- /#page-wrapper -->

    </div><!-- /#wrapper -->

    <!-- JavaScript -->
    <script src="backend/website/bootstrap-admin/js/jquery-1.10.2.js"></script>
    <script src="backend/website/bootstrap-admin/js/bootstrap.js"></script>
    <script>
        // Função para carregar a lista de arquivos e pastas
        function loadFileList() {
            fetch('backend/E2Directory4viewer.php')
                .then(response => response.json())
                .then(data => {
                    const fileList = document.getElementById('fileList');
                    fileList.innerHTML = ''; // Limpa a lista atual

                    data.forEach(item => {
                        const li = document.createElement('li');
                        li.textContent = item;
                        li.addEventListener('click', () => {
                            showFileContent(item);
                        });
                        fileList.appendChild(li);
                    });
                })
                .catch(error => console.error('Erro ao carregar a lista de arquivos:', error));
        }

        // Função para exibir o conteúdo do arquivo
        function showFileContent(filename) {
            fetch('backend/read.php?file=' + encodeURIComponent(filename))
                .then(response => response.text())
                .then(data => {
                    alert('Conteúdo do arquivo ' + filename + ':\n\n' + data);
                })
                .catch(error => console.error('Erro ao ler o arquivo:', error));
        }

        // Carrega a lista de arquivos quando a página é carregada
        document.addEventListener('DOMContentLoaded', loadFileList);
    </script>

  </body>
</html>










