<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

$idadmin = (int)$_SESSION['admin']['idadmin'];

// busca votações (pode filtrar por admin se preferir)
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes ORDER BY data_inicio DESC");
$stmt->execute();
$votacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Painel Administrativo</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Votação para representante de sala</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="index">
        <div class="boxpadrao">
        <h1 class="headpaineladm">PAINEL ADMINISTRATIVO</h1>
        <?php foreach ($votacoes as $v): ?>
            <div class="votacaoadm">
                <div class="infovotacaoadm">
                    <strong><?= htmlspecialchars($v['curso']) ?></strong>
                    <span>Semestre: <?= htmlspecialchars($v['semestre']) ?></span>
                    <span>Candidatura: <?= (new DateTime($v['data_candidatura']))->format('d/m/Y H:i') ?></span>
                    <span>Votação: <?= (new DateTime($v['data_inicio']))->format('d/m/Y H:i') ?> até <?= (new DateTime($v['data_final']))->format('d/m/Y H:i') ?></span>
                </div>

                <div class="botoesvotoadm">
                    <a href="ver_candidatos.php?idvotacao=<?= (int)$v['idvotacao'] ?>">Ver candidatos</a>
                    <a href="apurar_votos.php?idvotacao=<?= (int)$v['idvotacao'] ?>">Apurar votos</a>
                    <a class="botaoremovot" href="remover_votacao.php?idvotacao=<?= (int)$v['idvotacao'] ?>">Remover Votação</a>
                </div>
                
            </div>
        <?php endforeach; ?>
        <div class="criarvot">
            <a href="criarvotacao.php" class="botoesvotoadm"><div class="criavot"><img src="images/addvotacao.png" alt=""><p>Criar nova votação</p></div></a>
        </div>
        <div class="finalizarsessao" style="margin:3vh auto;">
            <a href="logout.php"><img src="images/log-out.png" alt=""> <p>Sair</p></a>
        </div>
        </div>
    </main>

    <footer class="rodape">
        <img src="images/govsp.png" alt="" class="logosp">
        <img src="images/astros.png" alt="" class="logobottom">
    </footer>
</div>
</body>
</html>
