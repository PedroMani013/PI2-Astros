<?php
session_start();
require_once "conexao.php";

if (!isset($_SESSION['admin'])) {
    header("Location: logadm.php");
    exit;
}

if (!isset($_GET['idvotacao'])) {
    die("Votação não definida!");
}

$idvotacao = (int)$_GET['idvotacao'];

// Buscar candidatos
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra, c.email,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ?
    ORDER BY c.nomealuno ASC
");
$sql->execute([$idvotacao]);
$candidatos = $sql->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <title>ASTROS - Sistema De Votação</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" class="logotop">
        <h1>Votação Para Representante de Sala</h1>
        <img src="images/cps.png" class="logotop">
    </header>

    <main class="index">
        <h1>Área de Eleição</h1>

        <div class="boxvotos">

            <?php if (empty($candidatos)): ?>

                <p style="text-align:center; font-size:18px; width:100%; margin-top:20px;">
                    Nenhum candidato cadastrado nesta votação.
                </p>

            <?php else: ?>

                <?php foreach ($candidatos as $c): ?>
                    <div class="boxaluno">
                        <div class="boxalunotitulo">
                            <h2>Candidato: <?= htmlspecialchars($c['nomealuno']) ?></h2>
                        </div>

                        <div class="boxalunotexto">
                            <p>RA: <?= htmlspecialchars($c['ra']) ?></p>
                            <p>E-mail: <?= htmlspecialchars($c['email']) ?></p>
                            <p>Quantidade de votos: <strong><?= (int)$c['total_votos'] ?></strong></p>
                        </div>
                    </div>
                <?php endforeach; ?>

            <?php endif; ?>

        </div>

        <div class="apurarvotos">
            <p><a href="#">Apurar Votos</a></p>
        </div>

        <div class="finalizarsessao">
            <a href="paineladministrativo.php">
                <img src="images/log-out.png">
                <p>Voltar Para Votações</p>
            </a>
        </div>

    </main>

    <footer class="rodape">
        <img src="images/govsp.png" class="logosp">
        <img src="images/astros.png" class="logobottom">
    </footer>
</div>

</body>
</html>
