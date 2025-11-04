<?php
// owner/pagamento.php
// Requisitos: config.php define $mysqli (conexão)
// Coloque este arquivo em owner/ e inclua header/footer do owner se quiser.

include '../config_owner.php';
session_start();
// Proteção básica - ajuste conforme sua sessão
if (!isset($_SESSION['owner_id'])) {
    header("Location: owner_login.php");
    exit;
}

// Helpers: getSetting
function getSetting($mysqli, $key, $default = '') {
    $stmt = $mysqli->prepare("SELECT setting_value FROM settings WHERE setting_key = ? LIMIT 1");
    $stmt->bind_param("s", $key);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) return $row['setting_value'];
    return $default;
}

// CRC16-CCITT (polynomial 0x1021, init 0xFFFF) for EMV QR
function crc16_ccitt($data) {
    $polynomial = 0x1021;
    $crc = 0xFFFF;
    $bytes = array_values(unpack('C*', $data));
    foreach ($bytes as $b) {
        $crc ^= ($b << 8);
        for ($i = 0; $i < 8; $i++) {
            if ($crc & 0x8000) {
                $crc = (($crc << 1) & 0xFFFF) ^ $polynomial;
            } else {
                $crc = ($crc << 1) & 0xFFFF;
            }
        }
    }
    return strtoupper(sprintf("%04X", $crc & 0xFFFF));
}

// monta TLV helper
function tlv($id, $value) {
    $len = str_pad(strlen($value), 2, '0', STR_PAD_LEFT);
    return $id . $len . $value;
}

// Monta payload EMV para PIX (padrão simples para chave estática)
// Atenção: este é um payload padrão usado por muitos geradores — alguns PSPs têm variações.
// Não cobre todos os casos de PIC/TEF corporativo.
function build_pix_payload($pix_key, $merchant_name, $merchant_city, $txid = '', $amount = '') {
    // Payload format based on EMV + BR Code
    // 00 - Payload Format Indicator - "01"
    // 01 - Point of Initiation Method - "12" (if dynamic) or "11" (static) -- we use 11 for static
    $payload = '';
    $payload .= tlv('00','01');
    $payload .= tlv('01','11'); // static
    // Merchant Account Information - GUI + key
    // 26 - (Merchant Account Information) template with nested:
    //    00 -> GUI = "BR.GOV.BCB.PIX"
    //    01 -> chave PIX, ou 02 merchant key (for pix with additional info)
    $ma = '';
    $ma .= tlv('00','BR.GOV.BCB.PIX');
    $ma .= tlv('01', $pix_key); // chave PIX
    $payload .= tlv('26', $ma);

    // Merchant Category Code - 5204 default "0000" (sem informação específica)
    $payload .= tlv('52','0000');
    // Transaction Currency - 986 = BRL
    $payload .= tlv('53','986');
    // Transaction amount (optional) — if provided, format with dot decimal
    if ($amount !== '' && is_numeric($amount)) {
        // format with dot and 2 decimals
        $amount = number_format(floatval($amount), 2, '.', '');
        $payload .= tlv('54', $amount);
    }
    // Country code
    $payload .= tlv('58','BR');
    // Merchant name (max 25) and city (max 15)
    $mn = strtoupper(substr($merchant_name, 0, 25));
    $mc = strtoupper(substr($merchant_city, 0, 15));
    $payload .= tlv('59', $mn);
    $payload .= tlv('60', $mc);

    // Additional data field template (optional) - 62
    // 62 -> 05 -> TXID (transaction id) or '*' for random
    $ad = '';
    if ($txid !== '') {
        $ad .= tlv('05', substr($txid, 0, 25));
    } else {
        // If you want to force random TXID, you can set '*' or leave empty
        $ad .= tlv('05', '*');
    }
    $payload .= tlv('62', $ad);

    // CRC placeholder '6304' + CRC
    $payload_for_crc = $payload . '63' . '04';
    $crc = crc16_ccitt($payload_for_crc);
    $payload .= tlv('63', $crc);

    return $payload;
}

// dados default do settings
$pix_key = getSetting($mysqli, 'pix_key', '');
$merchant_name = getSetting($mysqli, 'pix_receiver_name', 'GUAI CIDADE');
$merchant_city = getSetting($mysqli, 'pix_receiver_city', 'CIDADE');

