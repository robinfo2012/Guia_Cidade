
<?php

// detalhes.php — exibe detalhes de um negócio
include "config.php";

$id = intval($_GET['id']);
$res = $mysqli->query("SELECT * FROM businesses WHERE id = $id");
$biz = $res->fetch_assoc();

if (!$biz) {
  die("Empresa não encontrada");
}

// Variáveis para SEO
$title = $biz['name'] . " - Guia da Cidade";
$description = substr(strip_tags($biz['description']), 0, 160);
$image = !empty($biz['image']) ? "https://seudominio.com/" . $biz['image'] : "https://seudominio.com/assets/no-image.png";
$url = "https://seudominio.com/detalhes.php?id=" . $biz['id'];

// busca negócio com categoria
$stmt = $mysqli->prepare("
    SELECT b.*, c.name AS category_name
    FROM businesses b
    LEFT JOIN categories c ON b.category_id = c.id
    WHERE b.id = ?
    LIMIT 1
");
$stmt->bind_param("i", $id);
$stmt->execute();
$res = $stmt->get_result();
$business = $res->fetch_assoc();
$stmt->close();

if (!$business) {
    // negócio não encontrado
    header("Location: index.php");
    exit;
}

// helper para obter URL da imagem (aceita: nome de arquivo, 'uploads/arquivo' ou URL completa)
function image_url($img) {
    if (empty($img)) return null;
    // se já for URL absoluta
    if (preg_match('#^https?://#i', $img)) return $img;
    // se começar com slash (caminho absoluto no servidor)
    if (strpos($img, '/') === 0) return $img;
    // se já contém uploads/
    if (strpos($img, 'uploads/') !== false) return $img;
    // senão assumimos que é apenas o nome do arquivo dentro da pasta uploads
    return 'uploads/' . $img;
}

// sanitização pras exibições
$name = htmlspecialchars($business['name'] ?? '');
$category = htmlspecialchars($business['category_name'] ?? '');
$description = htmlspecialchars($business['description'] ?? '');
$address = htmlspecialchars($business['address'] ?? '');
$phone = htmlspecialchars($business['phone'] ?? '');
$whatsapp_raw = $business['whatsapp'] ?? '';
$whatsapp = htmlspecialchars($whatsapp_raw ?: '');
$website = htmlspecialchars($business['website'] ?? '');
$facebook = htmlspecialchars($business['facebook'] ?? '');
$instagram = htmlspecialchars($business['instagram'] ?? '');
$plan = htmlspecialchars($business['plan'] ?? 'free');
$paid_until = $business['paid_until'] ?? null;
$today = date('Y-m-d');
$plan_status = ($plan !== 'free' && $paid_until && $paid_until >= $today) ? 'Ativo' : (($plan !== 'free' && $paid_until && $paid_until < $today) ? 'Vencido' : 'Grátis');

$img_url = image_url($business['image'] ?? '');

// formata whatsapp pra wa.me (remove tudo que não for número)
$whatsapp_digits = preg_replace('/\D+/', '', $whatsapp_raw);
$wa_link = $whatsapp_digits ? "https://wa.me/{$whatsapp_digits}" : null;
?>
<!doctype html>
<html lang="pt-BR">
<head>
   <meta charset="utf-8">
  <title><?= htmlspecialchars($title) ?></title>
  <meta name="description" content="<?= htmlspecialchars($description) ?>">
  <meta name="keywords" content="<?= htmlspecialchars($biz['name']) ?>, negócios, guia comercial, serviços, <?= htmlspecialchars($biz['address']) ?>">
  <meta name="author" content="Guia da Cidade">

  <!-- Open Graph (Facebook / WhatsApp) -->
  <meta property="og:title" content="<?= htmlspecialchars($title) ?>">
  <meta property="og:description" content="<?= htmlspecialchars($description) ?>">
  <meta property="og:image" content="<?= $image ?>">
  <meta property="og:url" content="<?= $url ?>">
  <meta property="og:type" content="website">

  <!-- Twitter Card -->
  <meta name="twitter:card" content="summary_large_image">
  <meta name="twitter:title" content="<?= htmlspecialchars($title) ?>">
  <meta name="twitter:description" content="<?= htmlspecialchars($description) ?>">
  <meta name="twitter:image" content="<?= $image ?>">

  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://cdn.jsdelivr.net/npm/remixicon/fonts/remixicon.css" rel="stylesheet">
</head>
<body class="bg-gray-50 text-gray-800">

<!-- Header simples -->
<header class="bg-white shadow sticky top-0 z-40">
  <div class="max-w-6xl mx-auto px-6 py-4 flex items-center justify-between">
    <a href="index.php" class="text-2xl font-bold text-blue-600">🌆 Cidadela</a>
    <nav class="space-x-4">
      <a href="index.php" class="text-gray-700 hover:text-blue-600">Início</a>
      <a href="index.php#categorias" class="text-gray-700 hover:text-blue-600">Categorias</a>
    </nav>
  </div>
</header>

<main class="max-w-5xl mx-auto px-6 py-10">
  <article class="bg-white rounded-lg shadow overflow-hidden">
    <!-- imagem / hero -->
    <div class="relative">
      <?php if ($img_url): ?>
        <img src="<?= htmlspecialchars($img_url) ?>" alt="<?= $name ?>" class="w-full h-64 object-cover">
      <?php else: ?>
        <div class="w-full h-64 bg-gradient-to-r from-gray-200 to-gray-100 flex items-center justify-center">
          <!-- placeholder SVG -->
          <svg width="120" height="120" viewBox="0 0 24 24" fill="none" class="text-gray-400">
            <path d="M3 7a2 2 0 0 1 2-2h3l2 2h6a2 2 0 0 1 2 2v6" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
            <rect x="3" y="7" width="18" height="10" rx="2" stroke="currentColor" stroke-width="1.5"/>
            <circle cx="12" cy="12" r="2.5" fill="currentColor"/>
          </svg>
        </div>
      <?php endif; ?>

      <!-- badge do plano -->
      <div class="absolute top-4 left-4">
        <?php if ($plan === 'premium'): ?>
          <span class="bg-red-600 text-white px-3 py-1 rounded-full text-sm">Premium</span>
        <?php elseif ($plan === 'pro'): ?>
          <span class="bg-blue-600 text-white px-3 py-1 rounded-full text-sm">Pro</span>
        <?php elseif ($plan === 'basic'): ?>
          <span class="bg-yellow-500 text-white px-3 py-1 rounded-full text-sm">Básico</span>
        <?php else: ?>
          <span class="bg-gray-300 text-gray-800 px-3 py-1 rounded-full text-sm">Grátis</span>
        <?php endif; ?>
      </div>

      <!-- status do plano -->
      <div class="absolute top-4 right-4">
        <span class="px-3 py-1 rounded-full text-sm <?= $plan_status === 'Ativo' ? 'bg-green-100 text-green-800' : ($plan_status === 'Vencido' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') ?>">
          <?= $plan_status ?><?= ($plan_status !== 'Grátis' && $paid_until) ? " • " . date('d/m/Y', strtotime($paid_until)) : "" ?>
        </span>
      </div>
    </div>

    <div class="p-6">
      <header class="mb-4">
        <h1 class="text-2xl font-bold text-gray-800"><?= $name ?></h1>
        <?php if ($category): ?><p class="text-sm text-gray-500"><?= $category ?></p><?php endif; ?>
      </header>

      <div class="prose max-w-none text-gray-700 mb-6">
        <?= nl2br($description ?: '<em>Sem descrição cadastrada.</em>') ?>
      </div>

      <div class="grid md:grid-cols-2 gap-6">
        <div>
          <h3 class="font-semibold mb-2">Informações</h3>
          <ul class="space-y-2 text-gray-700">
            <?php if ($address): ?>
              <li><i class="ri-map-pin-2-line text-blue-600 align-middle"></i> <?= $address ?> <?php if($address): ?><a class="text-sm text-blue-600 hover:underline ml-2" target="_blank" href="https://www.google.com/maps/search/<?= urlencode($address) ?>">Ver mapa</a><?php endif; ?></li>
            <?php endif; ?>

            <?php if ($phone): ?>
              <li><i class="ri-phone-line text-blue-600 align-middle"></i> <?= htmlspecialchars($phone) ?></li>
            <?php endif; ?>

            <?php if ($wa_link): ?>
              <li>
                <i class="ri-whatsapp-line text-green-600 align-middle"></i>
                <a href="<?= $wa_link ?>" target="_blank" class="text-green-700 hover:underline ml-2">
                  <?= $whatsapp ?: preg_replace('/\D+/', '', $whatsapp_raw) ?>
                </a>
              </li>
            <?php endif; ?>
          </ul>
        </div>

        <div>
          <h3 class="font-semibold mb-2">Redes & Site</h3>
          <div class="flex flex-col gap-2">
            <?php if ($website): ?>
              <a href="<?= $website ?>" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-gray-800 text-white rounded hover:bg-gray-900">
                <i class="ri-global-line"></i> Site
              </a>
            <?php endif; ?>

            <?php if ($facebook): ?>
              <a href="<?= $facebook ?>" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
                <i class="ri-facebook-circle-fill"></i> Facebook
              </a>
            <?php endif; ?>

            <?php if ($instagram): ?>
              <a href="<?= $instagram ?>" target="_blank" class="inline-flex items-center gap-2 px-3 py-2 bg-pink-500 text-white rounded hover:bg-pink-600">
                <i class="ri-instagram-line"></i> Instagram
              </a>
            <?php endif; ?>
          </div>
        </div>
      </div>

      <div class="mt-6">
        <a href="index.php#negocios" class="inline-block px-5 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">← Voltar</a>

        <!--<?php
        // Se houver sessão de owner/admin e for dono desse negócio, mostra botão editar
        if (session_status() === PHP_SESSION_NONE) session_start();
        $show_edit = false;
        if (!empty($_SESSION['owner_id']) && !empty($_SESSION['business_id']) && intval($_SESSION['business_id']) === intval($business['id'])) {
            $show_edit = true;
        }
        if (!empty($_SESSION['admin_id'])) $show_edit = true;
        ?>
        <?php if ($show_edit): ?>
          <a href="admin/business_form.php?id=<?= $business['id'] ?>" class="ml-3 inline-block px-5 py-2 bg-yellow-500 text-white rounded hover:bg-yellow-600">Editar</a>
        <?php endif; ?>-->
      </div>
    </div>
  </article>
</main>

<footer class="bg-gray-900 text-gray-300 py-8 mt-12">
  <div class="max-w-6xl mx-auto px-6 text-center">
    © <?= date('Y') ?> Cidadela — Guia Local
  </div>
</footer>

</body>
</html>
