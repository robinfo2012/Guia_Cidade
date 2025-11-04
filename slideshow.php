<?php
require_once("config.php"); // ou db.php, depende de onde você define $mysqli

// Busca empresas em destaque com imagem
$res_slideshow = $mysqli->query("
    SELECT id, name, description, image 
    FROM businesses 
    WHERE is_featured=1 AND image IS NOT NULL 
    ORDER BY created_at DESC
    LIMIT 10
");
?>

<!-- Slideshow -->
<div class="max-w-7xl mx-auto px-6 py-10">
    <h3 class="text-3xl font-bold mb-6 text-gray-700">Empresas em Destaque</h3>

    <div x-data="{ slide: 0 }" class="relative">
        <!-- Slides -->
        <template x-for="(item, index) in <?= $res_slideshow->num_rows ?> " :key="index">
            <div x-show="slide === index" class="rounded-xl overflow-hidden shadow-lg">
                <?php $i = 0; while($b = $res_slideshow->fetch_assoc()): ?>
                    <img x-show="slide === <?= $i ?>" src="<?= htmlspecialchars($b['image']) ?>" alt="<?= htmlspecialchars($b['name']) ?>" class="w-full h-64 object-cover rounded-xl">
                <?php $i++; endwhile; ?>
            </div>
        </template>

        <!-- Botões -->
        <button @click="slide = (slide > 0 ? slide-1 : <?= $res_slideshow->num_rows - 1 ?>)" class="absolute left-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-2 hover:bg-opacity-100">
            &#10094;
        </button>
        <button @click="slide = (slide < <?= $res_slideshow->num_rows - 1 ?> ? slide+1 : 0)" class="absolute right-2 top-1/2 transform -translate-y-1/2 bg-white bg-opacity-70 rounded-full p-2 hover:bg-opacity-100">
            &#10095;
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
