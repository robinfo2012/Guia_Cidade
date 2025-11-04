<?php
// header.php
if(empty($_SESSION['owner_id'])){
    header('Location:login.php');
    exit;
}
?>
<div class="owner-header">
    <div class="owner-logo">
        <h1>Guia Comercial</h1>
    </div>
    <nav class="owner-nav">
        <a href="index.php" class="btn btn-blue">Painel</a>
        <a href="upgrade.php" class="btn btn-purple">Upgrade / Pix</a>
        <a href="logout.php" class="btn btn-red">Sair</a>
    </nav>
</div>
