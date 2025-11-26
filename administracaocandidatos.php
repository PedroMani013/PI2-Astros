<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin'])) {
    header("Location: logadm.php");
    exit;
}

if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID de votação inválido.");
}

$idvotacao = (int)$_GET['idvotacao'];

$stmt = $pdo->prepare("SELECT * FROM tb_candidatos WHERE idvotacao = ? ORDER BY nomealuno ASC");
$stmt->execute([$idvotacao]);
$candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>ASTROS - Administração de Candidatos</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
<header class="topo">
    <img src="images/fatec.png" class="logotop">
    <h1>Votação para representante de sala</h1>
    <img src="images/cps.png" class="logotop">
</header>

<main class="index">
    <h1>Administração de Candidatos</h1>

    <?php if (empty($candidatos)): ?>
        <h2 style="text-align:center; margin-top:20px;">
            Não há candidatos cadastrados nesta votação.
        </h2>
    <?php else: ?>

    <div class="boxcandidatos">

        <?php foreach ($candidatos as $c): ?>
        <?php
            $foto = !empty($c['imagem'])
                ? "data:image/jpeg;base64," . base64_encode($c['imagem'])
                : "images/fotouser.png";
        ?>
        <div class="candidato">
            <img src="<?= $foto ?>">

            <div class="candidatotext">
                <p>CANDIDATO(A):</p>
                <p><?= htmlspecialchars($c['nomealuno']) ?></p>
                <p><strong>RA:</strong> <?= htmlspecialchars($c['ra']) ?></p>
                <p><strong>Email:</strong> <?= htmlspecialchars($c['email']) ?></p>

                <div class="botaoremov">
                    <a href="remover_candidato.php?id=<?= $c['idcandidato'] ?>&idvotacao=<?= $idvotacao ?>">
                        Remover Candidatura
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <div class="finalizarsessao">
        <a href="paineladministrativo.php"><img src="images/log-out.png"> <p>Voltar Para Votações</p></a>
    </div>

</main>

<footer class="rodape">
    <img src="images/govsp.png" class="logosp">
    <img src="images/astros.png" class="logobottom">
</footer>
</div>
</body>
</html>
