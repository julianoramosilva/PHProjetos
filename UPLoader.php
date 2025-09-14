<?php
/**
 * File Manager SPA (Single file)
 * Requirements: PHP 7.4+
 * Features:
 *  - List folders/files within a safe BASE_DIR
 *  - Navigate folders (AJAX)
 *  - Upload images & audio (multi-file, drag & drop)
 *  - Create folders
 *  - Thumbnails for images; audio playback; icons for others
 *  - Bootstrap UI (single page style)
 *
 * Security notes:
 *  - Constrains all operations to BASE_DIR; prevents traversal
 *  - Validates upload by extension and MIME and size
 */
 /*
 $BASE_DIR = __DIR__ . '/uploads';            // Files live here (must be web-accessible)
$BASE_URL = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/uploads'; // Public URL for BASE_DIR
 */

// ---------- CONFIG ---------- //
$BASE_DIR = __DIR__ . '/images';            // Files live here (must be web-accessible)
$BASE_URL = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/') . '/images'; // Public URL for BASE_DIR
$MAX_UPLOAD_BYTES = 50 * 1024 * 1024;        // 50 MB per file limit
$ALLOWED_EXT = [
  'image' => ['jpg','jpeg','png','gif','webp','bmp','svg'],
  'audio' => ['mp3','wav','ogg','oga','m4a','aac','flac']
];
$ALLOWED_MIME_PREFIX = ['image/','audio/'];



if (!is_dir($BASE_DIR)) { @mkdir($BASE_DIR, 0775, true); }

