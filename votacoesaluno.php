<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];

// Busca aluno e idvotacao
$stmt = $pdo->prepare("SELECT idvotacao, nome, curso, semestre FROM tb_alunos WHERE idaluno = ?");
$stmt->execute([$idaluno]);
$aluno = $stmt->fetch();

$mensagem = '';
$status = '';
$vot = null;
$candidatos = [];
$already = 0;

if (!$aluno) {
    $mensagem = "Aluno não encontrado.";
    $status = 'erro';
} elseif (empty($aluno['idvotacao'])) {
    $mensagem = "Nenhuma votação atribuída ao seu usuário.";
    $status = 'erro';
} else {
    $idvotacao = (int)$aluno['idvotacao'];

    // Busca votação
    $stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
    $stmt->execute([$idvotacao]);
    $vot = $stmt->fetch();

    if (!$vot) {
        $mensagem = "Votação não encontrada.";
        $status = 'erro';
    } else {
        // Datas
        $agora = new DateTime();
        $dataCandidatura = new DateTime($vot['data_candidatura']);
        $dataInicio = new DateTime($vot['data_inicio']);
        $dataFinal = new DateTime($vot['data_final']);

        if ($agora < $dataCandidatura) {
            $status = 'antes_candidatura';
        } elseif ($agora >= $dataCandidatura && $agora < $dataInicio) {
            $status = 'periodo_candidatura';
        } elseif ($agora >= $dataInicio && $agora <= $dataFinal) {
            $status = 'periodo_votacao';
        } else {
            $status = 'encerrada';
        }

        // Verifica se já votou (qualquer candidato dessa votação)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM tb_votos v
            JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
            WHERE v.idaluno = ? AND c.idvotacao = ?
        ");
        $stmt->execute([$idaluno, $idvotacao]);
        $already = (int)$stmt->fetch()['total'];

        // Busca candidatos
        $stmt = $pdo->prepare("SELECT idcandidato, nomealuno, ra FROM tb_candidatos WHERE idvotacao = ?");
        $stmt->execute([$idvotacao]);
        $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Área de Votação - Aluno</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Votação Para Representante de Sala</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="boxpadrao">
        <h1>Votação — <?= $vot ? htmlspecialchars($vot['curso']) : '—' ?></h1>

        <?php if (!empty($mensagem)): ?>
            <div class="erro"><span><?= htmlspecialchars($mensagem) ?></span></div>
        <?php elseif ($vot): ?>

            <div id="caixavotacao">
                <div class="headcaixavotacao">
                    <h2>Votação para representante<br>de sala e suplente</h2>
                </div>

                <div class="maincaixavotacao">
                    <p><strong>Curso:</strong> <?= htmlspecialchars($vot['curso']) ?></p>
                    <p><strong>Semestre:</strong> <?= htmlspecialchars($vot['semestre']) ?>º</p>
                    <p><strong>Data para candidatura:</strong> <?= (new DateTime($vot['data_candidatura']))->format('d/m/Y') ?></p>
                    <p><strong>Período de eleição:</strong> <?= (new DateTime($vot['data_inicio']))->format('d/m/Y') ?> até <?= (new DateTime($vot['data_final']))->format('d/m/Y') ?></p>
                    <p><strong>Candidatos:</strong> <?= count($candidatos) ?></p>

                    <div class="botaocaixavotacao">
                        <?php if ($status === 'antes_candidatura'): ?>
                            <a href="#" class="btn-disabled">Candidatar</a>
                            <a href="#" class="btn-disabled">Votar</a>
                        <?php elseif ($status === 'periodo_candidatura'): ?>
                            <a href="candidatura.php?idvotacao=<?= $idvotacao ?>" class="btn-active">Candidatar</a>
                            <a href="#" class="btn-disabled">Votar</a>
                        <?php elseif ($status === 'periodo_votacao'): ?>
                            <a href="#" class="btn-disabled">Candidatar</a>
                            <?php if ($already): ?>
                                <a href="#" class="btn-disabled">Você já votou</a>
                            <?php else: ?>
                                <a href="areaeleicao.php?idvotacao=<?= $idvotacao ?>" class="btn-active">Votar</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="#" class="btn-disabled">Candidatar</a>
                            <a href="#" class="btn-disabled">Votar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        <?php endif; ?>

        <div class="finalizarsessao" style="margin:3vh auto;">
            <a href="logout.php"><img src="images/log-out.png" alt=""> <p>Sair</p></a>
        </div>

    </main>

    <footer class="rodape">
        <img src="images/govsp.png" alt="" class="logosp">
        <img src="images/astros.png" alt="" class="logobottom">
    </footer>
</div>
</body>
</html>