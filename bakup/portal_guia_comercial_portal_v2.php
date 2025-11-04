<?php
// portal_guia_comercial_portal_v2.php
// Versão 2 (melhorias): CSRF, paginação, responsividade, submissão pública pendente, aprovação no admin.
// Instruções: salve em C:\wamp64\www\portal\portal_guia_comercial_portal_v2.php

// ---------- CONFIGURAÇÃO DO BANCO ----------
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';

// ---------- CONEXÃO (mysqli com charset) ----------
$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) {
    die('Erro conexão MySQL: ' . $mysqli->connect_error);
}
$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($db_name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($db_name);
$mysqli->set_charset('utf8mb4');

// ---------- CRIAÇÃO DE TABELAS (se não existirem) ----------
$mysqli->query("CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL UNIQUE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS businesses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL UNIQUE,
    category_id INT DEFAULT NULL,
    description TEXT,
    address VARCHAR(255),
    phone VARCHAR(100),
    whatsapp VARCHAR(100),
    website VARCHAR(255),
    facebook VARCHAR(255),
    instagram VARCHAR(255),
    status ENUM('pending','active') DEFAULT 'pending',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'editor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Insere usuário admin padrão se não existir (username: admin, senha: admin123) - troque depois
$res = $mysqli->query("SELECT id FROM users WHERE username='admin'");
if ($res->num_rows == 0) {
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $userx = 'admin';
    $stmt->bind_param('ss', $userx, $pass_hash);
    $stmt->execute();
}

// Insere categorias de exemplo
$default_cats = ['Restaurantes','Serviços','Saúde','Hospedagem','Comércio','Beleza','Oficinas'];
foreach ($default_cats as $c) {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    $stmt->bind_param('s', $c);
    $stmt->execute();
}

// ---------- FUNÇÕES ----------
function slugify($text){
    $text = preg_replace('~[^\pL0-9_]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9_]+~', '', $text);
    $text = trim($text, '-');
    $text = strtolower($text);
    if (empty($text)) return 'n-a';
    return $text;
}

session_start();
if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
function check_csrf($token){
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

$action = $_GET['action'] ?? '';

// ---------- AUTH ----------
$login_error = '';
if ($action === 'login') {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $login_error = 'Token inválido.'; }
    else {
        $u = $_POST['username'] ?? '';
        $p = $_POST['password'] ?? '';
        $stmt = $mysqli->prepare("SELECT id,password,role FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param('s', $u);
        $stmt->execute();
        $res = $stmt->get_result();
        if ($row = $res->fetch_assoc()) {
            if (password_verify($p, $row['password'])) {
                $_SESSION['user_id'] = $row['id'];
                $_SESSION['role'] = $row['role'];
                header('Location: ?admin=1'); exit;
            }
        }
        $login_error = 'Usuário ou senha inválidos.';
    }
}
if ($action === 'logout') { session_destroy(); header('Location: ?'); exit; }

// ---------- PUBLIC SUBMIT (pending) ----------
$submission_message = '';
if ($action === 'public_submit' && !empty($_POST['name'])) {
    if (!check_csrf($_POST['csrf_token'] ?? '')) { $submission_message = 'Token inválido.'; }
    else {
        $name = substr(trim($_POST['name']),0,255);
        $slug = slugify($name) . '-' . substr(bin2hex(random_bytes(3)),0,6);
        $category_id = (!empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
        $description = substr($_POST['description'] ?? '',0,2000);
        $address = substr($_POST['address'] ?? '',0,255);
        $phone = substr($_POST['phone'] ?? '',0,100);
        $whatsapp = substr($_POST['whatsapp'] ?? '',0,100);
        $website = substr($_POST['website'] ?? '',0,255);
        $facebook = substr($_POST['facebook'] ?? '',0,255);
        $instagram = substr($_POST['instagram'] ?? '',0,255);

        $stmt = $mysqli->prepare("INSERT INTO businesses (name,slug,category_id,description,address,phone,whatsapp,website,facebook,instagram,status) VALUES (?,?,?,?,?,?,?,?,?,?, 'pending')");
        $stmt->bind_param('ssisssssss', $name, $slug, $category_id, $description, $address, $phone, $whatsapp, $website, $facebook, $instagram);
        $stmt->execute();
        $submission_message = 'Obrigado! Seu cadastro foi recebido e ficará visível após aprovação.';
    }
}

// ---------- ADMIN ACTIONS ----------
if (($action === 'save_business') && isset($_POST['name'])) {
    if (empty($_SESSION['user_id']) || !check_csrf($_POST['csrf_token'] ?? '')) { header('Location: ?admin=1'); exit; }
    $name = trim($_POST['name']);
    $slug = slugify($name);
    $category_id = (!empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
    $description = $_POST['description'] ?? null;
    $address = $_POST['address'] ?? null;
    $phone = $_POST['phone'] ?? null;
    $whatsapp = $_POST['whatsapp'] ?? null;
    $website = $_POST['website'] ?? null;
    $facebook = $_POST['facebook'] ?? null;
    $instagram = $_POST['instagram'] ?? null;
    $status = (!empty($_POST['status']) && $_POST['status']==='active') ? 'active' : 'pending';

    if (!empty($_POST['id'])) {
        $id = (int)$_POST['id'];
        $stmt = $mysqli->prepare("UPDATE businesses SET name=?, slug=?, category_id=?, description=?, address=?, phone=?, whatsapp=?, website=?, facebook=?, instagram=?, status=? WHERE id=?");
        $stmt->bind_param('sisssssssssi', $name, $slug, $category_id, $description, $address, $phone, $whatsapp, $website, $facebook, $instagram, $status, $id);
        $stmt->execute();
    } else {
        $stmt = $mysqli->prepare("INSERT INTO businesses (name,slug,category_id,description,address,phone,whatsapp,website,facebook,instagram,status) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
        $stmt->bind_param('ssissssssss', $name, $slug, $category_id, $description, $address, $phone, $whatsapp, $website, $facebook, $instagram, $status);
        $stmt->execute();
    }
    header('Location: ?admin=1'); exit;
}
if ($action === 'delete_business' && !empty($_GET['id'])) { if (empty($_SESSION['user_id'])) { header('Location: ?'); exit; } $id = (int)$_GET['id']; $stmt = $mysqli->prepare("DELETE FROM businesses WHERE id=?"); $stmt->bind_param('i', $id); $stmt->execute(); header('Location: ?admin=1'); exit; }
if ($action === 'approve' && !empty($_GET['id'])) { if (empty($_SESSION['user_id'])) { header('Location: ?'); exit; } $id = (int)$_GET['id']; $stmt = $mysqli->prepare("UPDATE businesses SET status='active' WHERE id=?"); $stmt->bind_param('i', $id); $stmt->execute(); header('Location: ?admin=1'); exit; }

// ---------- SEARCH + PAGINATION ----------
$search = trim($_GET['q'] ?? '');
$cat_filter = !empty($_GET['cat']) ? (int)$_GET['cat'] : null;
$page = max(1, (int)($_GET['page'] ?? 1));
$per_page = 10;
$offset = ($page - 1) * $per_page;

$base_sql = "SELECT SQL_CALC_FOUND_ROWS b.*, c.name as category_name FROM businesses b LEFT JOIN categories c ON b.category_id = c.id";
$where = ["b.status='active'"];
$params = [];
$types = '';
if (!empty($search)) { $where[] = "(b.name LIKE ? OR b.description LIKE ? OR b.address LIKE ?)"); $like = "%{$search}%"; $params[] = &$like; $params[] = &$like; $params[] = &$like; $types .= 'sss'; }
if ($cat_filter) { $where[] = 'b.category_id = ?'; $params[] = &$cat_filter; $types .= 'i'; }
$sql = $base_sql . (count($where) ? ' WHERE '.implode(' AND ', $where) : '') . ' ORDER BY b.created_at DESC LIMIT ? OFFSET ?';

$stmt = $mysqli->prepare($sql);
if ($params) {
    $types_full = $types . 'ii';
    $params[] = &$per_page; $params[] = &$offset;
    array_unshift($params, $types_full);
    call_user_func_array(array($stmt,'bind_param'), refValues($params));
} else {
    $stmt->bind_param('ii', $per_page, $offset);
}
$stmt->execute();
$result = $stmt->get_result();
$total_res = $mysqli->query('SELECT FOUND_ROWS() as total')->fetch_assoc();
$total = (int)$total_res['total'];
$total_pages = max(1, ceil($total / $per_page));

function refValues($arr){ if (strnatcmp(phpversion(),'5.3')>=0) { $refs = array(); foreach($arr as $key => $value) $refs[$key] = &$arr[$key]; return $refs; } return $arr; }

// Pega categorias
$cats = []; $rc = $mysqli->query('SELECT id,name FROM categories ORDER BY name'); while ($r = $rc->fetch_assoc()) $cats[] = $r;

// ---------- RENDERIZAÇÃO ----------
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guia da Cidade - Portal</title>
<meta name="description" content="Vitrine digital local para comércios e serviços.">
<style>
:root{--max:1100px;--muted:#666}
body{font-family:Arial,Helvetica,sans-serif;max-width:var(--max);margin:18px auto;padding:12px;color:#222}
header{display:flex;align-items:center;justify-content:space-between}
.card{border:1px solid #eee;padding:12px;margin:8px 0;border-radius:8px}
.grid{display:grid;grid-template-columns:1fr 320px;gap:12px}
.business{border-bottom:1px dashed #f0f0f0;padding:10px 0}
.btn{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;border:1px solid #222;background:#fff}
.muted{color:var(--muted)}
@media (max-width:900px){ .grid{grid-template-columns:1fr} aside{order:2} }
</style>
</head>
<body>
<header>
<div>
<h1>Guia da Cidade</h1>
<div class="muted">Vitrine digital local — sua cidade em um só lugar</div>
</div>
<div>
<nav>
<a href="?">Início</a>
<a href="#register" onclick="document.getElementById('publicForm').style.display='block'">Cadastrar</a>
<a href="?admin=1">Admin</a>
<?php if (empty($_SESSION['user_id'])): ?>
<a href="#login" onclick="document.getElementById('loginForm').style.display='block'">Entrar</a>
<?php else: ?>
<a href="?action=logout">Sair</a>
<?php endif; ?>
</nav>
</div>
</header>
<hr>
<div class="grid">
<main>
<div class="card">
<form method="get" class="search">
<input type="search" name="q" value="<?php echo htmlspecialchars($search); ?>" placeholder="Buscar...">
<select name="cat"><option value="">Todas as categorias</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['name']).'</option>'; ?></select>
<input type="hidden" name="page" value="1"><button class="btn">Buscar</button>
</form>
</div>
<div class="card"><h3>Resultados (<?php echo $total?>)</h3>
<?php if ($result->num_rows==0): ?><div class="muted">Nenhum resultado. Cadastre seu negócio gratuitamente.</div>
<?php else: while($row=$result->fetch_assoc()): ?>
<div class="business"><h4><a href="?view=<?php echo $row['slug']; ?>"><?php echo htmlspecialchars($row['name']); ?></a></h4>
<div class="muted"><?php echo htmlspecialchars($row['category_name']); ?> — <?php echo htmlspecialchars($row['address']); ?></div>
<div><?php echo nl2br(htmlspecialchars(substr($row['description'],0,250))); ?></div></div>
<?php endwhile; ?>
<div style="margin-top:8px"><?php if($page>1) echo '<a class="btn" href="?'.http_build_query(array_merge($_GET,['page'=>$page-1])).'">&laquo; Anterior</a>';?>
<span class="muted"> Página <?php echo $page.' / '.$total_pages; ?></span>
<?php if($page<$total_pages) echo '<a class="btn" href="?'.http_build_query(array_merge($_GET,['page'=>$page+1])).'">Próxima &raquo;</a>'; ?></div>
<?php endif; ?></div>
</main>
<aside>
<div class="card"><h4>Categorias</h4><ul><?php foreach($cats as $c) echo '<li><a href="?cat='.$c['id'].'">'.htmlspecialchars($c['name']).'</a></li>'; ?></ul></div>
<div class="card"><h4>Cadastre seu negócio</h4><div class="muted">Seu cadastro será avaliado e publicado após aprovação.</div><div style="margin-top:8px"><a class="btn" href="#register" onclick="document.getElementById('publicForm').style.display='block'">Cadastrar</a></div></div>
<div class="card"><h4>Contato</h4><div class="muted">WhatsApp: (xx) xxxxx-xxxx<br>Email: contato@guidadacidade.local</div></div>
</aside>
</div>

<?php if(!empty($_GET['view'])){ $slug=$_GET['view']; $st=$mysqli->prepare("SELECT b.*, c.name as category_name FROM businesses b LEFT JOIN categories c ON b.category_id=c.id WHERE b.slug=? AND b.status='active' LIMIT 1"); $st->bind_param('s',$slug); $st->execute(); $rv=$st->get_result(); if($biz=$rv->fetch_assoc()){ echo '<div class="card"><h2>'.htmlspecialchars($biz['name']).'</h2>'; echo '<div class="muted">'.htmlspecialchars($biz['category_name']).' — '.htmlspecialchars($biz['address']).'</div>'; echo '<p>'.nl2br(htmlspecialchars($biz['description'])).'</p>'; if($biz['phone']) echo '<div>Telefone: '.htmlspecialchars($biz['phone']).'</div>'; if($biz['whatsapp']) echo '<div>WhatsApp: '.htmlspecialchars($biz['whatsapp']).'</div>'; if($biz['website']) echo '<div>Site: <a href="'.htmlspecialchars($biz['website']).'" target="_blank">'.htmlspecialchars($biz['website']).'</a></div>'; echo '</div>'; } else { echo '<div class="card"><div class="muted">Não encontrado ou não aprovado.</div></div>'; } }
?>

<!-- Public form -->
<div id="publicForm" style="display:none;position:fixed;left:0;right:0;top:0;bottom:0;background:rgba(0,0,0,0.5);padding:20px;overflow:auto">
<div style="max-width:680px;margin:30px auto;background:#fff;padding:16px;border-radius:8px">
<h3>Cadastrar meu negócio</h3>
<?php if($submission_message) echo '<div class="muted">'.htmlspecialchars($submission_message).'</div>'; ?>
<form method="post" action="?action=public_submit">
<input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">
<label>Nome*</label><input name="name" required>
<label>Categoria</label><select name="category_id"><option value="">-- selecione --</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['name']).'</option>'; ?></select>
<label>Endereço</label><input name="address"><label>Telefone</label><input name="phone"><label>WhatsApp</label><input name="whatsapp"><label>Descrição</label><textarea name="description"></textarea>
<div style="margin-top:8px"><button class="btn">Enviar</button> <a class="btn" href="#" onclick="document.getElementById('publicForm').style.display='none';return false">Fechar</a></div>
</form>
</div>
</div>

<!-- Login form -->
<div id="loginForm" style="display:none;position:fixed;left:0;right:0;top:0;bottom:0;background:rgba(0,0,0,0.4);padding:40px">
<div style="max-width:420px;margin:40px auto;background:#fff;padding:20px;border-radius:8px">
<h3>Entrar (admin)</h3>
<?php if(!empty($login_error)) echo '<div style="color:#a33">'.htmlspecialchars($login_error).'</div>'; ?>
<form method="post" action="?action=login"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>"><input name="username" placeholder="Usuário" required><input name="password" placeholder="Senha" type="password" required style="margin-top:8px"><div style="margin-top:8px"><button class="btn">Entrar</button> <a class="btn" href="?">Fechar</a></div></form>
</div>
</div>

<?php
// Admin panel
if(!empty($_GET['admin'])){
 if(empty($_SESSION['user_id'])){ echo '<script>document.getElementById("loginForm").style.display="block";</script>'; }
 else {
  echo '<div class="card"><h3>Painel Admin</h3>'; echo '<div class="muted">Usuário: admin</div>'; echo '<a class="btn" href="?admin=1&action=new">Novo negócio</a> <a class="btn" href="?">Voltar</a>';
  $r2=$mysqli->query('SELECT b.*, c.name as category_name FROM businesses b LEFT JOIN categories c ON b.category_id=c.id ORDER BY b.created_at DESC');
  echo '<h4 style="margin-top:12px">Negócios</h4>'; echo '<table style="width:100%;border-collapse:collapse"><tr><th>Nome</th><th>Categoria</th><th>Telefone</th><th>Status</th><th></th></tr>';
  while($b=$r2->fetch_assoc()){
    echo '<tr style="border-top:1px solid #eee"><td>'.htmlspecialchars($b['name']).'</td><td>'.htmlspecialchars($b['category_name']).'</td><td>'.htmlspecialchars($b['phone']).'</td><td>'.htmlspecialchars($b['status']).'</td>';
    $actions = '<a class="btn" href="?admin=1&action=edit&id='.$b['id'].'">Editar</a> '; $actions .= '<a class="btn" href="?action=delete_business&id='.$b['id'].'">Excluir</a> '; if($b['status']==='pending') $actions .= '<a class="btn" href="?action=approve&id='.$b['id'].'">Aprovar</a>';
    echo '<td>'.$actions.'</td></tr>';
  }
  echo '</table></div>';
  if(!empty($_GET['action']) && ($_GET['action']==='new' || $_GET['action']==='edit')){
    $editing=false;$biz=[]; if($_GET['action']==='edit' && !empty($_GET['id'])){ $id=(int)$_GET['id']; $st=$mysqli->prepare('SELECT * FROM businesses WHERE id=? LIMIT 1'); $st->bind_param('i',$id); $st->execute(); $rv=$st->get_result(); $biz=$rv->fetch_assoc(); $editing=true; }
    echo '<div class="card"><h4>'.($editing?'Editar negócio':'Novo negócio').'</h4>'; echo '<form method="post" action="?action=save_business">'; echo '<input type="hidden" name="csrf_token" value="'.htmlspecialchars($_SESSION['csrf_token']).'">'; if($editing) echo '<input type="hidden" name="id" value="'.htmlspecialchars($biz['id']).'">';
    echo '<label>Nome</label><input name="name" required value="'.htmlspecialchars($biz['name'] ?? '').'"><label>Categoria</label><select name="category_id"><option value="">-- selecione --</option>';
    foreach($cats as $c){ $sel = (!empty($biz['category_id']) && $biz['category_id']==$c['id'])? 'selected':''; echo '<option value="'.$c['id'].'" '.$sel.'>'.$c['name'].'</option>'; }
    echo '</select><label>Endereço</label><input name="address" value="'.htmlspecialchars($biz['address'] ?? '').'"><label>Telefone</label><input name="phone" value="'.htmlspecialchars($biz['phone'] ?? '').'"><label>Status</label><select name="status"><option value="pending" '.((!empty($biz['status']) && $biz['status']==='pending')?'selected':'').'>Pendente</option><option value="active" '.((!empty($biz['status']) && $biz['status']==='active')?'selected':'').'>Ativo</option></select><label>Descrição</label><textarea name="description">'.htmlspecialchars($biz['description'] ?? '').'</textarea><div style="margin-top:8px"><button class="btn">Salvar</button> <a class="btn" href="?admin=1">Cancelar</a></div></form></div>';
  }
 }
}
?>
