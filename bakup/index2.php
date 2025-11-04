<?php
// portal_guia_comercial_index.php - Página pública elegante com destaques Premium
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'guia_cidade';

$mysqli = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($mysqli->connect_errno) { die('Erro conexão MySQL: ' . $mysqli->connect_error); }
$mysqli->set_charset('utf8mb4');

$search = $_GET['q'] ?? '';
$cat = (int)($_GET['cat'] ?? 0);
$page = max(1,(int)($_GET['page'] ?? 1));
$per_page = 6;
$offset = ($page-1)*$per_page;

$where = [];
$params = [];
$types = '';
if($search){ $where[] = '(name LIKE ? OR description LIKE ?)'; $params[]="%$search%"; $params[]="%$search%"; $types.='ss'; }
if($cat>0){ $where[]='category_id=?'; $params[]=$cat; $types.='i'; }
$where_sql = $where? 'WHERE '.implode(' AND ',$where):'';

$stmt = $mysqli->prepare("SELECT SQL_CALC_FOUND_ROWS * FROM businesses $where_sql ORDER BY created_at DESC LIMIT ?,?");
if($types){ $types.='ii'; $params[]=$offset; $params[]=$per_page; $stmt->bind_param($types, ...$params); } else { $stmt->bind_param('ii',$offset,$per_page); }
$stmt->execute();
$result = $stmt->get_result();
$total = $mysqli->query('SELECT FOUND_ROWS()')->fetch_row()[0];
$total_pages = ceil($total/$per_page);
$cats = $mysqli->query("SELECT id,name FROM categories ORDER BY name");

