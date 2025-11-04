<?php
// portal_guia_comercial_portal.php com CRUD completo + alertas de plano vencido

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';

$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) { die('Erro conexão MySQL: ' . $mysqli->connect_error); }
$mysqli->query("CREATE DATABASE IF NOT EXISTS `" . $mysqli->real_escape_string($db_name) . "` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($db_name);
$mysqli->set_charset('utf8mb4');

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

$res = $mysqli->query("SELECT id FROM users WHERE username='admin'");
if ($res->num_rows == 0) {
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username, password, role) VALUES (?, ?, 'admin')");
    $userx = 'admin';
    $stmt->bind_param('ss', $userx, $pass_hash);
    $stmt->execute();
}

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
<head><meta charset='utf-8'><title>Admin - Guia Comercial</title>
<style>
table{border-collapse:collapse;width:100%;} th,td{border:1px solid #ccc;padding:6px;} th{background:#eee;} .red{color:red;font-weight:bold;} .green{color:green;font-weight:bold;}
</style></head>
<body>
<?php if(empty($_SESSION['user_id'])): ?>
<h2>Login Admin</h2>
<?php if(!empty($login_error)) echo "<div style='color:red'>$login_error</div>";?>
<form method='post' action='?action=login'>Usuário:<input name='username'><br>Senha:<input type='password' name='password'><br><button>Entrar</button></form>
<?php else: ?>
<h2>Painel Admin</h2>
<a href='?action=logout'>Sair</a>
<h3><a href='?action=new_business'>Adicionar Novo Negócio</a></h3>
<table>
<tr><th>Nome</th><th>Plano</th><th>Vencimento</th><th>Ações</th></tr>
<?php
$res=$mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC");
$today=date('Y-m-d');
while($b=$res->fetch_assoc()): 
    $class='';
    if($b['plan']!=='free' && (!isset($b['paid_until']) || $b['paid_until']<$today)) $class='red';
    elseif($b['plan']!=='free') $class='green';
?>
<tr class='<?php echo $class;?>'>
<td><?php echo htmlspecialchars($b['name']);?></td>
<td><?php echo $planos[$b['plan']]['nome']??$b['plan'];?></td>
<td><?php echo $b['paid_until']?:'-';?></td>
<td>
<a href='?action=edit_business&id=<?php echo $b['id'];?>'>Editar</a> |
<a href='?action=delete_business&id=<?php echo $b['id'];?>' onclick="return confirm('Excluir?')">Excluir</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<?php if($action==='edit_business' || $action==='new_business'):
    if($action==='edit_business'){$id=(int)$_GET['id']; $b=$mysqli->query("SELECT * FROM businesses WHERE id=$id")->fetch_assoc();}
?>
<h3><?php echo $action==='new_business'?'Adicionar Novo':'Editar';?> Negócio</h3>
<form method='post' action='?action=save_business'>
<input type='hidden' name='id' value='<?php echo $b['id']??0;?>'>
Nome:<input name='name' value='<?php echo $b['name']??'';?>'><br>
Categoria:<select name='category_id'><?php $cats=$mysqli->query("SELECT id,name FROM categories"); while($c=$cats->fetch_assoc()): ?><option value='<?php echo $c['id'];?>' <?php if(($b['category_id']??0)==$c['id']) echo 'selected';?>><?php echo htmlspecialchars($c['name']);?></option><?php endwhile;?></select><br>
Descrição:<textarea name='description'><?php echo $b['description']??'';?></textarea><br>
Endereço:<input name='address' value='<?php echo $b['address']??'';?>'><br>
Telefone:<input name='phone' value='<?php echo $b['phone']??'';?>'><br>
WhatsApp:<input name='whatsapp' value='<?php echo $b['whatsapp']??'';?>'><br>
Site:<input name='website' value='<?php echo $b['website']??'';?>'><br>
Facebook:<input name='facebook' value='<?php echo $b['facebook']??'';?>'><br>
Instagram:<input name='instagram' value='<?php echo $b['instagram']??'';?>'><br>
Plano:<select name='plan'><?php foreach($planos as $k=>$pl): ?><option value='<?php echo $k;?>' <?php if(($b['plan']??'free')==$k) echo 'selected';?>><?php echo $pl['nome'];?></option><?php endforeach;?></select><br>
Vencimento:<input type='date' name='paid_until' value='<?php echo $b['paid_until']??'';?>'><br>
<button>Salvar</button>
</form>
<?php endif; ?>
<?php endif; ?>
</body>
</html>