// ---------- HELPERS ---------- //
function json_response($data, $code = 200) {
  http_response_code($code);
  header('Content-Type: application/json; charset=utf-8');
  echo json_encode($data, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
  exit;
}

function normalize_path($path) {
  $path = str_replace(['\\', '..'], ['/', ''], $path); // strip backslashes & parent refs
  $path = trim($path, "/ ");
  return $path === '' ? '' : $path;
}

function ensure_within_base($fullPath, $BASE_DIR) {
  $baseReal = realpath($BASE_DIR);
  $targetReal = realpath($fullPath);
  if ($targetReal === false) return false;
  return strpos($targetReal, $baseReal) === 0;
}

function is_allowed_upload($name, $tmp, $ALLOWED_EXT, $ALLOWED_MIME_PREFIX, $MAX_UPLOAD_BYTES) {
  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
  $allowedExts = array_merge($ALLOWED_EXT['image'], $ALLOWED_EXT['audio']);
  if (!in_array($ext, $allowedExts, true)) return [false, 'Extensão não permitida'];
  if (filesize($tmp) > $MAX_UPLOAD_BYTES) return [false, 'Arquivo excede o limite'];
  $finfo = finfo_open(FILEINFO_MIME_TYPE);
  $mime = finfo_file($finfo, $tmp);
  finfo_close($finfo);
  $okPrefix = false;
  foreach ($ALLOWED_MIME_PREFIX as $pref) { if (strpos($mime, $pref) === 0) { $okPrefix = true; break; } }
  if (!$okPrefix) return [false, 'MIME não permitido'];
  return [true, $mime];
}

function human_size($bytes) {
  $units = ['B','KB','MB','GB','TB'];
  $i = 0; $n = max(0, (int)$bytes);
  while ($n >= 1024 && $i < count($units)-1) { $n /= 1024; $i++; }
  return (round($n, 2) + 0) . ' ' . $units[$i];
}

function entry_meta($fullPath, $baseDir, $baseUrl) {
  $isDir = is_dir($fullPath);
  $rel = ltrim(str_replace($baseDir, '', $fullPath), '/');
  $url = rtrim($baseUrl, '/') . '/' . str_replace(' ', '%20', $rel);
  $ext = strtolower(pathinfo($fullPath, PATHINFO_EXTENSION));
  $size = $isDir ? null : filesize($fullPath);
  $mtime = filemtime($fullPath);
  return [
    'name' => basename($fullPath),
    'relPath' => $rel,
    'url' => $isDir ? null : $url,
    'type' => $isDir ? 'dir' : 'file',
    'ext' => $isDir ? null : $ext,
    'size' => $size,
    'size_h' => $isDir ? null : human_size($size),
    'mtime' => $mtime,
    'mtime_h' => date('Y-m-d H:i', $mtime)
  ];
}

// ---------- API ROUTES ---------- //
$action = $_GET['action'] ?? $_POST['action'] ?? null;
if ($action) {
  // CORS for fetch in same origin; not strictly required
  header('Cache-Control: no-store');
  switch ($action) {
    case 'list':
      $path = normalize_path($_GET['path'] ?? '');
      $target = rtrim($BASE_DIR . '/' . $path, '/');
      if ($path !== '' && !ensure_within_base($target, $BASE_DIR)) {
        json_response(['ok'=>false,'error'=>'Caminho inválido'], 400);
      }
      if (!is_dir($target)) { json_response(['ok'=>false,'error'=>'Diretório não encontrado'], 404); }
      $dirs = []; $files = [];
      $dh = opendir($target);
      while (($item = readdir($dh)) !== false) {
        if ($item === '.' || $item === '..') continue;
        $full = $target . '/' . $item;
        $meta = entry_meta($full, $BASE_DIR, $BASE_URL);
        if (is_dir($full)) $dirs[] = $meta; else $files[] = $meta;
      }
      closedir($dh);
      // Sort: dirs by name, files by mtime desc
      usort($dirs, fn($a,$b)=>strcasecmp($a['name'],$b['name']));
      usort($files, fn($a,$b)=>$b['mtime'] <=> $a['mtime']);
      json_response(['ok'=>true,'cwd'=>$path,'baseUrl'=>$BASE_URL,'dirs'=>$dirs,'files'=>$files]);
      break;

    case 'mkdir':
      $path = normalize_path($_POST['path'] ?? '');
      $name = trim($_POST['name'] ?? '');
      if ($name === '' || preg_match('~[\\/:*?"<>|]~', $name)) json_response(['ok'=>false,'error'=>'Nome inválido'], 400);
      $target = rtrim($BASE_DIR . '/' . $path, '/') . '/' . $name;
      if (!ensure_within_base(dirname($target), $BASE_DIR)) json_response(['ok'=>false,'error'=>'Caminho inválido'], 400);
      if (is_dir($target)) json_response(['ok'=>false,'error'=>'Pasta já existe'], 409);
      if (!@mkdir($target, 0775, true)) json_response(['ok'=>false,'error'=>'Falha ao criar pasta'], 500);
      json_response(['ok'=>true]);
      break;

    case 'upload':
      $path = normalize_path($_POST['path'] ?? '');
      $targetDir = rtrim($BASE_DIR . '/' . $path, '/');
      if (!ensure_within_base($targetDir, $BASE_DIR) || !is_dir($targetDir)) json_response(['ok'=>false,'error'=>'Destino inválido'], 400);
      if (!isset($_FILES['files'])) json_response(['ok'=>false,'error'=>'Nenhum arquivo enviado'], 400);
      $results = [];
      foreach ($_FILES['files']['name'] as $i => $name) {
        $tmp = $_FILES['files']['tmp_name'][$i];
        $error = $_FILES['files']['error'][$i];
        if ($error !== UPLOAD_ERR_OK) { $results[] = ['name'=>$name,'ok'=>false,'error'=>'Erro no upload']; continue; }
        [$ok, $why] = is_allowed_upload($name, $tmp, $ALLOWED_EXT, $ALLOWED_MIME_PREFIX, $MAX_UPLOAD_BYTES);
        if (!$ok) { $results[] = ['name'=>$name,'ok'=>false,'error'=>$why]; continue; }
        $safeName = preg_replace('~[^a-zA-Z0-9._-]+~', '_', $name);
        $dest = $targetDir . '/' . $safeName;
        // Prevent overwrite by appending (n)
        $n = 1;
        while (file_exists($dest)) {
          $pi = pathinfo($safeName);
          $alt = $pi['filename'] . "($n)" . (isset($pi['extension'])?'.'.$pi['extension']:'');
          $dest = $targetDir . '/' . $alt;
          $n++;
        }
        if (!@move_uploaded_file($tmp, $dest)) { $results[] = ['name'=>$name,'ok'=>false,'error'=>'Falha ao salvar']; continue; }
        @chmod($dest, 0664);
        $results[] = ['name'=>$name,'ok'=>true,'savedAs'=>basename($dest)];
      }
      json_response(['ok'=>true,'results'=>$results]);
      break;

    default:
      json_response(['ok'=>false,'error'=>'Ação inválida'], 400);
  }
}

// ---------- HTML (SPA) ---------- //
?><!doctype html>
<html lang="pt-br">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Gerenciador de Arquivos - SPA</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    :root{ --bg:#0b0f16; --panel:#121826; --muted:#a6b1c2; --accent:#3b82f6; }
    body{ background:linear-gradient(180deg,#0b0f16,#0f172a); color:#e5e7eb; }
    .navbar{ background:rgba(2,6,23,.7); backdrop-filter: blur(6px); }
    .card{ background:linear-gradient(180deg,#0f172a,#0b1220); border:1px solid #1f2937; box-shadow: 0 10px 30px rgba(0,0,0,.25); }
    .card:hover{ transform: translateY(-2px); transition: .2s; box-shadow: 0 16px 40px rgba(0,0,0,.35); }
    .file-grid{ display:grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap:1rem; }
    .thumb{ width:100%; height:120px; object-fit:cover; border-radius:.75rem; border:1px solid #223; background:#0b1220; }
    .icon-wrap{ font-size:48px; display:flex; align-items:center; justify-content:center; height:120px; color:#7dd3fc; }
    .breadcrumb-item + .breadcrumb-item::before { color:#64748b; }
    .dropzone{ border:2px dashed #334155; border-radius:1rem; padding:1.25rem; text-align:center; color:#94a3b8; }
    .dropzone.drag{ border-color:#3b82f6; background: rgba(59,130,246,.08); color:#c7d2fe; }
    .badge-ext{ background:#1f2937; border:1px solid #334155; }
    .muted{ color:#94a3b8; }
    .footer{ color:#64748b; }
    .btn-accent{ background:#3b82f6; border:0; }
    .btn-accent:hover{ background:#2563eb; }
  </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top border-bottom border-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="#"><i class="bi bi-hdd-stack"></i> File Manager</a>
    <a href="../admin/Editorador/Editorator.php" target="_blank" rel="noopener">PHPGallery</a>
    <div class="d-flex align-items-center gap-2">
      <button id="btnUp" class="btn btn-sm btn-outline-light" title="Pasta anterior"><i class="bi bi-arrow-up"></i></button>
      <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#modalMkdir"><i class="bi bi-folder-plus"></i> Nova pasta</button>
      <label class="btn btn-sm btn-accent mb-0" for="inpUpload"><i class="bi bi-cloud-upload"></i> Upload</label>
      <input id="inpUpload" type="file" multiple hidden accept="image/*,audio/*">
    </div>
  </div>
</nav>

<main class="container py-4">
  <div class="row g-3 align-items-center">
    <div class="col-md-8">
      <nav aria-label="breadcrumb"><ol id="crumbs" class="breadcrumb mb-0"></ol></nav>
      <div class="muted small">Base: <code><?= htmlspecialchars($BASE_URL) ?></code></div>
    </div>
    <div class="col-md-4">
      <div id="dropzone" class="dropzone">Arraste imagens/áudios aqui para enviar…</div>
    </div>
  </div>

  <hr class="border-secondary">
  <div id="grid" class="file-grid"></div>

  <p class="footer mt-4 small">Confinado em <code><?= htmlspecialchars($BASE_DIR) ?></code>. Personalize no topo do arquivo. ✨</p>
</main>

<!-- Modal: Nova pasta -->
<div class="modal fade" id="modalMkdir" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content bg-dark text-light">
      <div class="modal-header"><h5 class="modal-title"><i class="bi bi-folder-plus"></i> Criar nova pasta</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
      <div class="modal-body">
        <input id="mkdirName" type="text" class="form-control" placeholder="nome-da-pasta">
      </div>
      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button id="btnMkdir" class="btn btn-accent">Criar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
const state = { cwd: '' };
const grid = document.getElementById('grid');
const crumbs = document.getElementById('crumbs');
const btnUp = document.getElementById('btnUp');
const dropzone = document.getElementById('dropzone');
const inpUpload = document.getElementById('inpUpload');

async function api(action, data) {
  const opts = { method: 'POST' };
  let url = `?action=${encodeURIComponent(action)}`;
  if (data instanceof FormData) {
    opts.body = data;
  } else if (data) {
    opts.headers = {'Content-Type':'application/x-www-form-urlencoded'};
    opts.body = new URLSearchParams(data);
  }
  const res = await fetch(url, opts);
  return await res.json();
}

async function listDir(path='') {
  const res = await fetch(`?action=list&path=${encodeURIComponent(path)}`);
  const data = await res.json();
  if (!data.ok) { grid.innerHTML = `<div class='alert alert-danger'>${data.error||'Falha'}</div>`; return; }
  state.cwd = data.cwd || '';
  renderBreadcrumb();
  renderGrid(data);
}

function renderBreadcrumb() {
  const parts = state.cwd ? state.cwd.split('/') : [];
  crumbs.innerHTML = '';
  const root = document.createElement('li');
  root.className='breadcrumb-item';
  const a = document.createElement('a'); a.href='#'; a.textContent='uploads';
  a.onclick = (e)=>{ e.preventDefault(); listDir(''); };
  root.appendChild(a); crumbs.appendChild(root);
  let acc = '';
  parts.forEach((p,i)=>{
    acc += (acc?'/':'') + p;
    const li = document.createElement('li');
    li.className = 'breadcrumb-item' + (i===parts.length-1 ? ' active' : '');
    if (i===parts.length-1) { li.textContent = p; }
    else { const al = document.createElement('a'); al.href='#'; al.textContent=p; al.onclick=(e)=>{e.preventDefault(); listDir(acc);}; li.appendChild(al); }
    crumbs.appendChild(li);
  });
}

function iconFor(ext, type) {
  if (type==='dir') return 'bi-folder2-open';
  if (['jpg','jpeg','png','gif','webp','bmp','svg'].includes(ext)) return 'bi-image-fill';
  if (['mp3','wav','ogg','oga','m4a','aac','flac'].includes(ext)) return 'bi-music-note-beamed';
  return 'bi-file-earmark';
}

function renderGrid(data) {
  const items = [...data.dirs, ...data.files];
  if (items.length===0) { grid.innerHTML = `<div class='text-center text-secondary'>Pasta vazia</div>`; return; }
  grid.innerHTML = '';
  items.forEach(item => {
    const card = document.createElement('div');
    card.className='card p-2';
    const isDir = item.type==='dir';
    let inner = '';
    if (!isDir && ['jpg','jpeg','png','gif','webp','bmp'].includes(item.ext)) {
      inner = `<img class='thumb' src='${item.url}' alt='${item.name}'>`;
    } else if (!isDir && item.ext==='svg') {
      inner = `<object class='thumb' type='image/svg+xml' data='${item.url}'></object>`;
    } else if (!isDir && ['mp3','wav','ogg','oga','m4a','aac','flac'].includes(item.ext)) {
      inner = `<div class='icon-wrap'><i class='bi ${iconFor(item.ext)}'></i></div>` +
              `<audio controls preload='none' style='width:100%; margin-top:.5rem'><source src='${item.url}'></audio>`;
    } else if (isDir) {
      inner = `<div class='icon-wrap'><i class='bi ${iconFor(null,'dir')}'></i></div>`;
    } else {
      inner = `<div class='icon-wrap'><i class='bi ${iconFor(item.ext)}'></i></div>`;
    }

    const badge = isDir ? '' : `<span class='badge rounded-pill badge-ext'>.${item.ext||''}</span>`;
    const meta = isDir ? `<span class='muted small'>${item.mtime_h}</span>`
                       : `<span class='muted small'>${item.size_h} • ${item.mtime_h}</span>`;

    card.innerHTML = `
      ${inner}
      <div class='d-flex justify-content-between align-items-start mt-2'>
        <div class='text-truncate' title='${item.name}'><strong>${item.name}</strong></div>
        ${badge}
      </div>
      <div class='d-flex justify-content-between align-items-center mt-1'>
        ${meta}
        <div class='btn-group btn-group-sm'>
          ${isDir ? `<button class='btn btn-outline-light' title='Abrir'><i class='bi bi-arrow-right-circle'></i></button>`
                  : `<a class='btn btn-outline-light' href='${item.url}' target='_blank' title='Abrir'><i class='bi bi-box-arrow-up-right'></i></a>`}
          ${!isDir ? `<button class='btn btn-outline-light' title='Copiar link' data-url='${item.url}'><i class='bi bi-clipboard'></i></button>`: ''}
        </div>
      </div>
    `;

    // actions
    const btns = card.querySelectorAll('.btn');
    if (isDir) {
      btns[0].addEventListener('click', ()=> {
        const newPath = (state.cwd? state.cwd + '/' : '') + item.name;
        listDir(newPath);
      });
    } else if (btns.length>1) {
      btns[1-0]; // noop
      const copyBtn = btns[1];
      if (copyBtn) copyBtn.addEventListener('click', async ()=>{
        try { await navigator.clipboard.writeText(item.url); toast('Link copiado!'); } catch(e){ toast('Falha ao copiar'); }
      });
    }

    grid.appendChild(card);
  });
}

btnUp.addEventListener('click', ()=>{
  if (!state.cwd) return;
  const parts = state.cwd.split('/'); parts.pop();
  listDir(parts.join('/'));
});

// Upload via input
inpUpload.addEventListener('change', ()=> {
  if (!inpUpload.files?.length) return;
  uploadFiles(inpUpload.files);
  inpUpload.value = '';
});

// Drag & Drop
;['dragenter','dragover'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); dropzone.classList.add('drag'); }));
;['dragleave','drop'].forEach(ev=> dropzone.addEventListener(ev, e=>{ e.preventDefault(); dropzone.classList.remove('drag'); }));
dropzone.addEventListener('drop', e=>{
  const files = e.dataTransfer.files;
  if (files && files.length) uploadFiles(files);
});

async function uploadFiles(fileList) {
  const form = new FormData();
  for (const f of fileList) form.append('files[]', f);
  form.append('path', state.cwd || '');
  const res = await api('upload', form);
  if (!res.ok) { toast(res.error||'Falha no upload', true); return; }
  let okCount = 0, failCount = 0;
  res.results.forEach(r=> r.ok ? okCount++ : failCount++);
  if (okCount) toast(`Enviado(s): ${okCount}`);
  if (failCount) toast(`Falhas: ${failCount}`, true);
  listDir(state.cwd);
}

// Mkdir
const btnMkdir = document.getElementById('btnMkdir');
const mkdirName = document.getElementById('mkdirName');
btnMkdir.addEventListener('click', async ()=>{
  const name = mkdirName.value.trim();
  if (!name) return;
  const res = await api('mkdir', { path: state.cwd || '', name });
  if (!res.ok) toast(res.error||'Falha ao criar pasta', true); else { toast('Pasta criada!'); mkdirName.value=''; bootstrap.Modal.getInstance(document.getElementById('modalMkdir')).hide(); listDir(state.cwd); }
});

// Toast utility
function toast(msg, danger=false) {
  const el = document.createElement('div');
  el.className = `position-fixed top-0 end-0 p-3`;
  el.style.zIndex = 1080;
  el.innerHTML = `
  <div class="toast align-items-center text-bg-${danger?'danger':'primary'} border-0 show" role="alert">
    <div class="d-flex">
      <div class="toast-body">${msg}</div>
      <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
    </div>
  </div>`;
  document.body.appendChild(el);
  setTimeout(()=> el.remove(), 2500);
}

// boot
listDir('');
</script>
</body>
</html>
