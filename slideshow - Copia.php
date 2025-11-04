<?php
require_once "config.php";

// Buscar empresas em destaque
$stmt = $mysqli->query("SELECT id, name, image, slug FROM businesses WHERE is_featured = 1 ORDER BY created_at DESC LIMIT 10");
$featured = $stmt->fetch_all(MYSQLI_ASSOC);
?>

<div class="slideshow-container" style="max-width:100%;margin:20px auto;position:relative;overflow:hidden;border-radius:12px;box-shadow:0 6px 20px rgba(0,0,0,0.2);">
    <?php if ($featured): ?>
        <?php foreach ($featured as $i => $biz): ?>
            <div class="mySlides" style="display:none;">
                <a href="detalhes.php?slug=<?= htmlspecialchars($biz['slug']) ?>">
                    <img src="uploads/<?= htmlspecialchars($biz['image'] ?: 'default.png') ?>" alt="<?= htmlspecialchars($biz['name']) ?>" style="width:100%;height:350px;object-fit:cover;">
                    <div style="position:absolute;bottom:20px;left:30px;background:rgba(0,0,0,0.6);color:#fff;padding:10px 20px;border-radius:6px;font-size:18px;">
                        <?= htmlspecialchars($biz['name']) ?>
                    </div>
                </a>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="mySlides" style="display:block;">
            <img src="placeholder.jpg" alt="Nenhum destaque" style="width:100%;height:350px;object-fit:cover;">
            <div style="position:absolute;bottom:20px;left:30px;background:rgba(0,0,0,0.6);color:#fff;padding:10px 20px;border-radius:6px;font-size:18px;">
                Nenhum destaque disponível
            </div>
        </div>
    <?php endif; ?>

    <!-- Botões -->
    <a class="prev" onclick="plusSlides(-1)" style="cursor:pointer;position:absolute;top:50%;left:0;transform:translateY(-50%);padding:16px;font-size:24px;color:#fff;background:rgba(0,0,0,0.4);border-radius:0 4px 4px 0;">&#10094;</a>
    <a class="next" onclick="plusSlides(1)" style="cursor:pointer;position:absolute;top:50%;right:0;transform:translateY(-50%);padding:16px;font-size:24px;color:#fff;background:rgba(0,0,0,0.4);border-radius:4px 0 0 4px;">&#10095;</a>
</div>

<div style="text-align:center;margin-top:12px;">
    <?php if ($featured): ?>
        <?php foreach ($featured as $i => $biz): ?>
            <span class="dot" onclick="currentSlide(<?= $i+1 ?>)" style="cursor:pointer;height:12px;width:12px;margin:0 4px;background-color:#bbb;border-radius:50%;display:inline-block;"></span>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<script>
let slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) { showSlides(slideIndex += n); }
function currentSlide(n) { showSlides(slideIndex = n); }

function showSlides(n) {
    let i;
    let slides = document.getElementsByClassName("mySlides");
    let dots = document.getElementsByClassName("dot");
    if (slides.length === 0) return;
    if (n > slides.length) {slideIndex = 1}
    if (n < 1) {slideIndex = slides.length}
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].style.backgroundColor = "#bbb";
    }
    slides[slideIndex-1].style.display = "block";
    if(dots.length > 0) dots[slideIndex-1].style.backgroundColor = "#2a5298";
}

// autoplay
setInterval(() => { plusSlides(1); }, 5000);
</script>
