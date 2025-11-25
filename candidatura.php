<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];

if (!isset($_GET['idvotacao'])) {
    die("Votação inválida.");
}
$idvotacao = (int)$_GET['idvotacao'];

// busca votação
$stmt = $pdo->prepare("SELECT data_candidatura, data_inicio, curso FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();
if (!$vot) die("Votação não encontrada.");

// verifica período de candidatura
$agora = new DateTime();
$dataCandidatura = new DateTime($vot['data_candidatura']);
$dataInicio = new DateTime($vot['data_inicio']);

if (!($agora >= $dataCandidatura && $agora < $dataInicio)) {
    die("Fora do período de candidatura.");
}

// busca aluno
$stmt = $pdo->prepare("SELECT nome, email, ra FROM tb_alunos WHERE idaluno = ?");
$stmt->execute([$idaluno]);
$aluno = $stmt->fetch();
if (!$aluno) die("Aluno não encontrado.");

// checa já candidato
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_candidatos WHERE ra = ? AND idvotacao = ?");
$stmt->execute([$aluno['ra'], $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já está inscrito como candidato nesta votação.");
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Candidatura</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Inscrição de Candidatura</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="formmain">
        <div id="formbox">
            <h2>Votação: <?= htmlspecialchars($vot['curso']) ?></h2>

            <form method="POST" action="processa_candidatura.php" enctype="multipart/form-data">
                <input type="hidden" name="idvotacao" value="<?= $idvotacao ?>">

                <label>Nome:</label>
                <input type="text" name="nomealuno" value="<?= htmlspecialchars($aluno['nome']) ?>" required>

                <label>Email:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($aluno['email']) ?>" required>

                <label>RA:</label>
                <input type="text" name="ra" value="<?= htmlspecialchars($aluno['ra']) ?>" required>

                <label>Foto (jpg/png)</label>
                <label class="btn-upload" for="foto">Escolher foto</label>
                <input type="file" id="foto" name="foto" accept="image/*" required>

                <input type="submit" value="Enviar candidatura">
            </form>
        </div>

        <div class="finalizarsessao">
            <a href="votacoesaluno.php"><img src="images/log-out.png" alt=""> <p>Voltar</p></a>
        </div>
    </main>

    <footer class="rodape">
        <img src="images/govsp.png" alt="" class="logosp">
        <img src="images/astros.png" alt="" class="logobottom">
    </footer>
</div>
</body>
</html>
