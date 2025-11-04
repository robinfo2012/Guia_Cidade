<?php
include '../config.php';
session_start();
if(empty($_SESSION['owner_id'])){
    header('Location: login_owner.php');
    exit;
}

// Carrega o negócio do usuário
$bid = $_SESSION['business_id'];
$business = $mysqli->query("SELECT * FROM businesses WHERE id=$bid")->fetch_assoc();

// Atualizar dados do negócio
if(isset($_POST['save'])){
    $stmt = $mysqli->prepare("UPDATE businesses SET name=?, description=?, address=?, phone=?, whatsapp=?, website=?, facebook=?, instagram=? WHERE id=?");
    $stmt->bind_param(
        "ssssssssi",
        $_POST['name'], $_POST['description'], $_POST['address'], $_POST['phone'], $_POST['whatsapp'],
        $_POST['website'], $_POST['facebook'], $_POST['instagram'], $bid
    );
    $stmt->execute();
    header('Location: owner_panel.php');
    exit;
}
?>
<style>/* Container do painel do dono */
.owner-container {
    max-width:900px;
    margin:30px auto;
    padding:30px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* Cabeçalhos */
.owner-container h2 {
    font-size:28px;
    color:#1f2937;
    margin-bottom:20px;
}
.owner-container h3 {
    font-size:20px;
    color:#374151;
    margin-bottom:15px;
}

/* Formulários */
.owner-container form input,
.owner-container form select,
.owner-container form textarea {
    width:100%;
    padding:10px 14px;
    margin-bottom:15px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:15px;
    transition:0.2s;
}
.owner-container form input:focus,
.owner-container form select:focus,
.owner-container form textarea:focus {
    border-color:#3b82f6;
    outline:none;
}

/* Botões */
.owner-container .btn {
    display:inline-block;
    padding:10px 18px;
    border-radius:8px;
    font-weight:600;
    transition:0.2s;
    cursor:pointer;
    text-decoration:none;
}
.owner-container .btn-blue { background:#3b82f6; color:#fff; }
.owner-container .btn-blue:hover { background:#2563eb; }
.owner-container .btn-green { background:#10b981; color:#fff; }
.owner-container .btn-green:hover { background:#059669; }
.owner-container .btn-red { background:#ef4444; color:#fff; }
.owner-container .btn-red:hover { background:#b91c1c; }
.owner-container .btn-purple { background:#8b5cf6; color:#fff; }
.owner-container .btn-purple:hover { background:#6d28d9; }

/* Cards de resumo do negócio */
.owner-card {
    background:#f9fafb;
    padding:15px 20px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.owner-card strong { color:#111827; }

/* Links e pequenas informações */
.owner-container a {
    text-decoration:none;
    font-weight:600;
    margin-right:10px;
}
.owner-container a:hover { text-decoration:underline; color:#3b82f6; }

/* Mensagens de alerta */
.alert-red { background:#fee2e2; color:#b91c1c; padding:8px 12px; border-radius:6px; margin-bottom:15px; }
.alert-green { background:#d1fae5; color:#065f46; padding:8px 12px; border-radius:6px; margin-bottom:15px; }

/* Responsivo */
@media(max-width:768px){
    .owner-container { padding:20px; }
    .owner-container h2 { font-size:24px; }
}
</style>
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
Descrição:<textarea name="description"><?php echo $business['description']; ?></textarea><br>
Endereço:<input name="address" value="<?php echo htmlspecialchars($business['address']); ?>"><br>
Telefone:<input name="phone" value="<?php echo htmlspecialchars($business['phone']); ?>"><br>
WhatsApp:<input name="whatsapp" value="<?php echo htmlspecialchars($business['whatsapp']); ?>"><br>
Site:<input name="website" value="<?php echo htmlspecialchars($business['website']); ?>"><br>
Facebook:<input name="facebook" value="<?php echo htmlspecialchars($business['facebook']); ?>"><br>
Instagram:<input name="instagram" value="<?php echo htmlspecialchars($business['instagram']); ?>"><br>
<button class="btn btn-green" name="save">Salvar Dados</button>
</form>

<div style="margin-top:20px;">
<a href="owner_upgrade.php" class="btn btn-purple">Pagar / Upgrade Plano</a>
<a href="logout_owner.php" class="btn btn-red">Sair</a>
</div>
</div>


