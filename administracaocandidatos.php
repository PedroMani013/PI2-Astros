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

// Busca informações da votação
$stmtVot = $pdo->prepare("SELECT curso, semestre FROM tb_votacoes WHERE idvotacao = ?");
$stmtVot->execute([$idvotacao]);
$votacao = $stmtVot->fetch(PDO::FETCH_ASSOC);

if (!$votacao) {
    die("Votação não encontrada.");
}

// Busca candidatos (EXCLUINDO o candidato especial ID=0)
$stmt = $pdo->prepare("
    SELECT * FROM tb_candidatos 
    WHERE idvotacao = ? AND nomealuno != 'VOTO NULO'
    ORDER BY nomealuno ASC
");
$stmt->execute([$idvotacao]);
$candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Administração de Candidatos</title>
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
</head>
<body>
<div id="tudo">
<header class="topo">
    <img src="images/fatec.png" class="logotop">
    <h1>Sistema de Eleição para Representante de Sala</h1>
    <img src="images/cps.png" class="logotop">
</header>

<main class="index">
    <h1>Administração de Candidatos</h1>
    
    <div style="background-color: #e8f4f8; padding: 15px; border-radius: 10px; margin: 20px auto; width: 60%; text-align: center;">
        <p style="margin: 5px 0; font-size: 1.2rem;">
            <strong>Votação:</strong> <?= htmlspecialchars($votacao['curso']) ?> - <?= htmlspecialchars($votacao['semestre']) ?>º Semestre
        </p>
        <p style="margin: 5px 0; font-size: 1.1rem;">
            <strong>Total de candidatos:</strong> <?= count($candidatos) ?>
        </p>
    </div>

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
            <img src="<?= $foto ?>" alt="Foto do candidato">

            <div class="candidatotext">
                <p>CANDIDATO(A):</p>
                <p><strong><?= htmlspecialchars($c['nomealuno']) ?></strong></p>

                <div class="botaoremov">
                    <button class="btn-remover-candidato"
                            data-id="<?= $c['idcandidato'] ?>"
                            data-nome="<?= htmlspecialchars($c['nomealuno']) ?>"
                            data-idvotacao="<?= $idvotacao ?>">
                        Remover Candidatura
                    </button>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <?php endif; ?>

    <div class="finalizarsessao">
        <a href="paineladministrativo.php"><img src="images/log-out.png"> <p>Voltar</p></a>
    </div>

</main>

<footer class="rodape">
    <img src="images/govsp.png" class="logosp">
    <img src="images/astros.png" class="logobottom">
</footer>
</div>

<!-- POPUP DE CONFIRMAÇÃO DE REMOÇÃO -->
<div id="popupOverlayCand" class="overlay" style="display:none;">
    <div class="popup">
        <img src="images/alert-triangle.png" alt="Alerta" class="popup-icon">
        <h2>Confirmação de Remoção</h2>
        <p>
            Tem certeza que deseja remover o candidato<br>
            <strong><span id="nomeCandidato"></span></strong>?<br><br>
            Esta ação não pode ser desfeita.
        </p>
        <button id="confirmarRemocaoCand" style="margin-top:15px;">
            CONFIRMAR REMOÇÃO
        </button>
        <button id="cancelarRemocao" style="margin-top:10px; background-color:#6c757d;">
            CANCELAR
        </button>
    </div>
</div>

<script>
const overlayCand = document.getElementById("popupOverlayCand");
const nomeCandidato = document.getElementById("nomeCandidato");
const confirmarBtn = document.getElementById("confirmarRemocaoCand");
const cancelarBtn = document.getElementById("cancelarRemocao");

let idCandidatoSelecionado = null;
let idVotacaoSelecionada = null;

// Adiciona evento a todos os botões de remover
document.querySelectorAll(".btn-remover-candidato").forEach(btn => {
    btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const nome = this.getAttribute("data-nome");
        const idvotacao = this.getAttribute("data-idvotacao");

        nomeCandidato.textContent = nome;
        idCandidatoSelecionado = id;
        idVotacaoSelecionada = idvotacao;

        overlayCand.style.display = "flex";
    });
});

// Fechar popup clicando no fundo
overlayCand.addEventListener("click", function(e) {
    if (e.target === overlayCand) {
        overlayCand.style.display = "none";
    }
});

// Botão cancelar
cancelarBtn.addEventListener("click", function() {
    overlayCand.style.display = "none";
});

// Confirmar remoção
confirmarBtn.addEventListener("click", function() {
    if (idCandidatoSelecionado && idVotacaoSelecionada) {
        window.location.href = `remover_candidato.php?id=${idCandidatoSelecionado}&idvotacao=${idVotacaoSelecionada}`;
    }
});
</script>

</body>
</html>