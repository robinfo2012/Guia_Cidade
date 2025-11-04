<?php
// portal_guia_comercial_portal.php
// Versão final: CRUD admin, planos configuráveis, pagamento Pix estático (copia & cola) com QR code gerado
// Chave Pix usada: robinfonet@gmail.com

// ---------- CONFIG ----------
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';
$pix_key = 'robinfonet@gmail.com'; // sua chave Pix
$merchant_name = 'ROBINFONET'; // exibido no QR
$merchant_city = 'SAO PAULO';

// ---------- CONEXÃO ----------
$mysqli = new mysqli($db_host, $db_user, $db_pass);
if ($mysqli->connect_errno) die('Erro conexão MySQL: '.$mysqli->connect_error);
$mysqli->query("CREATE DATABASE IF NOT EXISTS `".$mysqli->real_escape_string($db_name)."` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
$mysqli->select_db($db_name);
$mysqli->set_charset('utf8mb4');

// ---------- CRIAÇÃO DE TABELAS ----------
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
    plan_price DECIMAL(10,2) DEFAULT 0,
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

// default admin
$res = $mysqli->query("SELECT id FROM users WHERE username='admin'");
if ($res->num_rows==0){
    $pass_hash = password_hash('admin123', PASSWORD_DEFAULT);
    $stmt = $mysqli->prepare("INSERT INTO users (username,password,role) VALUES (?,?,'admin')");
    $userx='admin'; $stmt->bind_param('ss',$userx,$pass_hash); $stmt->execute();
}

// default categories
$default_cats = ['Restaurantes','Serviços','Saúde','Hospedagem','Comércio','Beleza','Oficinas'];
foreach($default_cats as $c){ $s=$mysqli->prepare("INSERT IGNORE INTO categories (name) VALUES (?)"); $s->bind_param('s',$c); $s->execute(); }

// helpers
function slugify($text){ $text = preg_replace('~[^\pL0-9_]+~u','-',$text); $text = iconv('UTF-8','ASCII//TRANSLIT',$text); $text = preg_replace('~[^-a-zA-Z0-9_]+~','',$text); $text = trim($text,'-'); $text = strtolower($text); return $text?:'n-a'; }
function refValues($arr){ if (strnatcmp(phpversion(),'5.3')>=0){ $refs=array(); foreach($arr as $key=>$value) $refs[$key]=&$arr[$key]; return $refs; } return $arr; }

session_start();
$action = $_GET['action'] ?? '';

// ---------- AUTH ----------
if ($action==='login'){
    $u = trim($_POST['username'] ?? ''); $p = $_POST['password'] ?? '';
    $stmt = $mysqli->prepare("SELECT id,password,role FROM users WHERE username=? LIMIT 1"); $stmt->bind_param('s',$u); $stmt->execute(); $res = $stmt->get_result();
    if ($row = $res->fetch_assoc() && password_verify($p,$row['password'])){ $_SESSION['user_id']=$row['id']; $_SESSION['role']=$row['role']; header('Location:?admin=1'); exit; }
    $login_error='Usuário ou senha inválidos.';
}
if ($action==='logout'){ session_destroy(); header('Location:?'); exit; }

// planos
$planos = ['free'=>['nome'=>'Grátis','preco'=>0], 'basic'=>['nome'=>'Básico','preco'=>49], 'pro'=>['nome'=>'Pro','preco'=>99], 'premium'=>['nome'=>'Premium','preco'=>199]];

// ---------- ADMIN CRUD ----------
if (!empty($_SESSION['user_id'])){
    // save business
    if ($action==='save_business' && !empty($_POST['name'])){
        $id = (int)($_POST['id']??0);
        $name = trim($_POST['name']); $slug = slugify($name);
        $category_id = (int)($_POST['category_id']??0);
        $description = $_POST['description']??''; $address = $_POST['address']??''; $phone = $_POST['phone']??''; $whatsapp = $_POST['whatsapp']??''; $website = $_POST['website']??''; $facebook = $_POST['facebook']??''; $instagram = $_POST['instagram']??'';
        $plan = $_POST['plan']??'free'; $plan_price = floatval(str_replace(',','.',($_POST['plan_price']??'0')));
        $paid_until = !empty($_POST['paid_until']) ? $_POST['paid_until'] : NULL;
        if ($id>0){
            $stmt = $mysqli->prepare("UPDATE businesses SET name=?,slug=?,category_id=?,description=?,address=?,phone=?,whatsapp=?,website=?,facebook=?,instagram=?,plan=?,plan_price=?,paid_until=? WHERE id=?");
            $stmt->bind_param('sisssssssssidi',$name,$slug,$category_id,$description,$address,$phone,$whatsapp,$website,$facebook,$instagram,$plan,$plan_price,$paid_until,$id);
            $stmt->execute();
        } else {
            $stmt = $mysqli->prepare("INSERT INTO businesses (name,slug,category_id,description,address,phone,whatsapp,website,facebook,instagram,plan,plan_price,paid_until) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
            $stmt->bind_param('ssisssssssssd',$name,$slug,$category_id,$description,$address,$phone,$whatsapp,$website,$facebook,$instagram,$plan,$plan_price,$paid_until);
            $stmt->execute();
        }
        header('Location:?admin=1'); exit;
    }
    // delete
    if ($action==='delete_business' && !empty($_GET['id'])){ $id=(int)$_GET['id']; $stmt=$mysqli->prepare("DELETE FROM businesses WHERE id=?"); $stmt->bind_param('i',$id); $stmt->execute(); header('Location:?admin=1'); exit; }
    // confirm payment
    if ($action==='confirm_payment' && !empty($_GET['id'])){
        $id=(int)$_GET['id'];
        $mysqli->query("UPDATE businesses SET paid_until = DATE_ADD(COALESCE(paid_until,CURDATE()), INTERVAL 30 DAY) WHERE id=$id");
        header('Location:?admin=1'); exit;
    }
}

// ---------- PAGAMENTO PIX (public) ----------
if (!empty($_GET['pagamento'])){
    $slug = $_GET['pagamento'];
    $st = $mysqli->prepare("SELECT * FROM businesses WHERE slug=? LIMIT 1"); $st->bind_param('s',$slug); $st->execute(); $biz = $st->get_result()->fetch_assoc();
    if (!$biz){ echo "Negócio não encontrado."; exit; }
    $amount = number_format($biz['plan_price']>0 ? $biz['plan_price'] : ($planos[$biz['plan']]['preco'] ?? 0), 2, '.', '');
    // build BR Code (EMV) for Pix
    function emv($id,$value){ $len = str_pad(strlen($value),2,'0',STR_PAD_LEFT); return $id.$len.$value; }
    function crc16($payload){
        $polynomial = 0x1021; $crc = 0xFFFF;
        $bytes = array_map('ord', str_split($payload));
        foreach($bytes as $b){ $crc ^= ($b << 8); for($i=0;$i<8;$i++){ if(($crc & 0x8000) != 0) $crc = (($crc << 1) ^ $polynomial) & 0xFFFF; else $crc = ($crc << 1) & 0xFFFF; } }
        return strtoupper(str_pad(dechex($crc),4,'0',STR_PAD_LEFT));
    }
    $merchantAccountInfo = emv('00','br.gov.bcb.pix').emv('01',$pix_key);
    $additional = emv('05', uniqid()); // txid random
    $payload  = emv('00','01') . emv('26',$merchantAccountInfo) . emv('52','0000') . emv('53','986') . ($amount>0?emv('54',$amount):'') . emv('58','BR') . emv('59', strtoupper(substr($merchant_name,0,25))) . emv('60', strtoupper(substr($merchant_city,0,15))) . emv('62',$additional);
    $payload_to_crc = $payload.'6304';
    $crc = crc16($payload_to_crc);
    $brcode = $payload.'63'.'04'.$crc;
    $qr_url = 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl='.urlencode($brcode);
    // render page
    echo "<!doctype html><html lang='pt-BR'><head><meta charset='utf-8'><title>Pagamento Pix - ".htmlspecialchars($biz['name'])."</title></head><body>";
    echo "<h2>Pagamento Pix - ".htmlspecialchars($biz['name'])."</h2>";
    echo "<p>Valor: R$ ".number_format($amount,2,',','.')."</p>";
    echo "<p>Chave Pix recebedora: <strong>".htmlspecialchars($pix_key)."</strong></p>";
    echo "<p>QR Code:</p><p><img src='".$qr_url."' alt='QR Code Pix'></p>";
    echo "<h4>Código copia e cola (BR Code)</h4><textarea style='width:100%;height:120px;'>".htmlspecialchars($brcode)."</textarea>";
    echo "<p>Ao realizar o pagamento, volte ao painel e peça para o administrador confirmar o pagamento (botão 'Confirmar pagamento').</p>";
    echo "</body></html>";
    exit;
}

// ---------- FRONTEND / LISTA ----------
$search = $_GET['q'] ?? ''; $cat_filter = !empty($_GET['cat']) ? (int)$_GET['cat'] : null; $page = max(1,(int)($_GET['page']??1)); $per_page=10; $offset=($page-1)*$per_page;
$where = []; $types=''; $params=[];
if ($search){ $where[] = "(b.name LIKE ? OR b.description LIKE ? OR b.address LIKE ?)"; $like="%$search%"; $params[]=&$like; $params[]=&$like; $params[]=&$like; $types.='sss'; }
if ($cat_filter){ $where[]='b.category_id=?'; $params[]=&$cat_filter; $types.='i'; }
$where_sql = count($where)?' WHERE '.implode(' AND ',$where):'';
$count_sql = "SELECT COUNT(*) as total FROM businesses b LEFT JOIN categories c ON b.category_id=c.id".$where_sql;
$stmt = $mysqli->prepare($count_sql);
if ($params){ array_unshift($params,$types); call_user_func_array([$stmt,'bind_param'], refValues($params)); }
$stmt->execute(); $total = $stmt->get_result()->fetch_assoc()['total'] ?? 0; $total_pages=max(1,ceil($total/$per_page));
$sql = "SELECT b.*, c.name as category_name FROM businesses b LEFT JOIN categories c ON b.category_id=c.id".$where_sql." ORDER BY b.created_at DESC LIMIT ?,?";
$params_for_query = $params; $types_for_query = $types.'ii'; $params_for_query[]=&$offset; $params_for_query[]=&$per_page; array_unshift($params_for_query,$types_for_query);
$stmt = $mysqli->prepare($sql); if($params_for_query) call_user_func_array([$stmt,'bind_param'], refValues($params_for_query)); $stmt->execute(); $result = $stmt->get_result();
$cats=[]; $rc=$mysqli->query('SELECT id,name FROM categories ORDER BY name'); while($r=$rc->fetch_assoc()) $cats[]=$r;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Guia da Cidade</title>
<style>body{font-family:Arial,Helvetica,sans-serif;max-width:1100px;margin:18px auto;padding:12px;color:#222} .grid{display:grid;grid-template-columns:1fr 320px;gap:12px} @media(max-width:900px){.grid{grid-template-columns:1fr}} .card{background:#fff;border:1px solid #eee;padding:12px;margin:8px 0;border-radius:8px} .btn{display:inline-block;padding:8px 12px;border-radius:8px;text-decoration:none;border:1px solid #333;background:#fff} table{width:100%;border-collapse:collapse} th,td{padding:8px;border-bottom:1px solid #eee} .red{background:#ffecec} .green{background:#ecffef}</style>
</head>
<body>
<header><h1>Guia da Cidade</h1><div style="float:right;"><a href="?admin=1">Admin</a></div></header>
<hr>
<div class="grid">
<main>
<div class="card">
<form method="get" class="search"><input name="q" placeholder="Buscar" value="<?php echo htmlspecialchars($search); ?>"> <select name="cat"><option value="">Todas as categorias</option><?php foreach($cats as $c) echo '<option value="'.$c['id'].'">'.htmlspecialchars($c['name']).'</option>';?></select> <button class="btn">Buscar</button></form>
</div>
<div class="card"><h3>Resultados (<?php echo $total; ?>)</h3>
<?php if ($result->num_rows==0) echo '<div class="muted">Nenhum negócio encontrado.</div>'; else while($row=$result->fetch_assoc()): ?>
<div style="border-bottom:1px dashed #eee;padding:8px 0"><h4><?php echo htmlspecialchars($row['name']); ?></h4><div class="muted"><?php echo htmlspecialchars($row['category_name'] ?? ''); ?> — <?php echo htmlspecialchars($row['address']); ?></div><div><?php echo nl2br(htmlspecialchars(substr($row['description'],0,200))); ?></div><div style="margin-top:6px"><a class="btn" href="?pagamento=<?php echo $row['slug']; ?>">Pagar R$ <?php echo number_format($row['plan_price']>0?$row['plan_price']:$planos[$row['plan']]['preco'],2,',','.'); ?></a></div></div>
<?php endwhile; ?>
<div style="margin-top:12px">
<?php for($i=1;$i<=$total_pages;$i++): if($i==$page) echo "<strong>$i</strong> "; else echo '<a href="?page='.$i.'" class="btn">'.$i.'</a> '; endfor; ?>
</div>
</div>
</main>
<aside>
<div class="card"><h4>Categorias</h4><ul><?php foreach($cats as $c) echo '<li><a href="?cat='.$c['id'].'">'.htmlspecialchars($c['name']).'</a></li>'; ?></ul></div>
<div class="card"><h4>Cadastre seu negócio</h4><div class="muted">Solicite cadastro via admin.</div></div>
</aside>
</div>

<?php // ADMIN PÁGINA
if (!empty($_GET['admin'])):
    if (empty($_SESSION['user_id'])):
        ?>
        <h2>Login Admin</h2>
        <?php if(!empty($login_error)) echo "<div style='color:red'>$login_error</div>"; ?>
        <form method="post" action="?action=login">Usuário: <input name="username"> Senha: <input type="password" name="password"> <button>Entrar</button></form>
        <?php
    else:
        // admin panel
        echo '<h2>Painel Admin</h2><a href="?">Voltar</a> | <a href="?action=logout">Sair</a>';
        echo '<h3><a href="?action=new_business">Adicionar Novo Negócio</a></h3>';
        echo '<table><tr><th>Nome</th><th>Plano</th><th>Valor (R$)</th><th>Vencimento</th><th>Ações</th></tr>';
        $r = $mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC"); $today = date('Y-m-d');
        while($b=$r->fetch_assoc()){
            $rowclass = '';
            if($b['plan']!=='free' && (empty($b['paid_until']) || $b['paid_until']<$today)) $rowclass='class=\"red\"';
            elseif($b['plan']!=='free') $rowclass='class=\"green\"';
            echo "<tr $rowclass>";
            echo '<td>'.htmlspecialchars($b['name']).'</td>';
            echo '<td>'.htmlspecialchars($b['plan']).'</td>';
            echo '<td>'.number_format($b['plan_price'],2,',','.').'</td>';
            echo '<td>'.($b['paid_until']?:'-').'</td>';
            echo '<td><a href="?action=edit_business&id='.$b['id'].'">Editar</a> | <a href="?action=confirm_payment&id='.$b['id'].'">Confirmar pagamento</a> | <a href="?action=delete_business&id='.$b['id'].'" onclick="return confirm(\'Excluir?\')">Excluir</a></td>';
            echo '</tr>';
        }
        echo '</table>';
        // form new/edit
        if($action==='edit_business' || $action==='new_business'){
            if($action==='edit_business'){ $id=(int)$_GET['id']; $b=$mysqli->query("SELECT * FROM businesses WHERE id=$id")->fetch_assoc(); }
            ?>
            <h3><?php echo $action==='new_business'?'Adicionar Novo':'Editar'; ?> Negócio</h3>
            <form method="post" action="?action=save_business">
            <input type="hidden" name="id" value="<?php echo $b['id']??0; ?>">
            Nome: <input name="name" value="<?php echo htmlspecialchars($b['name']??''); ?>"><br>
            Categoria: <select name="category_id"><?php $cats2=$mysqli->query('SELECT id,name FROM categories'); while($c=$cats2->fetch_assoc()): ?><option value="<?php echo $c['id']; ?>" <?php if(($b['category_id']??0)==$c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']); ?></option><?php endwhile; ?></select><br>
            Descrição: <textarea name="description"><?php echo htmlspecialchars($b['description']??''); ?></textarea><br>
            Endereço: <input name="address" value="<?php echo htmlspecialchars($b['address']??''); ?>"><br>
            Telefone: <input name="phone" value="<?php echo htmlspecialchars($b['phone']??''); ?>"><br>
            WhatsApp: <input name="whatsapp" value="<?php echo htmlspecialchars($b['whatsapp']??''); ?>"><br>
            Site: <input name="website" value="<?php echo htmlspecialchars($b['website']??''); ?>"><br>
            Facebook: <input name="facebook" value="<?php echo htmlspecialchars($b['facebook']??''); ?>"><br>
            Instagram: <input name="instagram" value="<?php echo htmlspecialchars($b['instagram']??''); ?>"><br>
            Plano: <select name="plan"><?php foreach($planos as $k=>$p): ?><option value="<?php echo $k;?>" <?php if(($b['plan']??'free')==$k) echo 'selected'; ?>><?php echo $p['nome']; ?></option><?php endforeach; ?></select><br>
            Valor do plano (R$): <input name="plan_price" value="<?php echo htmlspecialchars($b['plan_price']??'0'); ?>"><br>
            Vencimento: <input type="date" name="paid_until" value="<?php echo htmlspecialchars($b['paid_until']??''); ?>"><br>
            <button>Salvar</button>
            </form>
            <?php
        }
    endif;
endif;
?>
</body>
</html>
