<?php
include '../config.php';
session_start();

// Logout
if(isset($_GET['action']) && $_GET['action']==='logout'){
    session_destroy();
    header("Location: login.php");
    exit;
}

// Verifica login
if(empty($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

// Função slugify (para nomes de negócios)
function slugify($text){
    $text = preg_replace('~[^\pL0-9_]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9_]+~', '', $text);
    $text = trim($text, '-');
    return strtolower($text) ?: 'n-a';
}

// Carrega valores dos planos dinamicamente
$planos = [
    'basic'   => ['nome' => 'Basic',   'preco' => floatval(getSetting('plan_basic') ?? 29.90)],
    'pro'     => ['nome' => 'Pro',     'preco' => floatval(getSetting('plan_pro') ?? 59.90)],
    'premium' => ['nome' => 'Premium', 'preco' => floatval(getSetting('plan_premium') ?? 99.90)],
];

// Ações de salvar/excluir negócio
$action = $_GET['action'] ?? '';

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
        header('Location: index.php'); exit;
    }

    if($action==='delete_business' && !empty($_GET['id'])){
        $id=(int)$_GET['id'];
        $stmt=$mysqli->prepare("DELETE FROM businesses WHERE id=?");
        $stmt->bind_param('i',$id);
        $stmt->execute();
        header('Location: index.php'); exit;
    }
}

// Consulta todos negócios
$res = $mysqli->query("SELECT * FROM businesses ORDER BY created_at DESC");
$today = date('Y-m-d');
$image = $b['image'] ?? null;
if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir,0777,true);
    $filename = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $filename;
    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $image = "uploads/" . $filename;
    }
}
$image = $b['image'] ?? null;

if (!empty($_FILES['image']['name'])) {
    $uploadDir = "../uploads/";
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

    $filename = time() . "_" . basename($_FILES['image']['name']);
    $targetFile = $uploadDir . $filename;

    if (move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
        $image = "uploads/" . $filename; // caminho salvo no banco
    }
}

?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Admin - Guia Comercial</title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">

<div class="max-w-7xl mx-auto bg-white shadow rounded-lg p-6">
<h2 class="text-2xl font-bold mb-4">Painel Admin</h2>
<div class="flex justify-between items-center mb-4">
    <a href="?action=logout" class="text-red-600 hover:underline">Sair</a>
    <a href="settings.php" class="bg-yellow-500 text-white px-3 py-1 rounded hover:bg-yellow-600">Configurações</a>
</div>

<h3 class="text-xl font-semibold mb-2">Negócios
    <a href='?action=new_business' class="ml-4 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">Adicionar Novo</a>
    <a href="categorias.php" class="ml-4 bg-green-600 text-white px-3 py-1 rounded hover:bg-green-700">+ Categorias</a>

</h3>

<table class="w-full border-collapse border border-gray-300">
<tr class="bg-gray-200 text-left">
<th class="border p-2">Nome</th>
<th class="border p-2">Plano</th>
<th class="border p-2">Vencimento</th>
<th class="border p-2">Ações</th>
</tr>

<?php while($b=$res->fetch_assoc()): 
    $class='';
    if($b['plan']!=='free' && (!isset($b['paid_until']) || $b['paid_until']<$today)) $class='text-red-600 font-bold';
    elseif($b['plan']!=='free') $class='text-green-600 font-bold';
?>
<tr class="<?php echo $class;?>">
<td class="border p-2"><?php echo htmlspecialchars($b['name']);?></td>
<td class="border p-2"><?php echo $planos[$b['plan']]['nome']??$b['plan'];?></td>
<td class="border p-2"><?php echo $b['paid_until']?:'-';?></td>
<td class="border p-2">
<a href='?action=edit_business&id=<?php echo $b['id'];?>' class="text-blue-600 hover:underline">Editar</a> |
<a href='?action=delete_business&id=<?php echo $b['id'];?>' onclick="return confirm('Excluir?')" class="text-red-600 hover:underline">Excluir</a> |
<a href='upgrade.php?id=<?php echo $b['id'];?>' class="text-purple-600 hover:underline">Upgrade Plano</a>
</td>
</tr>
<?php endwhile; ?>
</table>

<?php
if($action==='edit_business' || $action==='new_business'):
    if($action==='edit_business'){
        $id=(int)($_GET['id']??0);
        $b = $mysqli->query("SELECT * FROM businesses WHERE id=$id")->fetch_assoc();
    }
    
?>
<div class="max-w-xl mx-auto mt-6 bg-white shadow rounded-lg p-6">
<h3 class="text-lg font-semibold mb-4"><?php echo $action==='new_business'?'Adicionar Novo':'Editar'; ?> Negócio</h3>
<!--<form method="post" action="?action=save_business" class="space-y-4">-->
    <form method="post" enctype="multipart/form-data">

<input type="hidden" name="id" value="<?php echo $b['id']??0; ?>">

<label class="block text-sm font-medium text-gray-700">Nome</label>
<input type="text" name="name" value="<?php echo $b['name']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Categoria</label>
<select name="category_id" class="w-full p-2 border rounded">
<?php $cats=$mysqli->query("SELECT id,name FROM categories"); while($c=$cats->fetch_assoc()): ?>
    <option value="<?php echo $c['id'];?>" <?php if(($b['category_id']??0)==$c['id']) echo 'selected';?>><?php echo htmlspecialchars($c['name']);?></option>
<?php endwhile; ?>
</select>
Imagem:<input type="file" name="image"><br>


<label class="block text-sm font-medium text-gray-700">Descrição</label>
<textarea name="description" class="w-full p-2 border rounded"><?php echo $b['description']??'';?></textarea>

<label class="block text-sm font-medium text-gray-700">Endereço</label>
<input type="text" name="address" value="<?php echo $b['address']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Telefone</label>
<input type="text" name="phone" value="<?php echo $b['phone']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">WhatsApp</label>
<input type="text" name="whatsapp" value="<?php echo $b['whatsapp']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Site</label>
<input type="text" name="website" value="<?php echo $b['website']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Facebook</label>
<input type="text" name="facebook" value="<?php echo $b['facebook']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Instagram</label>
<input type="text" name="instagram" value="<?php echo $b['instagram']??'';?>" class="w-full p-2 border rounded">

<label class="block text-sm font-medium text-gray-700">Plano</label>
<select name="plan" class="w-full p-2 border rounded">
<?php foreach($planos as $k=>$pl): ?>
    <option value="<?php echo $k;?>" <?php if(($b['plan']??'free')==$k) echo 'selected';?>><?php echo $pl['nome'];?></option>
<?php endforeach; ?>
</select>

<label class="block text-sm font-medium text-gray-700">Vencimento</label>
<input type="date" name="paid_until" value="<?php echo $b['paid_until']??'';?>" class="w-full p-2 border rounded">

<button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
</form>
</div>
<?php endif; ?>

</body>
</html>
