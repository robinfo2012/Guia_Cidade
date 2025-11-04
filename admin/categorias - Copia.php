<?php
session_start();
$mysqli = new mysqli("localhost","root","","guia_cidade");
if($mysqli->connect_errno){ die("Erro: ".$mysqli->connect_error); }
$mysqli->set_charset("utf8mb4");

if(empty($_SESSION['user_id'])){ header("Location: login.php"); exit; }

$action = $_GET['action'] ?? '';
if($action==='add' && !empty($_POST['name'])){
    $stmt = $mysqli->prepare("INSERT INTO categories (name) VALUES (?)");
    $stmt->bind_param("s", $_POST['name']);
    $stmt->execute();
    header("Location: categorias.php"); exit;
}
if($action==='delete' && !empty($_GET['id'])){
    $id=(int)$_GET['id'];
    $stmt=$mysqli->prepare("DELETE FROM categories WHERE id=?");
    $stmt->bind_param("i",$id);
    $stmt->execute();
    header("Location: categorias.php"); exit;
}
$cats=$mysqli->query("SELECT * FROM categories ORDER BY name ASC");
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Categorias - Admin</title>
<style>
body { font-family: Arial, sans-serif; background:#f3f4f6; }
.container { max-width:700px; margin:30px auto; background:#fff; padding:20px; border-radius:12px; box-shadow:0 5px 15px rgba(0,0,0,0.1); }
h2 { margin-top:0; }
table { width:100%; border-collapse:collapse; margin-top:15px; }
th,td { border:1px solid #ddd; padding:8px; text-align:left; }
th { background:#3b82f6; color:#fff; }
a.btn { padding:6px 12px; border-radius:6px; text-decoration:none; font-weight:600; }
.btn-red { background:#ef4444; color:#fff; }
.btn-blue { background:#3b82f6; color:#fff; }
</style>
</head>
<body>
<div class="container">
    <h2>Gerenciar Categorias</h2>
    <form method="post" action="?action=add">
        <input type="text" name="name" placeholder="Nova categoria" required>
        <button class="btn btn-blue">Adicionar</button>
    </form>
    <table>
        <tr><th>Categoria</th><th>Ações</th></tr>
        <?php while($c=$cats->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($c['name']); ?></td>
            <td>
                <a href="?action=delete&id=<?= $c['id']; ?>" class="btn btn-red" onclick="return confirm('Excluir categoria?')">Excluir</a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
    <p><a href="index.php" class="btn btn-blue">⬅ Voltar ao Admin</a></p>
</div>
</body>
</html>
