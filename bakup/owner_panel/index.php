<?php
include 'config.php';
if(empty($_SESSION['owner_id'])){ header('Location:login.php'); exit; }

// Pega os dados do negócio
$stmt = $mysqli->prepare("SELECT * FROM businesses WHERE id=? LIMIT 1");
$stmt->bind_param('i', $_SESSION['business_id']);
$stmt->execute();
$business = $stmt->get_result()->fetch_assoc();

// Salvar alterações
if($_SERVER['REQUEST_METHOD']=='POST' && isset($_POST['save'])){
    $stmt = $mysqli->prepare("UPDATE businesses SET name=?, description=?, address=?, phone=?, whatsapp=?, website=?, facebook=?, instagram=? WHERE id=?");
    $stmt->bind_param('ssssssssi',
        $_POST['name'], $_POST['description'], $_POST['address'],
        $_POST['phone'], $_POST['whatsapp'], $_POST['website'],
        $_POST['facebook'], $_POST['instagram'], $business['id']
    );
    $stmt->execute();
    header('Location:index.php');
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Painel do Dono</title>
<link rel="stylesheet" href="style.css">
</head>
<body>

    <?php include 'header.php'; ?>
<div class="owner-container">
<h2>Painel do Negócio</h2>

<div class="owner-card">
<p><strong>Negócio:</strong> <?php echo htmlspecialchars($business['name']); ?></p>
<p><strong>Plano atual:</strong> <?php echo ucfirst($business['plan']); ?></p>
<p><strong>Vencimento:</strong> <?php echo $business['paid_until'] ?: '-'; ?></p>
</div>

<form method="post">
<h3>Editar informações do negócio</h3>
Nome:<input name="name" value="<?php echo htmlspecialchars($business['name']); ?>"><br>
Descrição:<textarea name="description"><?php echo htmlspecialchars($business['description']); ?></textarea><br>
Endereço:<input name="address" value="<?php echo htmlspecialchars($business['address']); ?>"><br>
Telefone:<input name="phone" value="<?php echo htmlspecialchars($business['phone']); ?>"><br>
WhatsApp:<input name="whatsapp" value="<?php echo htmlspecialchars($business['whatsapp']); ?>"><br>
Site:<input name="website" value="<?php echo htmlspecialchars($business['website']); ?>"><br>
Facebook:<input name="facebook" value="<?php echo htmlspecialchars($business['facebook']); ?>"><br>
Instagram:<input name="instagram" value="<?php echo htmlspecialchars($business['instagram']); ?>"><br>
<button class="btn btn-green" name="save">Salvar Dados</button>
</form>

<div style="margin-top:20px;">
<a href="upgrade.php" class="btn btn-purple">Pagar / Upgrade Plano</a>
<a href="logout.php" class="btn btn-red">Sair</a>
</div>
</div>
</body>
</html>
