<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

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
    <style>
        .status-ativa { color: green; font-weight: bold; }
        .status-inativa { color: red; font-weight: bold; }
    </style>
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" class="logotop">
        <h1>Votação para representante de sala</h1>
        <img src="images/cps.png" class="logotop">
    </header>

    <main class="index">
        <div class="boxpadrao">
        <h1 class="headpaineladm">PAINEL ADMINISTRATIVO</h1>

        <?php if (empty($votacoes)): ?>
            <p style="text-align:center; font-size:18px; margin: 20px 0;">
                Não há votações disponíveis no momento.
            </p>
        <?php else: ?>

            <?php foreach ($votacoes as $v): ?>
                <div class="votacaoadm">
                    <div class="infovotacaoadm">

                        <strong><?= htmlspecialchars($v['curso']) ?></strong>
                        <span>Semestre: <?= htmlspecialchars($v['semestre']) ?></span>
                        <span>Candidatura: <?= (new DateTime($v['data_candidatura']))->format('d/m/Y H:i') ?></span>
                        <span>Votação: <?= (new DateTime($v['data_inicio']))->format('d/m/Y H:i') ?> até <?= (new DateTime($v['data_final']))->format('d/m/Y H:i') ?></span>

                        <span>Status:
                            <span class="status-ativa">Ativa</span>
                        </span>

                    </div>

                    <div class="botoesvotoadm">
                        <a href="administracaocandidatos.php?idvotacao=<?= $v['idvotacao'] ?>">Ver candidatos</a>
                        <a href="votosadm.php?idvotacao=<?= $v['idvotacao'] ?>">Apurar votos</a>
                        <button class="botaoremovot remover-votacao-btn"
                                data-id="<?= $v['idvotacao'] ?>"
                                data-curso="<?= htmlspecialchars($v['curso']) ?>"
                                data-semestre="<?= htmlspecialchars($v['semestre']) ?>">
                            Remover Votação
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>

        <?php endif; ?>

        <div class="criarvot">
            <a href="criarvotacao.php" class="botoesvotoadm">
                <div class="criavot"><img src="images/addvotacao.png"><p>Criar nova votação</p></div>
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
            Tem certeza que deseja remover a votação do curso 
            <strong><span id="nomeVotacao"></span></strong>?<br>
            Todos os candidatos e vínculos com alunos serão apagados.
        </p>

        <button id="confirmarRemocaoVot" style="margin-top:15px;">
            CONFIRMAR REMOÇÃO
        </button>
    </div>
</div>

<script>
const overlayVot = document.getElementById("popupOverlayVot");
const nomeVot = document.getElementById("nomeVotacao");
const confirmarVotBtn = document.getElementById("confirmarRemocaoVot");

let idSelecionadoVot = null;

document.querySelectorAll(".remover-votacao-btn").forEach(btn => {

    btn.addEventListener("click", () => {

        const id = btn.getAttribute("data-id");
        const curso = btn.getAttribute("data-curso");
        const semestre = btn.getAttribute("data-semestre");

        nomeVot.textContent = `${curso} - ${semestre}º semestre`;

        idSelecionadoVot = id;

        overlayVot.style.display = "flex";
    });

});

// Fechar popup clicando no fundo
overlayVot.addEventListener("click", (e) => {
    if (e.target === overlayVot)
        overlayVot.style.display = "none";
});

// Confirmar remoção
confirmarVotBtn.addEventListener("click", () => {
    if (idSelecionadoVot) {
        window.location.href = "remocaovot.php?idvotacao=" + idSelecionadoVot;
    }
});
</script>

</body>
</html>
