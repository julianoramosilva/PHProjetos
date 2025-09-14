<?php include('admin/config/authenticate.php'); ?>
<?php include('admin/config/conexao.php'); ?>
        <!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <title>Ubuntu Server Web Manager</title>
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <!-- Bootstrap & jQuery CDN (Darkly Bootswatch theme) -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootswatch@5.3.3/dist/darkly/bootstrap.min.css" />
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    body { background:#121212; color:#d1d1d1; }
    .navbar-brand{ color:#66d9ef !important; }
    .file-item:hover{ background:#1e1e1e; cursor:pointer; }
    .folder-icon{ color:#f1fa8c; }
    .file-icon{ color:#8be9fd; }
    .cmd-output{ height:200px; background:#1e1e1e; color:#50fa7b; overflow:auto; font-family:monospace; }
  </style>
</head>
<body>
  <!-- TOP NAV -->
  <nav class="navbar navbar-expand-lg navbar-dark bg-dark fixed-top">
    <div class="container-fluid">
      <span class="navbar-brand mb-0 h1">Ubuntu Server Web Manager</span>
      <div class="collapse navbar-collapse">
        <ul class="navbar-nav me-auto">
          <li class="nav-item"><a class="nav-link" href="#" id="logoutBtn">Logout</a></li>
        </ul>
        <form class="d-flex" id="cmdForm">
          <input class="form-control me-2" id="cmdInput" placeholder="Comando Shell" />
          <button class="btn btn-secondary" type="submit">Executar</button>
        </form>
      </div>
    </div>
  </nav>

  <!-- MAIN -->
  <div class="container-fluid mt-5 pt-3">
    <div class="row">
      <!-- SIDE BAR -->
      <div class="col-3">
          <br />
        <div class="d-flex mb-2">
          <button class="btn btn-success btn-sm me-1" id="newFolderBtn">+ Pasta</button>
          <button class="btn btn-info btn-sm me-1" id="uploadBtn">Upload</button>
          <input type="file" id="uploadInput" hidden multiple />
        </div>
        <ul class="list-group" id="dirTree"></ul>
      </div>
      <div class="col-9">
        <!-- CONTENT 
        <table class="table table-dark table-hover" id="fileTable">
          <thead><tr><th>Nome</th><th>Tamanho</th><th>Modificado</th><th>Ações</th></tr></thead>
          <tbody></tbody>
        </table> --> 
        <br />
        <?php include('EDirectory.php'); ?>
        <pre class="cmd-output p-2" id="cmdOut"></pre>
      </div>
    </div>
  </div>

  <!-- MODALS -->
  <div class="modal fade" id="editModal" tabindex="-1"><div class="modal-dialog modal-lg modal-dialog-scrollable"><div class="modal-content bg-dark"><div class="modal-header"><h5 class="modal-title">Editar Arquivo</h5><button class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><textarea id="fileContent" class="form-control" style="height:60vh"></textarea></div><div class="modal-footer"><button class="btn btn-primary" id="saveFileBtn">Salvar</button></div></div></div></div>
  <div class="modal fade" id="loginModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog"><div class="modal-content bg-dark"><div class="modal-header"><h5 class="modal-title">Login</h5></div><div class="modal-body"><input class="form-control mb-2" id="user" placeholder="Usuário"><input type="password" class="form-control" id="pass" placeholder="Senha"></div><div class="modal-footer"><button class="btn btn-primary" id="loginBtn">Entrar</button></div></div></div></div>

<script>
let currentPath = '/home';
$(document).ready(function(){
  //$('#loginModal').modal('show');
  loadDirectory(currentPath);

  /*$('#loginBtn').click(()=>{
    $.post('login.php', {user:$('#user').val(), pass:$('#pass').val()}, r=>{
      if(r==='OK'){ $('#loginModal').modal('hide'); loadDirectory(currentPath); }
      else alert('Falha no login');
    });
  });*/

  $('#newFolderBtn').click(()=>{
    const name = prompt('Nome da pasta:');
    if(name) $.post('create_folder.php',{path:currentPath,name},()=>loadDirectory(currentPath));
  });

  $('#uploadBtn').click(()=> $('#uploadInput').trigger('click'));
  $('#uploadInput').change(function(){
    const files = this.files;
    const fd = new FormData();
    fd.append('path', currentPath);
    for(let f of files){ fd.append('files[]', f); }
    $.ajax({url:'upload.php',method:'POST',processData:false,contentType:false,data:fd,success:()=>loadDirectory(currentPath)});
  });

  $('#cmdForm').submit(e=>{
    e.preventDefault();
    const cmd = $('#cmdInput').val();
    $.post('execute_command.php',{cmd,path:currentPath},o=>$('#cmdOut').text(o));
  });
});

function loadDirectory(path){
  $.getJSON('admin/Backend/list_directory.php',{path},data=>{
    currentPath = path;
    $('#pathTitle').text(path);
    // tree
    $('#dirTree').empty();
    data.dirs.forEach(d=> $('#dirTree').append(`<li class='list-group-item file-item'><span class='folder-icon'>📁</span> ${d}</li>`));
    // table
    const tbody = $('#fileTable tbody').empty();
    data.dirs.forEach(d=> tbody.append(rowHtml(d,'--','--','dir')));
    data.files.forEach(f=> tbody.append(rowHtml(f.name,f.size,f.mtime,'file')));
    // click events
    $('.file-item').click(function(){ loadDirectory(path+'/'+$(this).text().trim()); });
    $('.del-btn').click(function(){ del($(this).data('type'), $(this).data('name')); });
    $('.dl-btn').click(function(){ window.location='download.php?path='+encodeURIComponent(path+'/'+$(this).data('name')); });
    $('.edit-btn').click(function(){ editFile($(this).data('name')); });
  });
}
function rowHtml(name,size,mtime,type){
  const icon = type==='dir'? '📁':'📄';
  let act = `<button class='btn btn-sm btn-danger del-btn' data-type='${type}' data-name='${name}'>🗑️</button>`;
  if(type==='file') act += ` <button class='btn btn-sm btn-warning dl-btn' data-name='${name}'>⬇️</button> <button class='btn btn-sm btn-info edit-btn' data-name='${name}'>✏️</button>`;
  return `<tr><td><span>${icon}</span> ${name}</td><td>${size}</td><td>${mtime}</td><td>${act}</td></tr>`;
}
function del(type,name){
  if(confirm('Excluir '+name+'?'))
    $.post('delete.php',{path:currentPath,name,type},()=>loadDirectory(currentPath));
}
function editFile(name){
  $.get('get_file.php',{path:currentPath,name},c=>{ $('#fileContent').val(c); $('#editModal').modal('show'); $('#saveFileBtn').off('click').click(()=> saveFile(name)); });
}
function saveFile(name){
  $.post('edit_file.php',{path:currentPath,name,content:$('#fileContent').val()},()=>{ $('#editModal').modal('hide'); });
}
</script>
</body>
</html>