<?php
// admin/settings.php
include '../config_owner.php';
session_start();

// Proteção: só admin logado acessa
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Função para pegar valores do settings
function getSetting($mysqli, $key, $default = '') {
    $stmt = $mysqli->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['setting_value'];
    return $default;
}

// Atualiza settings ao enviar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pix_key = trim($_POST['pix_key'] ?? '');
    $receiver_name = trim($_POST['pix_receiver_name'] ?? '');
    $receiver_city = trim($_POST['pix_receiver_city'] ?? '');

    $settings = [
        'pix_key' => $pix_key,
        'pix_receiver_name' => $receiver_name,
        'pix_receiver_city' => $receiver_city
    ];

    foreach ($settings as $k => $v) {
        $stmt = $mysqli->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) 
                                  ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
        $stmt->bind_param("ss", $k, $v);
        $stmt->execute();
    }

    $success = "Configurações salvas com sucesso!";
}

// Valores atuais
$pix_key = getSetting($mysqli, 'pix_key');
$receiver_name = getSetting($mysqli, 'pix_receiver_name');
$receiver_city = getSetting($mysqli, 'pix_receiver_city');
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Configurações do PIX</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-6">⚙️ Configurações PIX</h1>

    <?php if (!empty($success)): ?>
      <div class="bg-green-100 border border-green-300 text-green-700 p-3 rounded mb-4">
        <?= htmlspecialchars($success) ?>
      </div>
    <?php endif; ?>

    <form method="post" class="bg-white p-6 rounded shadow space-y-4">
      <div>
        <label class="block text-sm font-semibold mb-1">🔑 Chave PIX</label>
        <input type="text" name="pix_key" value="<?= htmlspecialchars($pix_key) ?>" required 
               class="w-full border p-2 rounded" placeholder="ex: email, CPF, CNPJ ou chave aleatória">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">👤 Nome do Recebedor</label>
        <input type="text" name="pix_receiver_name" value="<?= htmlspecialchars($receiver_name) ?>" 
               class="w-full border p-2 rounded" maxlength="25" placeholder="ex: Minha Empresa">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1">🏙️ Cidade</label>
        <input type="text" name="pix_receiver_city" value="<?= htmlspecialchars($receiver_city) ?>" 
               class="w-full border p-2 rounded" maxlength="15" placeholder="ex: Salvador">
      </div>

      <div class="flex gap-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">💾 Salvar</button>
        <a href="index.php" class="px-4 py-2 border rounded">⬅ Voltar</a>
      </div>
    </form>
  </div>
</body>
</html>
