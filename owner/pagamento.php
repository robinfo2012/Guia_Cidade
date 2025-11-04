<?php
require_once __DIR__ . "/../config_owner.php";
if (session_status() == PHP_SESSION_NONE) session_start();
if (empty($_SESSION['owner_id'])) header("Location: login.php");

$business_id = $_SESSION['business_id'];
$business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
$planos = ['basic'=>49,'pro'=>99,'premium'=>199];
$msg = '';

// Atualizar plano e vencimento
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $selected_plan = $_POST['plan'] ?? $business['plan'];
    if(isset($planos[$selected_plan])){
        $paid_until = date('Y-m-d', strtotime('+30 days'));
        $stmt = $mysqli->prepare("UPDATE businesses SET plan=?, paid_until=? WHERE id=?");
        $stmt->bind_param("ssi", $selected_plan, $paid_until, $business_id);
        $stmt->execute();
        $msg = "Plano atualizado com sucesso! Válido até $paid_until.";
        $business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
    }
}

// Configurar chave Pix
$pix_key = $business['pix_key'] ?? 'robinfonet@gmail.com';
$selected_amount = $planos[$business['plan']] ?? 0;

// Gerar QR Code Pix (Google Chart API)
$pix_payload_text = "00020126360014BR.GOV.BCB.PIX0114{$pix_key}520400005303986540" . number_format($selected_amount,2,'','') . "5802BR5910{$business['name']}6009CidadeBR62070503***6304";
$qr_url = "https://chart.googleapis.com/chart?cht=qr&chs=200x200&chl=" . urlencode($pix_payload_text);
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Pagamento Pix</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include "header.php"; ?>

<div class="max-w-3xl mx-auto mt-6 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Renovar Plano</h1>

    <?php if($msg): ?>
        <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $msg ?></div>
    <?php endif; ?>

    <form method="post" class="mb-6 space-y-4">
        <label>Escolha o plano:</label>
        <select name="plan" class="w-full border rounded p-2">
            <?php foreach($planos as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($business['plan']==$k)?'selected':'' ?>>
                    <?= ucfirst($k) ?> - R$ <?= number_format($v,2,',','.') ?>
                </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Gerar Pix</button>
    </form>

    <div>
        <h2 class="font-semibold text-gray-700 mb-2">Pix para pagamento:</h2>
        <img src="<?= $qr_url ?>" alt="QR Code Pix" class="mb-2 w-48 h-48">
        <p>Chave Pix: <strong><?= htmlspecialchars($pix_key) ?></strong></p>
        <p>Valor: <strong>R$ <?= number_format($planos[$business['plan']],2,',','.') ?></strong></p>
        <p class="text-gray-600 mt-2 text-sm">Após o pagamento, seu plano será atualizado automaticamente.</p>
    </div><div class="mt-6">
     <a href="index.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Voltar</a>
  </div>
</div>

</body>
</html>
