<?php
include '../config.php';
session_start();
$error='';
if($_SERVER['REQUEST_METHOD']=='POST'){
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $business_id = (int)$_POST['business_id'];

    if($username && $password && $business_id){
        // Verifica se usuário já existe
        $stmt = $mysqli->prepare("SELECT id FROM users WHERE username=? LIMIT 1");
        $stmt->bind_param('s',$username);
        $stmt->execute();
        $res = $stmt->get_result();
        if($res->num_rows>0){
            $error='Usuário já existe';
        } else {
            $pass_hash = password_hash($password,PASSWORD_DEFAULT);
            $stmt = $mysqli->prepare("INSERT INTO users (username,password,role,business_id) VALUES (?,?, 'owner', ?)");
            $stmt->bind_param('ssi', $username, $pass_hash, $business_id);
            $stmt->execute();
            header('Location: login_owner.php');
            exit;
        }
    } else {
        $error='Preencha todos os campos';
    }
}

// Lista negócios disponíveis
$businesses = $mysqli->query("SELECT id,name FROM businesses ORDER BY name");
?>
<table>
<tr>
<th>Nome</th><th>Plano</th><th>Vencimento</th><th>Ações</th>
</tr>
<?php while($b=$res->fetch_assoc()): ?>
<tr>
<td><?php echo htmlspecialchars($b['name']);?></td>
<td><span class="<?php echo $b['plan']!=='free' && $b['paid_until']<$today?'red':'green'; ?>">
<?php echo ucfirst($b['plan']); ?></span></td>
<td><?php echo $b['paid_until']?:'-'; ?></td>
<td>
<a href='?action=edit_business&id=<?php echo $b['id'];?>' class="btn btn-blue">Editar</a>
<a href='?action=delete_business&id=<?php echo $b['id'];?>' class="btn btn-red" onclick="return confirm('Excluir?')">Excluir</a>
<a href='upgrade.php?id=<?php echo $b['id'];?>' class="btn btn-purple">Upgrade</a>
</td>
</tr>
<?php endwhile; ?>
</table>

