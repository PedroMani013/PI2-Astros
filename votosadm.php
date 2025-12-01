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

// Buscar candidatos (EXCLUINDO o candidato especial ID=0)
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra, c.email,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ? AND c.nomealuno != 'VOTO NULO'
    ORDER BY c.nomealuno ASC
");
$sql->execute([$idvotacao]);
$candidatos = $sql->fetchAll(PDO::FETCH_ASSOC);

// Buscar quantidade de votos nulos DESTA votação
$sqlNulo = $pdo->prepare("
    SELECT COUNT(*) as total_nulos FROM tb_votos v
    INNER JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE c.idvotacao = ? AND c.nomealuno = 'VOTO NULO'
");
$sqlNulo->execute([$idvotacao]);
$votosNulos = (int)$sqlNulo->fetch()['total_nulos'];

// Buscar total de votos (incluindo nulos)
$sqlTotal = $pdo->prepare("
    SELECT COUNT(*) as total FROM tb_votos v
    WHERE v.idcandidato IN (
        SELECT idcandidato FROM tb_candidatos WHERE idvotacao = ?
        UNION SELECT 0
    )
");
$sqlTotal->execute([$idvotacao]);
$totalVotos = (int)$sqlTotal->fetch()['total'];
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <title>ASTROS - Votos</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .box-votos-nulos {
            background-color: #6c757d;
            border-radius: 2vh;
            padding: 2vh;
            margin: 2vh auto;
            width: 80%;
            color: white;
            text-align: center;
        }
        
        .box-votos-nulos h2 {
            margin-bottom: 1vh;
        }
        
        .box-total-votos {
            background-color: #6986C5;
            border-radius: 2vh;
            padding: 2vh;
            margin: 2vh auto;
            width: 80%;
            color: white;
            text-align: center;
        }
    </style>
</head>

<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" class="logotop">
        <h1>Sistema de Votação para representante de sala</h1>
        <img src="images/cps.png" class="logotop">
    </header>

    <main class="index">
        <h1>Área de Eleição</h1>

        <!-- Resumo Total -->
        <div class="box-total-votos">
            <h2>📊 Total de Votos Registrados: <?= $totalVotos ?></h2>
        </div>

        <!-- Candidatos -->
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

        <!-- Votos Nulos -->
        <?php if ($votosNulos > 0): ?>
        <div class="box-votos-nulos">
            <h2>⚫ Votos Nulos</h2>
            <p style="font-size: 2vh; margin-top: 1vh;">
                Total de votos nulos: <strong style="font-size: 2.5vh;"><?= $votosNulos ?></strong>
            </p>
        </div>
        <?php endif; ?>

        <div class="apurarvotos">
            <p><a href="votosapurados.php?idvotacao=<?= $idvotacao ?>">Apurar Votos</a></p>
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