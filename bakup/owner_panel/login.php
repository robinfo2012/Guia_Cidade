<?php
include 'config.php';
$error='';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $stmt = $mysqli->prepare("SELECT id,password,business_id FROM users WHERE username=? AND role='owner' LIMIT 1");
    $stmt->bind_param('s',$username);
    $stmt->execute();
    $res = $stmt->get_result();
    if($row=$res->fetch_assoc()){
        if(password_verify($password,$row['password'])){
            $_SESSION['owner_id']=$row['id'];
            $_SESSION['business_id']=$row['business_id'];
            header('Location:index.php');
            exit;
        }
    }
    $error='Usuário ou senha inválidos';
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<title>Login Dono de Negócio</title>
<link rel="stylesheet" href="style.css">
</head>
<body>
<div class="owner-container">
<h2>Login Dono de Negócio</h2>
<?php if($error) echo "<p class='alert-red'>$error</p>"; ?>
<form method="post">
Usuário:<input name="username"><br>
Senha:<input type="password" name="password"><br>
<button class="btn btn-blue">Entrar</button>
</form>
</div>
</body>
</html>
