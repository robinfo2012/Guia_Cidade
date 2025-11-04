<?php
include '../config.php';
session_start();
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
            header('Location: owner_panel.php');
            exit;
        }
    }
    $error='Usuário ou senha inválidos';
}

?>
<h2>Login Dono de Negócio</h2>
<?php if($error) echo "<p style='color:red'>$error</p>"; ?>
<form method="post">
Usuário: <input name="username"><br>
Senha: <input type="password" name="password"><br>
<button>Entrar</button>
</form>
<style>/* Container principal */
.public-container { max-width:1200px; margin:30px auto; padding:20px; }

/* Grid de cards */
.card-grid { display:grid; grid-template-columns:repeat(auto-fit,minmax(250px,1fr)); gap:20px; }

/* Card de negócio */
.card {
    background:#fff;
    padding:20px;
    border-radius:10px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
    transition:transform 0.2s, box-shadow 0.2s;
}
.card:hover { transform:translateY(-5px); box-shadow:0 10px 20px rgba(0,0,0,0.1); }

.card h3 { margin-bottom:10px; color:#1f2937; }
.card p { font-size:14px; color:#4b5563; margin-bottom:5px; }
.card a { display:inline-block; margin-top:10px; color:#3b82f6; text-decoration:none; font-weight:600; }
.card a:hover { text-decoration:underline; }

/* Filtros de categoria */
.filter-bar { margin-bottom:30px; display:flex; flex-wrap:wrap; gap:10px; }
.filter-bar button { padding:8px 16px; border:none; border-radius:5px; background:#e5e7eb; cursor:pointer; transition:0.2s; }
.filter-bar button.active, .filter-bar button:hover { background:#3b82f6; color:#fff; }

/* Responsive */
@media (max-width:768px){ .card-grid { grid-template-columns:1fr; } }

/* Reset básico */
* { margin:0; padding:0; box-sizing:border-box; font-family: 'Segoe UI', Tahoma, sans-serif; }

body { background: #f5f7fa; color:#333; line-height:1.6; }

/* Containers */
.container { max-width:1200px; margin:20px auto; padding:20px; background:#fff; border-radius:10px; box-shadow:0 5px 15px rgba(0,0,0,0.05); }

/* Tabelas */
table { width:100%; border-collapse:collapse; margin-top:20px; }
th, td { padding:10px; text-align:left; border-bottom:1px solid #eee; }
th { background:#f0f3f7; font-weight:600; }

/* Alertas de planos */
.green { background:#d1fae5; color:#065f46; font-weight:bold; padding:5px 10px; border-radius:5px; }
.red { background:#fee2e2; color:#b91c1c; font-weight:bold; padding:5px 10px; border-radius:5px; }

/* Botões */
.btn { display:inline-block; padding:8px 16px; border-radius:5px; text-decoration:none; font-weight:600; transition:0.2s; }
.btn-blue { background:#3b82f6; color:#fff; }
.btn-blue:hover { background:#2563eb; }
.btn-red { background:#ef4444; color:#fff; }
.btn-red:hover { background:#b91c1c; }
.btn-green { background:#10b981; color:#fff; }
.btn-green:hover { background:#059669; }
.btn-purple { background:#8b5cf6; color:#fff; }
.btn-purple:hover { background:#6d28d9; }

/* Formulários */
input, select, textarea { width:100%; padding:8px 12px; margin:5px 0 15px 0; border:1px solid #ccc; border-radius:5px; font-size:14px; }
textarea { min-height:80px; }
button { cursor:pointer; }

/* Cabeçalhos */
h2,h3 { margin-bottom:15px; color:#1f2937; }
a { text-decoration:none; }

/* Layout responsivo */
@media (max-width:768px){
    .container { padding:15px; }
    table, th, td { font-size:14px; }
}
/* Container do painel do dono */
.owner-container {
    max-width:900px;
    margin:30px auto;
    padding:30px;
    background:#fff;
    border-radius:12px;
    box-shadow:0 10px 25px rgba(0,0,0,0.08);
}

/* Cabeçalhos */
.owner-container h2 {
    font-size:28px;
    color:#1f2937;
    margin-bottom:20px;
}
.owner-container h3 {
    font-size:20px;
    color:#374151;
    margin-bottom:15px;
}

/* Formulários */
.owner-container form input,
.owner-container form select,
.owner-container form textarea {
    width:100%;
    padding:10px 14px;
    margin-bottom:15px;
    border:1px solid #d1d5db;
    border-radius:8px;
    font-size:15px;
    transition:0.2s;
}
.owner-container form input:focus,
.owner-container form select:focus,
.owner-container form textarea:focus {
    border-color:#3b82f6;
    outline:none;
}

/* Botões */
.owner-container .btn {
    display:inline-block;
    padding:10px 18px;
    border-radius:8px;
    font-weight:600;
    transition:0.2s;
    cursor:pointer;
    text-decoration:none;
}
.owner-container .btn-blue { background:#3b82f6; color:#fff; }
.owner-container .btn-blue:hover { background:#2563eb; }
.owner-container .btn-green { background:#10b981; color:#fff; }
.owner-container .btn-green:hover { background:#059669; }
.owner-container .btn-red { background:#ef4444; color:#fff; }
.owner-container .btn-red:hover { background:#b91c1c; }
.owner-container .btn-purple { background:#8b5cf6; color:#fff; }
.owner-container .btn-purple:hover { background:#6d28d9; }

/* Cards de resumo do negócio */
.owner-card {
    background:#f9fafb;
    padding:15px 20px;
    border-radius:10px;
    margin-bottom:20px;
    box-shadow:0 5px 15px rgba(0,0,0,0.05);
}
.owner-card strong { color:#111827; }

/* Links e pequenas informações */
.owner-container a {
    text-decoration:none;
    font-weight:600;
    margin-right:10px;
}
.owner-container a:hover { text-decoration:underline; color:#3b82f6; }

/* Mensagens de alerta */
.alert-red { background:#fee2e2; color:#b91c1c; padding:8px 12px; border-radius:6px; margin-bottom:15px; }
.alert-green { background:#d1fae5; color:#065f46; padding:8px 12px; border-radius:6px; margin-bottom:15px; }

/* Responsivo */
@media(max-width:768px){
    .owner-container { padding:20px; }
    .owner-container h2 { font-size:24px; }
}

</style>