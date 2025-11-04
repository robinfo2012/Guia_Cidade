<?php
// portal_guia_comercial_portal.php com CRUD completo + Tailwind moderno

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';

$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) { die('Erro conexão MySQL: ' . $mysqli->connect_error); }
$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($db_name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($db_name);
$mysqli->set_charset('utf8mb4');

// Criação de tabelas (categories, businesses, users) continua igual
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
    plan ENUM('free','basic','pro','premium') DEFAULT 'free',
    paid_until DATE DEFAULT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

$mysqli->query("CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin','editor') DEFAULT 'editor'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

// Criação admin padrão
$res = $mysqli->query("SELECT id FROM users WHERE username='admin'");
if ($res->num_rows == 0) {
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $userx = 'admin';
    $stmt->bind_param('ss', $userx, $pass_hash);
    $stmt->execute();
}

// Categorias default
$default_cats = ['Restaurantes','Serviços','Saúde','Hospedagem','Comércio','Beleza','Oficinas'];
foreach ($default_cats as $c) {
    $stmt = $mysqli->prepare("INSERT IGNORE INTO categories (name) VALUES (?)");
    $stmt->bind_param('s', $c);
    $stmt->execute();
}

function slugify($text){
    $text = preg_replace('~[^\pL0-9_]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9_]+~', '', $text);
    $text = trim($text, '-');
    return strtolower($text) ?: 'n-a';
}

session_start();
$action = $_GET['action'] ?? '';

// Login/logout permanece igual
if ($action === 'login') {
    $u = trim($_POST['username'] ?? '');
    $p = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT id,password,role FROM users WHERE username=? LIMIT 1");
    $stmt->bind_param('s',$u);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row=$res->fetch_assoc()){ if(password_verify($p,$row['password'])){ $_SESSION['user_id']=$row['id']; $_SESSION['role']=$row['role']; header('Location:?admin=1'); exit; } }
    $login_error='Usuário ou senha inválidos.';
}
if($action==='logout'){ session_destroy(); header('Location:?'); exit; }

$planos=['free'=>['nome'=>'Grátis','preco'=>0], 'basic'=>['nome'=>'Básico','preco'=>49], 'pro'=>['nome'=>'Pro','preco'=>99], 'premium'=>['nome'=>'Premium','preco'=>199]];

// CRUD save/delete permanece igual
if(!empty($_SESSION['user_id'])){
    if($action==='save_business' && !empty($_POST['name'])){
        $id=(int)($_POST['id']??0);
        $name=trim($_POST['name']);
        $slug=slugify($name);
        $category_id=(int)($_POST['category_id']??0);
        $description=$_POST['description']??'';
        $address=$_POST['address']??'';
        $phone=$_POST['phone']??'';
        $whatsapp=$_POST['whatsapp']??'';
        $website=$_POST['website']??'';
        $facebook=$_POST['facebook']??'';
        $instagram=$_POST['instagram']??'';
        $plan=$_POST['plan']??'free';
        $paid_until=$_POST['paid_until']?:NULL;
        if($id>0){
            $stmt=$mysqli->prepare("UPDATE businesses SET name=?, slug=?, category_id=?, description=?, address=?, phone=?, whatsapp=?, website=?, facebook=?, instagram=?, plan=?, paid_until=? WHERE id=?");
            $stmt->bind_param('sisssssssssii',$name,$slug,$category_id,$description,$address,$phone,$whatsapp,$website,$facebook,$instagram,$plan,$paid_until,$id);
            $stmt->execute();
        }else{
            $stmt=$mysqli->prepare("INSERT INTO businesses (name,slug,category_id,description,address,phone,whatsapp,website,facebook,instagram,plan,paid_until) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssisssssssss',$name,$slug,$category_id,$description,$address,$phone,$whatsapp,$website,$facebook,$instagram,$plan,$paid_until);
            $stmt->execute();
        }
        header('Location:?admin=1'); exit;
    }
    if($action==='delete_business' && !empty($_GET['id'])){
        $id=(int)$_GET['id'];
        $stmt=$mysqli->prepare("DELETE FROM businesses WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        header('Location:?admin=1'); exit;
    }
}
?>
<!doctype html>
<html lang='pt-BR'>
<head>
<meta charset='utf-8'>
<title>Admin - Guia Comercial</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 p-6">
<?php if(empty($_SESSION['user_id'])): ?>
<div class="max-w-md mx-auto bg-white p-6 rounded shadow">
<h2 class="text-xl font-semibold mb-4">Login Admin</h2>
<?php if(!empty($login_error)) echo "<div class='text-red-600 mb-2'>$login_error</div>";?>
<form method='post' action='?action=login' class="space-y-3">
  <div>Usuário:<input class="border p-2 w-full rounded" name='username'></div>
  <div>Senha:<input class="border p-2 w-full rounded" type='password' name='password'></div>
  <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Entrar</button>
</form>
</div>
<?php else: ?>
<div class="max-w-6xl mx-auto">
<h2 class="text-2xl font-semibold mb-4">Painel Admin</h2>
<a class="text-blue-600 hover:underline" href='?action=logout'>Sair</a>
<h3 class="mt-4 mb-2"><a class="bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700" href='?action=new_business'>Adicionar Novo Negócio</a></h3>
<div class="overflow-x-auto">
<table class="min-w-full bg-white border border-gray-200 rounded">
<tr class="bg-gray-100"><th class="p-2 border">Nome</th><th class="p-2 border">Plano</th><th class="p-2 border">Vencimento</th><th class="p-2 border">Ações</th></tr>
<?php
$res=$mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC");
$today=date('Y-m-d');
while($b=$res->fetch_assoc()): 
    $class='';
    if($b['plan']!=='free' && (!isset($b['paid_until']) || $b['paid_until']<$today)) $class='bg-red-100';
    elseif($b['plan']!=='free') $class='bg-green-100';
?>
<tr class='<?php echo $class;?>'><td class="p-2 border"><?php echo htmlspecialchars($b['name']);?></td><td class="p-2 border"><?php echo $planos[$b['plan']]['nome']??$b['plan'];?></td><td class="p-2 border"><?php echo $b['paid_until']?:'-';?></td><td class="p-2 border space-x-2">
<a class="text-blue-600 hover:underline" href='?action=edit_business&id=<?php echo $b['id'];?>'>Editar</a>
<a class="text-red-600 hover:underline" href='?action=delete_business&id=<?php echo $b['id'];?>' onclick="return confirm('Excluir?')">Excluir</a>
</td></tr>
<?php endwhile; ?>
</table>
</div>
<?php if($action==='edit_business' || $action==='new_business'):
    if($action==='edit_business'){$id=(int)$_GET['id']; $b=$mysqli->query("SELECT * FROM businesses WHERE id=$id")->fetch_assoc();}
?>
<h3 class="text-xl font-semibold mt-6 mb-2"><?php echo $action==='new_business'?'Adicionar Novo':'Editar';?> Negócio</h3>
<form method='post' action='?action=save_business' class="space-y-3 bg-white p-4 rounded shadow">
<input type='hidden' name='id' value='<?php echo $b['id']??0;?>'>
<div>Nome:<input class="border p-2 w-full rounded" name='name' value='<?php echo $b['name']??'';?>'></div>
<div>Categoria:<select class="border p-2 w-full rounded" name='category_id'><?php $cats=$mysqli->query("SELECT id,name FROM categories"); while($c=$cats->fetch_assoc()): ?><option value='<?php echo $c['id'];?>' <?php if(($b['category_id']??0)==$c['id']) echo 'selected';?>><?php echo htmlspecialchars($c['name']);?></option><?php endwhile;?></select></div>
<div>Descrição:<textarea class="border p-2 w-full rounded" name='description'><?php echo $b['description']??'';?></textarea></div>
<div>Endereço:<input class="border p-2 w-full rounded" name='address' value='<?php echo $b['address']??'';?>'></div>
<div>Telefone:<input class="border p-2 w-full rounded" name='phone' value='<?php echo $b['phone']??'';?>'></div>
<div>WhatsApp:<input class="border p-2 w-full rounded" name='whatsapp' value='<?php echo $b['whatsapp']??'';?>'></div>
<div>Site:<input class="border p-2 w-full rounded" name='website' value='<?php echo $b['website']??'';?>'></div>
<div>Facebook:<input class="border p-2 w-full rounded" name='facebook' value='<?php echo $b['facebook']??'';?>'></div>
<div>Instagram:<input class="border p-2 w-full rounded" name='instagram' value='<?php echo $b['instagram']??'';?>'></div>
<div>Plano:<select class="border p-2 w-full rounded" name='plan'><?php foreach($planos as $k=>$pl): ?><option value='<?php echo $k;?>' <?php if(($b['plan']??'free')==$k) echo 'selected';?>><?php echo $pl['nome'];?></option><?php endforeach;?></select></div>
<div>Vencimento:<input class="border p-2 w-full rounded" type='date' name='paid_until' value='<?php echo $b['paid_until']??'';?>'></div>
<button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
</form>
<?php endif; ?>
</div>
<?php endif; ?>
</body>
</html>
