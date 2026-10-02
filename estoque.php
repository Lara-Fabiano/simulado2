<?php
require_once 'config.php';
verificarLogin();

$mensagem = $tipoMensagem = $alertaEstoque = "";

if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $pid = (int)($_POST['produto_id'] ?? 0);
    $tipo = $_POST['tipo']?? "";
    $qtd = (int)($_POST['quantidade'] ?? 0);
    $data = $_POST['data'] ?? "";

    if(!$pid || !$tipo || !$data || $qtd <=0){
        $mensagem = 'Preencha todos os campos!';
        $tipoMensagem = 'erro';
    } else{
        $stmt = $conn->prepare("SELECT * FROM produto WHERE id = ?");
        $stmt->execute([$pid]);
        if(!($prod = $stmt->fetch())){
            $mensagem = 'Produto não encontrado';
            $tipoMensagem = 'erro';
        }else{
            $ant = $prod['estoque_atual'];
            $novo = $tipo === 'ENTRADA' ? $ant + $qnt : $ant - $qtd;
            if($novo < 0){
                $mensagem = 'Estoque insuficiente!';
                $tipoMensagem = 'erro';
            }else{
                try{
                $conn->beginTransaction();
                $conn->prepare("INSERT INTO movimentacao (tipo, data, quantidade, saldo_anterior, usuario_id, produto_id) VALUES(?,?,?,?,?,?)")->execute([$tipo === 'ENTRADA'?1:2, $data, $qtd, $ant, $_SESSION['usuario_id'], $pid]);
                $conn->prepare("UPDATE produto SET estoque_atual = ? WHERE id = ?")->execute([$novo,$pid]);
                $conn->commit();
                $mensagem = "Movimentação registrada com sucesso!";
                $tipoMensagem = 'sucesso';
                if($tipo === 'SAIDA' && $novo <= $prod['estoque_minimo']){
                    $alertaEstoque = "ALERTA DE ESTOQUE BAIXO! O produto {$prod['nome']} está com estoque BAIXO! Atual: {$novo}, Mínimo: {$prod['estoque_minimo']}.";
                }
                }catch(PDOException $e){
                    if($conn->inTransaction()) $conn -> rollBack();
                    $mensagem = 'Erro: ' . $e->getMessage();
                    $tipoMensagem = 'erro';
                }
            }
        }
    }
}
$produto = $conn->query("SELECT * FROM produto WHERE ativo = 1 ORDER BY nome")->fetchAll();
$movimentacao = $conn->query("SELECT m.*,
(SELECT p.nome FROM produto p WHERE p.id = m.produto_id) AS produto_nome,
(SELECT u.nome FROM usuario u WHERE u.id = m.usuario_id) AS usuario_nome
    FROM movimentacao m 
    ORDER BY m.id DESC;")->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestão de Estoque - Fábrica</title>
</head>
<body>
    <h1>Gestão de Estoque - Fábrica</h1>
    <p><a href="index.php"> ⬅️Voltar</a></p>
    <hr>


    <?php if ($mensagem): ?>
        <p style="color: <?= $tipoMensagem === 'sucesso' ? 'green' : 'red' ?>">
            <?= htmlspecialchars($mensagem) ?>
    </p>
    <hr>
   <?php endif;?>


    <?php if ($alertaEstoque): ?>
        <p style="color: gold;">
            <?= htmlspecialchars($alertaEstoque) ?>
        </p>
        <hr>
   <?php endif;?>     


   <h2>Nova Movimentação de Estoque</h2>
   <form method="post">
        <label>Produto:
            <select name="produto_id" required>
                <option value="">Selecione...</option>
                <?php foreach($produto as $p): ?>
                    <option value="<?= $p['id']?>">
                        <?= htmlspecialchars($p['nome']) ?>
                    </option>   
                <?php endforeach; ?>
            </select>         
        </label>   
        <br>
        <label>Tipo:
            <input type="radio" name="tipo" value="ENTRADA" required> ENTRADA
            <input type="radio" name="tipo" value="SAIDA" required> SAIDA
        </label>    
        <br>
        <label>Quantidade:
            <input type="number" name="quantidade" min='1' required>
        </label>    
        <br>
        <label>Data:
            <input type="date" name="data" value="<?= date('Y-m-d') ?>" required>
        </label>    
        <br>
        <br>
        <button type="submit">Registrar Movimentação</button>
    </form>


    <h2>Lista de Produtos</h2>
    <?php if($produto): ?>
        <table border="1">
            <tr>
                <th>Código</th>
                <th>Nome</th>
                <th>Categoria</th>
                <th>Preço</th>
                <th>Estoque Atual</th>
                <th>Estoque Mínimo</th>
                <th>Aviso</th>
            </tr>
            <?php foreach($produto as $p): ?>
                <tr>
                    <td><?= htmlspecialchars($p['codigo']) ?></td>
                    <td><?= htmlspecialchars($p['nome']) ?></td>
                    <td><?= htmlspecialchars($p['categoria'] ?? "") ?></td>
                    <td>R$ <?= number_format($p['preco'],2,',','.') ?></td>
                    <td><?= $p['estoque_atual'] ?></td>
                    <td><?= $p['estoque_minimo'] ?></td>
                    <td>
                        <?= $p['estoque_atual'] < $p['estoque_minimo'] ?
                    '<strong>AVISO: estoque baixo</strong>' : 'Normal';?>
                    </td>    
                </tr>
                <?php endforeach;?>    
        </table>
        <?php else: ?> <p>Nenhum produto cadastrado</p><?php endif;?>
        
        <hr>


        <h2>Histórico de Movimentação</h2>
        <?php if($movimentacao): ?>
            <table border="1">
                <tr>
                    <th>ID</th>
                    <th>Data</th>
                    <th>Produtos</th>
                    <th>Tipo</th>
                    <th>Quantidade</th>
                    <th>Saldo Anterior</th>
                    <th>Usuários</th>
                </tr>
                <?php foreach($movimentacao as $m):?>
                    <tr>
                        <td><?= $m['id'] ?></td>
                        <td><?= date('d/m/Y', strtotime($m['data'])) ?></td>
                        <td><?= htmlspecialchars($m['produto_nome']) ?></td>
                        <td><?= $m['tipo'] == 1 ? 'ENTRADA' : 'SAIDA' ?></td>
                        <td><?= $m['quantidade']?></td>
                        <td><?= $m['saldo_anterior']?></td>
                        <td><?= htmlspecialchars($m['usuario_nome'] ?? "Não informado") ?></td>
                    </tr>   
                <?php endforeach;?>    
            </table>     
        <?php else: ?> <p> Nenhuma movimentação registrada.</p><?php endif; ?>


</body>
</html>