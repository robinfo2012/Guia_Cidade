<?php
include "../config.php";
$page_title = 'Dashboard';
include "header.php";

// Estatísticas
$total_businesses = $mysqli->query("SELECT COUNT(*) as c FROM businesses")->fetch_assoc()['c'];
$total_users = $mysqli->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'];
$total_categories = $mysqli->query("SELECT COUNT(*) as c FROM categories")->fetch_assoc()['c'];
$today = date('Y-m-d');
$plan_expired = $mysqli->query("SELECT COUNT(*) as c FROM businesses WHERE plan!='free' AND (paid_until IS NULL OR paid_until<'$today')")->fetch_assoc()['c'];
$plan_active = $mysqli->query("SELECT COUNT(*) as c FROM businesses WHERE plan!='free' AND paid_until>='$today'")->fetch_assoc()['c'];
?>

<div class="max-w-6xl mx-auto mt-8 grid sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-6">
  <div class="bg-white shadow rounded-lg p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-700">Negócios</h3>
    <p class="text-3xl font-bold text-blue-600 mt-2"><?= $total_businesses; ?></p>
  </div>
  <div class="bg-white shadow rounded-lg p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-700">Usuários</h3>
    <p class="text-3xl font-bold text-blue-600 mt-2"><?= $total_users; ?></p>
  </div>
  <div class="bg-white shadow rounded-lg p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-700">Categorias</h3>
    <p class="text-3xl font-bold text-blue-600 mt-2"><?= $total_categories; ?></p>
  </div>
  <div class="bg-white shadow rounded-lg p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-700">Plano Vencido</h3>
    <p class="text-3xl font-bold text-red-600 mt-2"><?= $plan_expired; ?></p>
  </div>
  <div class="bg-white shadow rounded-lg p-6 text-center">
    <h3 class="text-lg font-semibold text-gray-700">Plano Ativo</h3>
    <p class="text-3xl font-bold text-green-600 mt-2"><?= $plan_active; ?></p>
  </div>
</div>

<?php include "footer.php"; ?>
