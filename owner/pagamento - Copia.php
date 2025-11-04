<?php
require_once __DIR__ . "/../config_owner.php";
if (session_status() == PHP_SESSION_NONE) session_start();

if (empty($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit;
}

$business_id = $_SESSION['business_id'];
$business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
$today = date('Y-m-d');

// Configurações de planos
$planos = [
    'basic'=>['nome'=>'Básico','preco'=>49],
    'pro'=>['nome'=>'Pro','preco'=>99],
    'premium'=>['nome'=>'Premium','preco'=>199]
];

$msg = '';

// Processar atualização de plano (simulação pagamento)
if ($_SERVER['REQUEST_METHOD']==='POST') {
    $novo_plano = $_POST['plan'] ?? 'free';
    $paid_until = date('Y-m-d', strtotime('+1 month'));
    $stmt = $mysqli->prepare("UPDATE businesses SET plan=?, paid_until=? WHERE id=?");
    $stmt->bind_param("ssi", $novo_plano, $paid_until, $business_id);
    if($stmt->execute()){
        $msg = "Plano atualizado com sucesso! Vencimento: $paid_until";
        $business['plan'] = $novo_plano;
        $business['paid_until'] = $paid_until;
    } else {
        $msg = "Erro ao atualizar plano.";
    }
}

// Pix chave estática configurável
$pix_chave = '73982289591'; // Pode ser alterada no config ou painel admin
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Pagamento - Plano</title>
<script src="https://cdn.tailwindcss.com"></script>
<?php include "header.php"; ?>

</head>
<body class="bg-gray-100">
<div class="max-w-3xl mx-auto mt-6 bg-white p-6 rounded shadow">
    <h1 class="text-2xl font-bold text-blue-600 mb-6">Pagamento e Renovação de Plano</h1>
    <?php if($msg): ?>
    <div class="bg-green-100 text-green-700 p-2 mb-4 rounded"><?= $msg ?></div>
    <?php endif; ?>

    <p><strong>Plano Atual:</strong> <?= htmlspecialchars($business['plan']) ?> (Vencimento: <?= $business['paid_until'] ?: '-' ?>)</p>
    <p class="mt-2">Escolha um plano e gere o Pix para pagamento:</p>

    <form method="post" class="space-y-4 mt-4">
        <select name="plan" class="w-full border rounded p-2">
            <?php foreach($planos as $k=>$pl): ?>
            <option value="<?= $k ?>" <?php if($business['plan']==$k) echo 'selected'; ?>>
                <?= $pl['nome'] ?> - R$ <?= number_format($pl['preco'],2,',','.') ?>
            </option>
            <?php endforeach; ?>
        </select>
        <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Gerar Pix / Renovar</button>
    </form>

    <div class="mt-6 bg-gray-50 p-4 rounded shadow text-center">
        <h3 class="font-semibold text-gray-700 mb-2">Pix para pagamento</h3>
        <p class="mb-4">Chave Pix: <strong><?= $pix_chave ?></strong></p>
        <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=<?= urlencode($pix_chave) ?>" alt="QR Code Pix" class="mx-auto">
        <p class="text-sm text-gray-500 mt-2">Escaneie o QR code ou use a chave acima no seu app de banco para efetuar o pagamento.</p>
    </div>
</div>
</body>
</html>
