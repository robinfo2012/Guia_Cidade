<?php
include '../config.php';

$msg = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    setSetting('pix_key', $_POST['pix_key']);
    setSetting('plan_basic', $_POST['plan_basic']);
    setSetting('plan_pro', $_POST['plan_pro']);
    setSetting('plan_premium', $_POST['plan_premium']);
    $msg = "Configurações atualizadas com sucesso!";
}

$currentPix = getSetting('pix_key');
$basic = getSetting('plan_basic');
$pro = getSetting('plan_pro');
$premium = getSetting('plan_premium');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>⚙️ Configurações</title>
  <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-6">
  <div class="max-w-xl mx-auto bg-white shadow rounded-lg p-6">
    <h2 class="text-xl font-bold mb-4">⚙️ Configurações do Sistema</h2>

    <?php if($msg): ?>
      <div class="p-3 bg-green-100 text-green-700 rounded mb-4"><?php echo $msg; ?></div>
    <?php endif; ?>

    <form method="post" class="space-y-4">
      <div>
        <label class="block text-sm font-medium text-gray-700">Chave Pix</label>
        <input type="text" name="pix_key" value="<?php echo htmlspecialchars($currentPix); ?>" class="w-full p-2 border rounded">
      </div>

      <h3 class="text-lg font-semibold mt-4 mb-2">💰 Valores dos Planos</h3>

      <div>
        <label class="block text-sm font-medium text-gray-700">Plano Basic</label>
        <input type="text" name="plan_basic" value="<?php echo htmlspecialchars($basic); ?>" class="w-full p-2 border rounded">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Plano Pro</label>
        <input type="text" name="plan_pro" value="<?php echo htmlspecialchars($pro); ?>" class="w-full p-2 border rounded">
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700">Plano Premium</label>
        <input type="text" name="plan_premium" value="<?php echo htmlspecialchars($premium); ?>" class="w-full p-2 border rounded">
      </div>

      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Salvar</button>
       <a href="index.php" class="btn-back">⬅ Voltar</a>
      
    </form>
  </div>
</body>
</html>
