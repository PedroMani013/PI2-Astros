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

if (!$aluno || empty($aluno['idvotacao'])) {
    $mensagem = "Nenhuma votação atribuída ao seu usuário.";
}
$idvotacao = (int)$aluno['idvotacao'];

// Busca votação
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();

if (!$vot) {
    $mensagem = "Votação não encontrada.";
}

// Datas
$agora = new DateTime();
if (isset($vot)) {
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
        <h1>Votação — <?= isset($vot) ? htmlspecialchars($vot['curso']) : '—' ?></h1>

        <?php if (!empty($mensagem)): ?>
            <div class="erro"><span><?= htmlspecialchars($mensagem) ?></span></div>
        <?php else: ?>

            <div id="caixavotacao">
                <div class="headcaixavotacao">
                    <h2><?= htmlspecialchars($vot['curso']) ?> — Semestre <?= htmlspecialchars($vot['semestre']) ?></h2>
                </div>

                <div class="maincaixavotacao">
                    <p><strong>Período de candidatura:</strong> <?= (new DateTime($vot['data_candidatura']))->format('d/m/Y H:i') ?></p>
                    <p><strong>Início da votação:</strong> <?= (new DateTime($vot['data_inicio']))->format('d/m/Y H:i') ?></p>
                    <p><strong>Fim da votação:</strong> <?= (new DateTime($vot['data_final']))->format('d/m/Y H:i') ?></p>

                    <div class="botaocaixavotacao">
                        <?php if ($status === 'antes_candidatura'): ?>
                            <a href="#" class="disabled">Candidatura não iniciada</a>
                        <?php elseif ($status === 'periodo_candidatura'): ?>
                            <a href="candidatura.php?idvotacao=<?= $idvotacao ?>" class="botaocand">Quero me candidatar</a>
                            <a href="#candidatos">Ver candidatos</a>
                        <?php elseif ($status === 'periodo_votacao'): ?>
                            <?php if ($already): ?>
                                <a href="#" class="botaocand">Você já votou</a>
                            <?php else: ?>
                                <a href="#candidatos" class="botaocand">Votar agora</a>
                            <?php endif; ?>
                        <?php else: ?>
                            <a href="#candidatos">Votação encerrada — ver candidatos</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Lista de candidatos -->
            <section id="candidatos" class="boxcandidatos">
                <?php if (empty($candidatos)): ?>
                    <p style="width:100%; text-align:center; padding:2rem;">Não há candidatos nesta votação.</p>
                <?php else: ?>
                    <?php foreach ($candidatos as $c): ?>

                        <div class="candidato">
                            <div style="padding-top:2vh;">
                                <img src="image_candidato.php?id=<?= (int)$c['idcandidato'] ?>" alt="foto candidato" style="width:80%; height:auto; border-radius:50%;">
                            </div>
                            <div class="candidatotext">
                                <p><?= htmlspecialchars($c['nomealuno']) ?></p>
                                <p style="font-size:1.6vh; color:#333;">RA: <?= htmlspecialchars($c['ra']) ?></p>
                                <div style="padding:1.5vh;">
                                    <?php if ($status === 'periodo_votacao' && !$already): ?>
                                        <form method="post" action="processa_voto.php" class="botaovot">
                                            <input type="hidden" name="idvotacao" value="<?= $idvotacao ?>">
                                            <input type="hidden" name="idcandidato" value="<?= (int)$c['idcandidato'] ?>">
                                            <button type="submit">Votar</button>
                                        </form>
                                    <?php elseif ($status === 'periodo_votacao' && $already): ?>
                                        <div class="greenpopup" style="padding:1vh; width:auto;">
                                            <p>Voto já registrado</p>
                                        </div>
                                    <?php else: ?>
                                        <div style="height:3vh;"></div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                    <?php endforeach; ?>
                <?php endif; ?>
            </section>

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
