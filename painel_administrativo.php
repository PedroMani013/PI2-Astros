<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    header('Location: login_adm.php');
    exit;
}

// Filtrar votações - mostrar ativas e finalizadas há menos de 1 semana
$stmt = $pdo->prepare("
    SELECT * FROM tb_votacoes 
    WHERE ativa = 'sim' 
       OR (ativa = 'não' AND data_final >= DATE_SUB(NOW(), INTERVAL 7 DAY))
    ORDER BY data_inicio DESC
");
$stmt->execute();
$votacoes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <title>ASTROS - Painel Administrativo</title>
    <link rel="stylesheet" href="style.css">
    <style>
        .status-ativa { color: green; font-weight: bold; }
        .status-inativa { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" class="logotop">
        <h1>Sistema de Eleição para representante de sala</h1>
        <img src="images/cps.png" class="logotop">
    </header>

    <main class="index">
        <div class="boxpadrao">
        <h1 class="headpaineladm">PAINEL ADMINISTRATIVO</h1>

        <?php if (empty($votacoes)): ?>
            <p style="text-align:center; font-size:18px; margin: 20px 0;">
                Não há Eleições disponíveis no momento.
            </p>
        <?php else: ?>

            <?php foreach ($votacoes as $v): ?>
                <div class="votacaoadm">
                    <div class="infovotacaoadm">

                        <strong><?= htmlspecialchars($v['curso']) ?></strong>
                        <span>Semestre: <?= htmlspecialchars($v['semestre']) ?></span>
                        <span>Candidatura: <?= (new DateTime($v['data_candidatura']))->format('d/m/Y H:i') ?></span>
                        <span>Votação: <?= (new DateTime($v['data_inicio']))->format('d/m/Y H:i') ?> até <?= (new DateTime($v['data_final']))->format('d/m/Y H:i') ?></span>

                        <!-- Indicador de status -->
                        <span>Status:
                            <?php if ($v['ativa'] === 'sim'): ?>
                                <span class="status-ativa">Ativa</span>
                            <?php else: ?>
                                <span class="status-inativa">Finalizada</span>
                            <?php endif; ?>
                        </span>

                    </div>

                    <div class="botoesvotoadm">
                        <?php if ($v['ativa'] === 'sim'): ?>
                            <a href="administracao_candidatos.php?idvotacao=<?= $v['idvotacao'] ?>">Ver candidatos</a>
                            <a href="votos_adm.php?idvotacao=<?= $v['idvotacao'] ?>">Visualizar votos</a>
                            <button class="botaoremovot remover-votacao-btn"
                                    data-id="<?= $v['idvotacao'] ?>"
                                    data-curso="<?= htmlspecialchars($v['curso']) ?>"
                                    data-semestre="<?= htmlspecialchars($v['semestre']) ?>">
                                Remover Eleição
                            </button>
                        <?php else: ?>
                            <a href="votos_apurados.php?idvotacao=<?= $v['idvotacao'] ?>">Ver Resultados Finais</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <div class="criarvot">
            <a href="criar_eleicao.php" class="botoesvotoadm">
                <div class="criavot"><img src="images/addvotacao.png"><p>Criar nova Eleição</p></div>
            </a>
        </div>
        <div class="finalizarsessao">
            <a href="logout.php"><img src="images/log-out.png"> <p>Sair</p></a>
        </div>
        </div>
    </main>

    <footer class="rodape">
        <img src="images/govsp.png" class="logosp">
        <img src="images/astros.png" class="logobottom">
    </footer>
</div>

<!-- POPUP DE REMOÇÃO DE VOTAÇÃO -->
<div id="popupOverlayVot" class="overlay" style="display:none;">
    <div class="popup">
        <img src="images/alert-triangle.png" alt="Alerta" class="popup-icon">

        <h2>Confirmação de Remoção</h2>

        <p>
            Tem certeza que deseja remover a eleição<br>
            <strong><span id="nomeVotacao"></span></strong>?<br><br>
            ⚠️ Todos os candidatos e votos desta eleição serão apagados.<br>
            Esta ação não pode ser desfeita.
        </p>

        <button id="confirmar" style="margin-top:15px;">
            CONFIRMAR REMOÇÃO
        </button>
        <button id="cancelarRemocaoVot" style="margin-top:10px; background-color:#6c757d;">
            CANCELAR
        </button>
    </div>
</div>

<script>
const overlayVot = document.getElementById("popupOverlayVot");
const nomeVot = document.getElementById("nomeVotacao");
const confirmarVotBtn = document.getElementById("confirmar");
const cancelarVotBtn = document.getElementById("cancelarRemocaoVot");

let idSelecionadoVot = null;

// Adiciona evento a todos os botões de remover votação
document.querySelectorAll(".remover-votacao-btn").forEach(btn => {
    btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const curso = this.getAttribute("data-curso");
        const semestre = this.getAttribute("data-semestre");

        nomeVot.textContent = `${curso} - ${semestre}º semestre`;
        idSelecionadoVot = id;

        overlayVot.style.display = "flex";
    });
});

// Fechar popup clicando no fundo
overlayVot.addEventListener("click", function(e) {
    if (e.target === overlayVot) {
        overlayVot.style.display = "none";
    }
});

// Botão cancelar
cancelarVotBtn.addEventListener("click", function() {
    overlayVot.style.display = "none";
});

// Confirmar remoção
confirmarVotBtn.addEventListener("click", function() {
    if (idSelecionadoVot) {
        window.location.href = "remocao_vot.php?idvotacao=" + idSelecionadoVot;
    }
});
</script>

</body>
</html>