function slugify($text){
    $text = preg_replace('~[^\pL0-9_]+~u', '-', $text);
    $text = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $text = preg_replace('~[^-a-zA-Z0-9_]+~', '', $text);
    $text = trim($text, '-');
    return strtolower($text) ?: 'n-a';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Guia da Cidade</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800">
<header class="bg-white shadow sticky top-0 z-50">
  <div class="max-w-6xl mx-auto px-4 py-4 flex justify-between items-center">
    <h1 class="text-2xl font-bold text-blue-600">Guia da Cidade</h1>
    <nav class="space-x-4">
      <a href="?" class="text-gray-700 hover:text-blue-600">Início</a>
      <a href="?admin=1" class="text-gray-700 hover:text-blue-600">Admin</a>
    </nav>
  </div>
</header>
<main class="max-w-6xl mx-auto px-4 py-6">
  <!-- Destaques Premium -->
  <div class="mb-6">
    <h2 class="text-xl font-bold text-yellow-700 mb-3">Destaques Premium</h2>
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
      <?php
      $premium_res = $mysqli->query("SELECT * FROM businesses WHERE plan='premium' AND (paid_until IS NULL OR paid_until >= CURDATE()) ORDER BY RAND() LIMIT 3");
      while($p = $premium_res->fetch_assoc()): ?>
        <div class="bg-yellow-100 p-4 rounded shadow hover:shadow-lg transition">
          <h3 class="text-lg font-semibold text-yellow-800 mb-1"><?php echo htmlspecialchars($p['name']);?></h3>
          <p class="text-gray-700 text-sm mb-2"><?php echo htmlspecialchars($p['description']);?></p>
          <div class="flex flex-wrap gap-2">
            <?php if($p['phone']): ?><span class="bg-gray-100 px-2 py-1 rounded text-sm">Tel: <?php echo htmlspecialchars($p['phone']);?></span><?php endif; ?>
            <?php if($p['whatsapp']): ?><a href="https://wa.me/<?php echo preg_replace('/\D/','',$p['whatsapp']); ?>" target="_blank" class="bg-green-100 px-2 py-1 rounded text-sm hover:bg-green-200">WhatsApp</a><?php endif; ?>
            <?php if($p['website']): ?><a href="<?php echo htmlspecialchars($p['website']);?>" target="_blank" class="bg-blue-100 px-2 py-1 rounded text-sm hover:bg-blue-200">Site</a><?php endif; ?>
          </div>
        </div>
      <?php endwhile; ?>
    </div>
  </div>

  <div class="bg-white p-4 rounded shadow mb-6">
    <form method="get" class="flex flex-col lg:flex-row gap-3">
      <input type="text" name="q" placeholder="Buscar por nome, serviço ou endereço" value="<?php echo htmlspecialchars($search); ?>" class="flex-1 border rounded p-2">
      <select name="cat" class="border rounded p-2">
        <option value="">Todas as categorias</option>
        <?php while($c=$cats->fetch_assoc()): ?>
          <option value="<?php echo $c['id'];?>" <?php if($cat==$c['id']) echo 'selected'; ?>><?php echo htmlspecialchars($c['name']);?></option>
        <?php endwhile; ?>
      </select>
      <button class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Buscar</button>
    </form>
  </div>

  <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-4">
      <?php if($result->num_rows==0): ?>
        <div class="text-gray-500">Nenhum negócio encontrado.</div>
      <?php else: while($row=$result->fetch_assoc()): 
        $bgPlan='bg-white';
        $today = date('Y-m-d');
        if($row['plan']=='premium') $bgPlan='bg-yellow-100';
        elseif($row['plan']=='pro') $bgPlan='bg-green-100';
        elseif($row['plan']=='basic') $bgPlan='bg-blue-50';
      ?>
        <div class="<?php echo $bgPlan;?> p-4 rounded shadow hover:shadow-lg transition">
          <h2 class="text-xl font-semibold text-blue-600 mb-1"><?php echo htmlspecialchars($row['name']); ?></h2>
          <p class="text-gray-700 text-sm mb-2"><?php echo htmlspecialchars($row['description']); ?></p>
          <div class="text-gray-500 text-sm mb-2"><?php echo htmlspecialchars($row['address']); ?></div>
          <div class="flex flex-wrap gap-2 mb-2">
            <?php if($row['phone']): ?><span class="bg-gray-100 px-2 py-1 rounded text-sm">Tel: <?php echo htmlspecialchars($row['phone']);?></span><?php endif; ?>
            <?php if($row['whatsapp']): ?><a href="https://wa.me/<?php echo preg_replace('/\D/','',$row['whatsapp']); ?>" target="_blank" class="bg-green-100 px-2 py-1 rounded text-sm hover:bg-green-200">WhatsApp</a><?php endif; ?>
            <?php if($row['website']): ?><a href="<?php echo htmlspecialchars($row['website']);?>" target="_blank" class="bg-blue-100 px-2 py-1 rounded text-sm hover:bg-blue-200">Site</a><?php endif; ?>
            <?php if($row['facebook']): ?><a href="<?php echo htmlspecialchars($row['facebook']);?>" target="_blank" class="bg-blue-50 px-2 py-1 rounded text-sm hover:bg-blue-100">Facebook</a><?php endif; ?>
            <?php if($row['instagram']): ?><a href="<?php echo htmlspecialchars($row['instagram']);?>" target="_blank" class="bg-pink-50 px-2 py-1 rounded text-sm hover:bg-pink-100">Instagram</a><?php endif; ?>
          </div>
          <?php if($row['plan']!='free'): ?><span class="text-xs font-semibold uppercase text-gray-600">Plano: <?php echo ucfirst($row['plan']); ?><?php if($row['paid_until']) echo ' até '.$row['paid_until'];?></span><?php endif; ?>
        </div>
      <?php endwhile; endif; ?>

      <div class="flex gap-2 mt-4">
        <?php for($i=1;$i<=$total_pages;$i++): ?>
          <?php if($i==$page): ?>
            <span class="px-3 py-1 bg-gray-200 rounded"><?php echo $i; ?></span>
          <?php else: ?>
            <a href="?page=<?php echo $i; ?>&q=<?php echo urlencode($search); ?>&cat=<?php echo $cat;?>" class="px-3 py-1 border rounded hover:bg-gray-100"><?php echo $i; ?></a>
          <?php endif; ?>
        <?php endfor; ?>
      </div>
    </div>

    <aside class="space-y-4">
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Categorias</h3>
        <ul class="space-y-1">
          <?php $cats2 = $mysqli->query("SELECT id,name FROM categories ORDER BY name"); while($c2=$cats2->fetch_assoc()): ?>
            <li><a href="?cat=<?php echo $c2['id'];?>" class="text-blue-600 hover:underline"><?php echo htmlspecialchars($c2['name']);?></a></li>
          <?php endwhile; ?>
        </ul>
      </div>
      <div class="bg-white p-4 rounded shadow">
        <h3 class="font-semibold mb-2">Cadastre seu negócio</h3>
        <p class="text-gray-500 text-sm">Entre em contato pelo admin para cadastrar seu negócio e aparecer no portal.</p>
      </div>
    </aside>
  </div>
</main>
<footer class="bg-white shadow mt-6">
  <div class="max-w-6xl mx-auto px-4 py-4 text-gray-500 text-sm">© <?php echo date('Y'); ?> Guia da Cidade — Todos os direitos reservados</div>
</footer>
</body>
</html>
