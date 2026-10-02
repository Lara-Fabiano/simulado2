<?php
require_once 'config.php';
verificarLogin();

if (isset($_GET['logout'])){
    session_destroy();
    header('Location: login.php');
    exit;
}

?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Fábrica</title>
</head>
<body>
    <h1>Sistema Fábica de Metálicos</h1>

    <div>
        <p>Usuário Logado: <?= htmlspecialchars($_SESSION['usuario_nome']) ?></p>
    </div>

    <hr>

    <h2>Menu</h2>
    <ul>
        <li><a href="produto.php">Cadastro de Produtos</a></li>
        <li><a href="estoque.php">Estoque</a></li>
        <li><a href="?logout=1">Sair</a></li>
    </ul>
</body>
</html>