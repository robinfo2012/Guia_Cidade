<?php
include '../config.php';
session_start();

// Verifica login
if(empty($_SESSION['user_id'])){
    header("Location: login.php");
    exit;
}

$id = intval($_GET['id'] ?? 0);
$negocio = $mysqli->query("SELECT * FROM businesses WHERE id=$id")->fetch_assoc();
if(!$negocio) die("Negócio não encontrado!");

// Valores dos planos e chave Pix
$pixKey = getSetting('pix_key') ?? 'SEU_PIX_CHAVE';
$planos = [
    'basic'   => ['nome'=>'Basic','preco'=>floatval(getSetting('plan_basic') ?? 29.90)],
    'pro'     => ['nome'=>'Pro','preco'=>floatval(getSetting('plan_pro') ?? 59.90)],
    'premium' => ['nome'=>'Premium','preco'=>floatval(getSetting('plan_premium') ?? 99.90)],
];

// Atualiza plano após confirmação de pagamento
if(isset($_GET['confirm_plano']) && isset($planos[$_GET['confirm_plano']])){
    $plano = $_GET['confirm_plano'];
    $valor = $planos[$plano]['preco'];
    $paid_until = date('Y-m-d', strtotime('+30 days'));
    $stmt = $mysqli->prepare("UPDATE businesses SET plan=?, paid_until=? WHERE id=?");
    $stmt->bind_param("ssi",$plano,$paid_until,$id);
    $stmt->execute();
    header("Location: index.php");
    exit;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Upgrade Plano - <?php echo htmlspecialchars($negocio['name']); ?></title>
<link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 p-6">
<div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
<h2 class="text-xl font-bold mb-4">Upgrade de Plano</h2>
<p class="mb-2">Negócio: <span class="font-semibold"><?php echo htmlspecialchars($negocio['name']); ?></span></p>
<p class="mb-4">Plano atual: <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded"><?php echo ucfirst($negocio['plan']); ?></span></p>

<form method="get" class="space-y-4">
<input type="hidden" name="id" value="<?php echo $id; ?>">
<label class="block font-medium">Escolha o plano</label>
<select name="plano" class="w-full p-2 border rounded">
<?php foreach($planos as $k=>$pl): ?>
<option value="<?php echo $k; ?>"><?php echo $pl['nome']; ?> - R$ <?php echo number_format($pl['preco'],2,',','.'); ?></option>
<?php endforeach; ?>
</select>
<button class="bg-green-600 text-white px-4 py-2 rounded hover:bg-green-700 mt-2">Gerar QR Code</button>
</form>

<?php
if(!empty($_GET['plano']) && isset($planos[$_GET['plano']])):
    $plano = $_GET['plano'];
    $valor = $planos[$plano]['preco'];
?>
<div class="mt-6 text-center">
<h3 class="text-lg font-semibold mb-2">Pagamento via Pix</h3>
<p class="mb-2">Plano <span class="font-bold"><?php echo $planos[$plano]['nome']; ?></span> - <span class="text-blue-600 font-bold">R$ <?php echo number_format($valor,2,',','.'); ?></span></p>
<img src="../qrcode_pix.php?valor=<?php echo $valor; ?>&chave=<?php echo urlencode($pixKey); ?>" class="mx-auto mb-3">
<p class="text-sm text-gray-600">Escaneie o QR Code para pagar via Pix</p>
<a href="?id=<?php echo $id; ?>&confirm_plano=<?php echo $plano; ?>" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 mt-3 inline-block">Confirmar Pagamento</a>
</div>
<?php endif; ?>
</div>
</body>
</html>
