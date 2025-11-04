<?php
session_start();
require_once("../config.php");

// Verifica se está logado
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $id = (int) $_GET['id'];

    // Busca imagem antes de excluir
    $stmt = $mysqli->prepare("SELECT image FROM businesses WHERE id=?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($row = $res->fetch_assoc()) {
        // Remove imagem do servidor se existir
        if (!empty($row['image']) && file_exists("../" . $row['image'])) {
            unlink("../" . $row['image']);
        }

        // Exclui do banco
        $del = $mysqli->prepare("DELETE FROM businesses WHERE id=?");
        $del->bind_param("i", $id);
        $del->execute();
    }
}

// Redireciona de volta
header("Location: index.php?msg=deleted");
exit;