// processa POST (gerar)
$generated = false;
$payload = '';
$qr_url = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amount = trim($_POST['amount'] ?? '');
    $txid = trim($_POST['txid'] ?? '');
    // sanitize pix key
    $pix_key_use = trim($_POST['pix_key'] ?? $pix_key);
    $payload = build_pix_payload($pix_key_use, $merchant_name, $merchant_city, $txid, $amount);
    // gerar QR via Google Chart API (ou pode usar biblioteca server-side)
    // url-encode payload
    $payload_enc = urlencode($payload);
    // Google Chart QR size 300x300
    $qr_url = "https://chart.googleapis.com/chart?cht=qr&chs=400x400&chl={$payload_enc}&chld=L|2";
    $generated = true;
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8">
  <title>Pagamento PIX</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
  <div class="max-w-3xl mx-auto p-6">
    <h1 class="text-2xl font-bold mb-4">Gerar PIX - Pagamento</h1>

    <form method="post" class="bg-white p-4 rounded shadow space-y-4">
      <div>
        <label class="block text-sm font-semibold">Chave PIX (padrão do sistema)</label>
        <input type="text" name="pix_key" value="<?= htmlspecialchars($pix_key) ?>" class="w-full border p-2 rounded" />
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-sm font-semibold">Valor (R$)</label>
          <input type="text" name="amount" placeholder="Ex: 49.90" class="w-full border p-2 rounded" />
        </div>
        <div>
          <label class="block text-sm font-semibold">TXID (opcional)</label>
          <input type="text" name="txid" placeholder="ID da transação (ex: pedido123)" class="w-full border p-2 rounded" />
        </div>
      </div>

      <div class="flex gap-2">
        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Gerar QR PIX</button>
        <a href="business_list.php" class="px-4 py-2 border rounded">Voltar</a>
      </div>
    </form>

    <?php if ($generated): ?>
      <div class="mt-6 bg-white p-4 rounded shadow">
        <h2 class="font-semibold mb-2">QR Code PIX</h2>
        <div class="flex gap-6 items-center">
          <div>
            <img src="<?= $qr_url ?>" alt="QR PIX" class="w-64 h-64 object-contain border rounded">
            <div class="mt-2 text-sm text-gray-600">Direito do usuário: escaneie o QR com o app do banco.</div>
          </div>
          <div class="flex-1">
            <label class="block font-semibold">Payload (copia e cola)</label>
            <textarea id="pixPayload" rows="8" class="w-full border p-2 rounded"><?= htmlspecialchars($payload) ?></textarea>

            <div class="mt-3 flex gap-2">
              <button onclick="copyText()" type="button" class="bg-green-600 text-white px-4 py-2 rounded">Copiar Payload</button>
              <a href="<?= $qr_url ?>" target="_blank" class="bg-gray-200 px-4 py-2 rounded">Abrir QR</a>
              <a href="<?= $qr_url ?>" download="pix_qr.png" class="bg-gray-200 px-4 py-2 rounded">Baixar QR</a>
            </div>

            <div class="mt-4 text-sm text-gray-500">
              <p><strong>Chave PIX:</strong> <?= htmlspecialchars($pix_key_use) ?></p>
              <p><strong>Nome:</strong> <?= htmlspecialchars($merchant_name) ?> — <strong>Cidade:</strong> <?= htmlspecialchars($merchant_city) ?></p>
              <?php if (!empty($amount)): ?><p><strong>Valor:</strong> R$ <?= htmlspecialchars($amount) ?></p><?php endif; ?>
              <?php if (!empty($txid)): ?><p><strong>TXID:</strong> <?= htmlspecialchars($txid) ?></p><?php endif; ?>
            </div>
          </div>
        </div>
      </div>
      <script>
        function copyText(){
          const el = document.getElementById('pixPayload');
          el.select();
          el.setSelectionRange(0, 99999);
          navigator.clipboard.writeText(el.value).then(()=>{ alert('Payload copiado!'); });
        }
      </script>
    <?php endif; ?>
  </div>
</body>
</html>
