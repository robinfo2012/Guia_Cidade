<?php
include "../config.php";
session_start();
if (empty($_SESSION['owner_id'])) {
    header("Location: login.php");
    exit;
}

$business_id = $_SESSION['business_id'];
$business = $mysqli->query("SELECT * FROM businesses WHERE id=$business_id")->fetch_assoc();
$today = date('Y-m-d');
$plan_status = 'Grátis';
if ($business['plan'] != 'free') {
    $plan_status = ($business['paid_until'] && $business['paid_until'] >= $today) ? 'Ativo' : 'Vencido';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Painel do Dono</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
<div class="max-w-5xl mx-auto mt-6">
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold text-blue-600">Olá, <?= htmlspecialchars($business['name']) ?></h1>
        <a href="logout.php" class="bg-red-500 text-white px-4 py-2 rounded hover:bg-red-600">Sair</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold text-gray-700">Plano Atual</h3>
            <p class="text-xl font-bold"><?= htmlspecialchars($business['plan']) ?> (<?= $plan_status ?>)</p>
            <p>Vencimento: <?= $business['paid_until'] ?: '-' ?></p>
            <a href="pagamento.php" class="mt-2 inline-block bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700">Pagar / Renovar</a>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold text-gray-700">Informações</h3>
            <p><strong>Telefone:</strong> <?= $business['phone'] ?></p>
            <p><strong>WhatsApp:</strong> <?= $business['whatsapp'] ?></p>
            <p><strong>Site:</strong> <?= $business['website'] ?></p>
        </div>
        <div class="bg-white p-4 rounded shadow">
            <h3 class="font-semibold text-gray-700">Redes Sociais</h3>
            <p><strong>Facebook:</strong> <?= $business['facebook'] ?></p>
            <p><strong>Instagram:</strong> <?= $business['instagram'] ?></p>
        </div>
    </div>

    <a href="perfil.php" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Editar Perfil</a>
</div>
</body>
</html>
