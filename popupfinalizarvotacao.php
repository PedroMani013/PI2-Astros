<?php
session_start();
require_once 'conexao.php';

// Verificar se é administrador
if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

// Verificar se idvotacao foi passado
if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID de votação inválido.");
}

$idvotacao = (int)$_GET['idvotacao'];

// Buscar informações da votação
$stmtVot = $pdo->prepare("SELECT curso, semestre, ativa FROM tb_votacoes WHERE idvotacao = ?");
$stmtVot->execute([$idvotacao]);
$votacao = $stmtVot->fetch(PDO::FETCH_ASSOC);

if (!$votacao) {
    die("Votação não encontrada.");
}

// Buscar os 2 candidatos mais votados (EXCLUINDO o candidato especial ID=0)
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra, c.email,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ? AND c.nomealuno != 'VOTO NULO'
    ORDER BY total_votos DESC, c.nomealuno ASC
    LIMIT 2
");
$sql->execute([$idvotacao]);
$vencedores = $sql->fetchAll(PDO::FETCH_ASSOC);

// Separar representante e suplente
$representante = $vencedores[0] ?? null;
$suplente = $vencedores[1] ?? null;

// Total de votos válidos
$sqlTotal = $pdo->prepare("
    SELECT COUNT(*) as total FROM tb_votos v
    WHERE v.idcandidato IN (
        SELECT idcandidato FROM tb_candidatos 
        WHERE idvotacao = ? AND idcandidato != 0
    )
");
$sqlTotal->execute([$idvotacao]);
$totalVotos = (int)$sqlTotal->fetch()['total'];

// Se veio do POST, processar finalização
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$representante) {
        die("Não há candidatos para finalizar a votação.");
    }
    
    try {
        $stmt = $pdo->prepare("
            UPDATE tb_votacoes 
            SET idcandidato_representante = ?,
                idcandidato_suplente = ?,
                ativa = 'não'
            WHERE idvotacao = ?
        ");
        
        $stmt->execute([
            $representante['idcandidato'],
            $suplente ? $suplente['idcandidato'] : null,
            $idvotacao
        ]);
        
        // Redirecionar após sucesso (o HTML mostrará a mensagem)
        $finalizado = true;
        
    } catch (PDOException $e) {
        $erro = "Erro ao finalizar votação: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Eleição Encerrada</title>
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Sistema de Eleição para Representante de Sala</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>

        <main class="index">
            <?php if ($votacao['ativa'] === 'não'): ?>
                <div class="greenpopup">
                    <img src="images/monitor.png" alt="Sucesso">
                    <h2>Eleição já foi finalizada</h2>
                    <h3>Os votos foram computados com sucesso</h3>
                    <p><a href="paineladministrativo.php">Clique Aqui para voltar para o painel administrativo</a></p>
                    <span>.</span>
                </div>
                <?php exit; ?>
            <?php endif; ?>
            <?php if (isset($finalizado) && $finalizado): ?>
                <!-- Popup de sucesso (já existe no seu sistema) -->
                <div class="greenpopup">
                    <img src="images/monitor.png" alt="Sucesso">
                    <h2>ELEIÇÃO FINALIZADA!</h2>
                    <h3>Os votos foram computados com sucesso</h3>
                    <p><a href="paineladministrativo.php">Clique Aqui para voltar para o painel administrativo</a></p>
                    <span>.</span>
                </div>

            <?php elseif (isset($erro)): ?>
                <div class="popup-finalizar">
                    <div class="popup-header-finalizar">
                        <h2>❌ Erro ao Finalizar</h2>
                    </div>
                    <div class="popup-content-finalizar">
                        <div class="erro">
                            <span><?= htmlspecialchars($erro) ?></span>
                        </div>
                        <div class="botoes-finalizar">
                            <a href="votosapurados.php?idvotacao=<?= $idvotacao ?>" class="btn-finalizar-cancelar">
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>

            <?php elseif (!$representante): ?>
                <div class="popup-finalizar">
                    <div class="popup-header-finalizar">
                        <h2>⚠️ Sem Candidatos</h2>
                    </div>
                    <div class="popup-content-finalizar">
                        <div class="sem-candidatos">
                            <img src="images/alert-triangle.png" alt="Alerta">
                            <h3>Não há candidatos votados nesta eleição.</h3>
                            <p>Não é possível finalizar a eleição sem vencedores.</p>
                        </div>
                        <div class="botoes-finalizar">
                            <a href="votosapurados.php?idvotacao=<?= $idvotacao ?>" class="btn-finalizar-cancelar">
                                Voltar
                            </a>
                        </div>
                    </div>
                </div>

            <?php else: ?>
                <div class="popup-finalizar">
                    <div class="popup-header-finalizar">
                        <h2>⚠️ Confirmar Finalização</h2>
                        <p>Revise os dados antes de finalizar</p>
                    </div>

                    <div class="popup-content-finalizar">
                        <div class="votacao-info-box">
                            <h3>📋 Informações da Votação</h3>
                            <div class="info-row">
                                <strong>Curso:</strong>
                                <span><?= htmlspecialchars($votacao['curso']) ?></span>
                            </div>
                            <div class="info-row">
                                <strong>Semestre:</strong>
                                <span><?= htmlspecialchars($votacao['semestre']) ?>º</span>
                            </div>
                            <div class="info-row">
                                <strong>Total de votos:</strong>
                                <span><?= $totalVotos ?></span>
                            </div>
                        </div>

                        <!-- Representante -->
                        <div class="vencedor-card">
                            <h4>🏆 Representante Eleito</h4>
                            <div class="nome"><?= htmlspecialchars($representante['nomealuno']) ?></div>
                            <div class="detalhes">
                                RA: <?= htmlspecialchars($representante['ra']) ?> | 
                                Votos: <?= $representante['total_votos'] ?>
                            </div>
                        </div>

                        <!-- Suplente -->
                        <?php if ($suplente): ?>
                        <div class="vencedor-card">
                            <h4>🥈 Suplente Eleito</h4>
                            <div class="nome"><?= htmlspecialchars($suplente['nomealuno']) ?></div>
                            <div class="detalhes">
                                RA: <?= htmlspecialchars($suplente['ra']) ?> | 
                                Votos: <?= $suplente['total_votos'] ?>
                            </div>
                        </div>
                        <?php else: ?>
                        <div class="vencedor-card" style="opacity: 0.6;">
                            <h4>🥈 Suplente</h4>
                            <div class="nome">Não há segundo colocado</div>
                        </div>
                        <?php endif; ?>

                        <div class="aviso-importante">
                            <strong>ATENÇÃO!</strong>
                            <p>Esta ação é <strong>IRREVERSÍVEL</strong>.</p>
                            <p>A eleição será marcada como finalizada e os resultados serão publicados aos alunos.</p>
                            <p>A eleição ficará visível por mais 1 semana após o encerramento.</p>
                        </div>

                        <form method="POST">
                            <div class="botoes-finalizar">
                                <a href="votosapurados.php?idvotacao=<?= $idvotacao ?>" class="btn-finalizar-cancelar">
                                    Cancelar
                                </a>
                                <button type="submit" class="btn-finalizar-confirmar">
                                    ✓ Confirmar Finalização
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </main>

        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
</body>
</html>