<?php
include 'config.php';
if(empty($_SESSION['owner_id'])){ header('Location:login.php'); exit; }

// Aqui você pode integrar o Pix, gerar QR Code, mostrar valor do plano etc.
// Exemplo simples:
$plano = 'basic';
$valor = 49;
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Upgrade Plano</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="owner-container">
<h2>Upgrade do Plano</h2>
<p>Plano: <?php echo ucfirst($plano); ?></p>
<p>Valor: R$ <?php echo number_format($valor,2,',','.'); ?></p>
<!-- Aqui você gera QR Code Pix usando API ou biblioteca -->
<p>[QR CODE AQUI]</p>
<a href="index.php" class="btn btn-blue">Voltar</a>

</div>
</body>
</html>
