<?php
header("Content-Type: application/xml; charset=utf-8");
include "config.php";

echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
  <!-- Página inicial -->
  <url>
    <loc>https://seudominio.com/</loc>
    <changefreq>daily</changefreq>
    <priority>1.0</priority>
  </url>

  <!-- Categorias -->
  <?php
  $cats = $mysqli->query("SELECT id, name FROM categories ORDER BY name");
  while($c = $cats->fetch_assoc()):
  ?>
  <url>
    <loc>https://seudominio.com/index.php?cat=<?= $c['id'] ?></loc>
    <changefreq>weekly</changefreq>
    <priority>0.8</priority>
  </url>
  <?php endwhile; ?>

  <!-- Negócios -->
  <?php
  $biz = $mysqli->query("SELECT id, slug FROM businesses ORDER BY id");
  while($b = $biz->fetch_assoc()):
    $slug = !empty($b['slug']) ? $b['slug'] : $b['id'];
  ?>
  <url>
    <loc>https://seudominio.com/detalhes.php?id=<?= $slug ?></loc>
    <changefreq>monthly</changefreq>
    <priority>0.6</priority>
  </url>
  <?php endwhile; ?>
</urlset>
