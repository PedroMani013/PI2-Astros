<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: login_aluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];

// Busca aluno e idvotacao
$stmt = $pdo->prepare("SELECT idvotacao, nome, curso, semestre, ra FROM tb_alunos WHERE idaluno = ?");
$stmt->execute([$idaluno]);
$aluno = $stmt->fetch();

$mensagem = '';
$status = '';
$vot = null;
$candidatos = [];
$already = 0;
$jaCandidato = false; // NOVO: flag para verificar se já é candidato

if (!$aluno) {
    $mensagem = "Aluno não encontrado.";
    $status = 'erro';
} elseif (empty($aluno['idvotacao'])) {
    $mensagem = "Nenhuma votação atribuída ao seu usuário.";
    $status = 'erro';
} else {
    $idvotacao = (int)$aluno['idvotacao'];

    // Busca votação COM os nomes do representante e suplente
    $stmt = $pdo->prepare("
        SELECT v.*, 
               c_rep.nomealuno as nome_representante,
               c_sup.nomealuno as nome_suplente
        FROM tb_votacoes v
        LEFT JOIN tb_candidatos c_rep ON v.idcandidato_representante = c_rep.idcandidato
        LEFT JOIN tb_candidatos c_sup ON v.idcandidato_suplente = c_sup.idcandidato
        WHERE v.idvotacao = ?
    ");
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

        // NOVO: Verifica se o aluno JÁ É CANDIDATO nesta votação
        $stmtCand = $pdo->prepare("
            SELECT COUNT(*) as total FROM tb_candidatos 
            WHERE idvotacao = ? AND ra = ? AND nomealuno != 'VOTO NULO'
        ");
        $stmtCand->execute([$idvotacao, $aluno['ra']]);
        $jaCandidato = (int)$stmtCand->fetch()['total'] > 0;

        // Verifica se já votou (qualquer candidato dessa votação)
        $stmt = $pdo->prepare("
            SELECT COUNT(*) as total FROM tb_votos v
            WHERE v.idaluno = ? AND v.idcandidato IN (
                SELECT idcandidato FROM tb_candidatos WHERE idvotacao = ?
            )
        ");
        $stmt->execute([$idaluno, $idvotacao]);
        $already = (int)$stmt->fetch()['total'];

        // Busca candidatos (apenas se votação não foi finalizada)
        if ($vot['ativa'] === 'sim') {
            $stmt = $pdo->prepare("SELECT idcandidato, nomealuno, ra FROM tb_candidatos WHERE idvotacao = ? AND nomealuno != 'VOTO NULO'");
            $stmt->execute([$idvotacao]);
            $candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <title>ASTROS - Eleições</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .votacao-finalizada-badge {
            background-color: #a32024;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
            font-weight: bold;
        }
        
        .ja-candidato-badge {
            background-color: #147C0E;
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            display: inline-block;
            margin: 10px 0;
            font-weight: bold;
        }
    </style>
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Sistema de Eleição para Representante de Sala</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="boxpadrao">
        <h1>Eleição — <?= $vot ? htmlspecialchars($vot['curso']) : '—' ?></h1>

        <?php if (!empty($mensagem)): ?>
            <div class="erro"><span><?= htmlspecialchars($mensagem) ?></span></div>
        <?php elseif ($vot): ?>

            <div id="caixavotacao">
                <div class="headcaixavotacao">
                    <h2>Eleição para representante<br>de sala e suplente</h2>
                </div>

                <div class="maincaixavotacao">
                    <p><strong>Curso:</strong> <?= htmlspecialchars($vot['curso']) ?></p>
                    <p><strong>Semestre:</strong> <?= htmlspecialchars($vot['semestre']) ?>º</p>
                    <p><strong>Data para candidatura:</strong> <?= (new DateTime($vot['data_candidatura']))->format('d/m/Y') ?></p>
                    <p><strong>Período de votação:</strong> <?= (new DateTime($vot['data_inicio']))->format('d/m/Y') ?> até <?= (new DateTime($vot['data_final']))->format('d/m/Y') ?></p>

                    <?php if ($vot['ativa'] === 'não'): ?>
                        <!-- VOTAÇÃO FINALIZADA - MOSTRAR RESULTADOS -->
                        
                        <?php if ($vot['nome_representante']): ?>
                            <div class="resultado-eleicao">
                                <p><strong>Representante Eleito: </strong><?= htmlspecialchars($vot['nome_representante']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <?php if ($vot['nome_suplente']): ?>
                            <div class="resultado-eleicao">
                                <p><strong>Suplente Eleito:</strong> <?= htmlspecialchars($vot['nome_suplente']) ?></p>
                            </div>
                        <?php endif; ?>
                        
                        <div class="votacao-finalizada-badge">
                            ✔ Eleição Finalizada
                        </div>

                    <?php else: ?>
                        <!-- VOTAÇÃO ATIVA - MOSTRAR CANDIDATOS E BOTÕES -->
                        
                        <p><strong>Candidatos:</strong> <?= count($candidatos) ?></p>

                        <!-- NOVO: Mostra badge se já for candidato -->
                        <?php if ($jaCandidato): ?>
                            <div class="ja-candidato-badge">
                                ✓ Você já está cadastrado como candidato
                            </div>
                        <?php endif; ?>

                        <div class="botaocaixavotacao">
                            <?php if ($status === 'antes_candidatura'): ?>
                                <a href="#" class="btn-disabled">Candidatar</a>
                                <a href="#" class="btn-disabled">Votar</a>
                                
                            <?php elseif ($status === 'periodo_candidatura'): ?>
                                <?php if ($jaCandidato): ?>
                                    <a href="#" class="btn-disabled">Já é candidato</a>
                                <?php else: ?>
                                    <a href="candidatura.php?idvotacao=<?= $idvotacao ?>" class="btn-active">Candidatar</a>
                                <?php endif; ?>
                                <a href="#" class="btn-disabled">Votar</a>
                                
                            <?php elseif ($status === 'periodo_votacao'): ?>
                                <a href="#" class="btn-disabled">Candidatar</a>
                                <?php if ($already): ?>
                                    <a href="#" class="btn-disabled">Você já votou</a>
                                <?php else: ?>
                                    <a href="area_eleicao.php?idvotacao=<?= $idvotacao ?>" class="btn-active">Votar</a>
                                <?php endif; ?>
                                
                            <?php else: ?>
                                <a href="#" class="btn-disabled">Candidatar</a>
                                <a href="#" class="btn-disabled">Votar</a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
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