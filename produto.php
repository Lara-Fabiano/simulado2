<?php
require_once 'config.php';
verificarLogin();

$mensagem = $tipoMensagem = "";

if(isset($_GET['excluir'])) {
    try {
        $conn->prepare("DELETE FROM produto WHERE id = ?")
        ->execute([$_GET['excluir']]);
        $mensagem = "Produto excluído com sucesso";
        $tipoMensagem = "sucesso";
    } catch (PDOException $e){
        $mensagem = "Erro ao excluir". $e->getMessage();
        $tipoMensagem = "erro";
    }
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $id = $_POST['id'] ?? 0;

    $data = [
        'codigo' => trim($_POST['codigo'] ?? ''),
        'nome' => trim($_POST['nome'] ?? ''),
        'preco' => trim($_POST['preco'] ?? 0),
        'estoque_atual' => trim($_POST['estoque_atual'] ?? 0),
        'estoque_minimo' => trim($_POST['estoque_minimo'] ?? 0),
        'ativo' => trim($_POST['ativo'] ?? 1),
        'categoria' => trim($_POST['categoria'] ?? null),
    ];

    if(!$data['codigo'] || !$data['nome'] || $data['preco'] <= 0){
        $mensagem = "Preencha todos os campos obrigatórios.";
        $tipoMensagem = "erro";
    }else{
        if($id > 0){
            $sql = 'UPDATE produto SET 
            codigo=:codigo,
            nome=:nome,
            preco=:preco,
            estoque_atual=:estoque_atual,
            estoque_minimo=:estoque_minimo,
            ativo=:ativo,
            categoria=:categoria
            WHERE id=:id';
        $data['id'] = $id;    

        }else{
            $sql = 'INSERT INTO produto (
                codigo, nome, preco, estoque_atual, estoque_minimo, ativo, categoria
            ) VALUES (
                :codigo, :nome, :preco, :estoque_atual, :estoque_minimo, :ativo, :categoria
            )';    
        }
        try {
            $conn->prepare($sql)->execute($data);
            $mensagem = $id > 0 ? 'Produto atualizado!' : 'Produto cadastrado!';
            $tipoMensagem = 'sucesso';
        } catch (PDOException $e){
            $mensagem = "Erro:". $e->getMessage();
            $tipoMensagem = "erro";
        }    
    }
}

$produtoEditar = null;
if(isset($_GET['editar'])){
    $stmt = $conn->prepare("SELECT * FROM produto WHERE id = ?");
    $stmt->execute([$_GET['editar']]);
    $produtoEditar = $stmt->fetch();
}

$busca = trim($_GET['busca'] ?? '');
$sql = "SELECT * FROM produto WHERE ativo = 1" .
    ($busca ? " AND (nome LIKE :b OR codigo LIKE :b OR categoria LIKE :b)" : "") .
    " ORDER BY nome";
$stmt = $conn->prepare($sql);
$stmt->execute($busca ? ['b' => "%$busca%"] : []);
$produto = $stmt->fetchAll();

$ativo = [1 => 'Ativo', 2 => 'Inativo'];

?>    

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de produtos - Fábrica</title>
</head>
<body>
    <h1> Cadastro de produtos - Fábrica</h1>
    <p><a href="index.php">⬅️Voltar</a></p>
    <hr>

    <?php if($mensagem): ?>
        <p style="color: <?= $tipoMensagem === 'sucesso' ? 'green' : 'red' ?>">
        <?= ($mensagem) ?>
        </p>    
    <?php endif; ?>
    
    <h2><?= $produtoEditar? 'Editar' : 'Novo' ?> Produtos</h2>
    <form method="POST">
        <input type="hidden" name="id" value="<?= $produtoEditar['id'] ?? 0 ?>">
        <label> Código: * <input type="text" name="codigo" required value="<?= ($produtoEditar['codigo'] ?? '')?>"></label><br>
        <label> Nome: * <input type="text" name="nome" size="40" required value="<?= ($produtoEditar['nome'] ?? '')?>"></label><br>
        <label> Preço: * <input type="number" name="preco" step="0.01" required value="<?= ($produtoEditar['preco'] ?? '')?>"></label><br>
        <label> Estoque Atual:  <input type="number" min="0" name="estoque_atual" value="<?= ($produtoEditar['estoque_atual'] ?? 0)?>"></label><br>
        <label> Estoque Mínimo:  <input type="number" min="0" name="estoque_minimo" value="<?= ($produtoEditar['estoque_minimo'] ?? 0)?>"></label><br>

        <label>Status (Ativo):
            <select name="ativo">
                <option value="1" <?= ($produtoEditar['ativo'] ?? 1) == 1 ? 'selected' : ''?>>Ativo</option>
                <option value="0" <?= ($produtoEditar['ativo'] ?? 1) == 0 ? 'selected' : ''?>>Inativo</option>
            </select>
        </label><br>
        <label> Categoria: <input type="text" name="categoria" size="40" value="<?= ($produtoEditar['categoria'] ?? '')?>"></label><br>

        <br><button type="submit">Salvar</button>
        <?php if($produtoEditar): ?><a href="produto.php"><button type="button">Cancelar</button><?php endif; ?>
    </form>    

    <hr>

    <h2>Lista de produtos</h2>
    <form method= "GET">
        <label>Buscar
            <input type="text" name="busca" value="<?= ($busca) ?>"
                placeholder="Nome, Código ou Categoria...">
        </label>
        <button type="submit">Buscar</button>
        <?php if($busca): ?><a href="produto.php">Limpar</a><?php endif; ?>
    </form>
    <br>

    <?php if($produto): ?>
        <table border="1" cellpadding="5" cellspacing="0">
            <tr>
                
                <th>Código</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Estoque Atual</th>
                <th>Estoque Mínimo</th>
                <th>Status</th>
                <th>Categoria</th>
                <th>Ações</th>
            </tr>  
            <?php foreach($produto as $p): ?>
                <tr>
                    <td><?= ($p['codigo']) ?></td>
                    <td><?= ($p['nome']) ?></td>
                    <td><?= ($p['preco']) ?></td>
                    <td><?= $p['estoque_atual'] ?></td>
                    <td><?= $p['estoque_minimo'] ?></td>
                    <td><?= $ativo[$p['ativo']] ?></td>
                    <td><?= ($p['categoria'] ?? '') ?></td>
                    <td>
                        <a href="produto.php?editar=<?= $p['id'] ?>">Editar</a> 
                        <a href="produto.php?excluir=<?= $p['id'] ?>" onclick="return confirm('Deseja excluir?')">Excluir</a>
                    </td>
                </tr>
            <?php endforeach;?>
        </table>
        <?php else: ?>
            <p>Nenhum produto encontrado.</p>
        <?php endif; ?>
</body>
</html>