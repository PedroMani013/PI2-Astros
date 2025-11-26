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

// Buscar candidatos da votação
$stmt = $pdo->prepare("SELECT * FROM tb_candidatos WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Administração de Candidatos</title>
    <link rel="shortcut icon" href="images/astros.png">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Administração de Candidatos</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>

        <main class="index">

            <?php if (empty($candidatos)): ?>
                <h2 style="text-align:center; margin-top:20px;">
                    Não há candidatos cadastrados nesta votação.
                </h2>
            <?php else: ?>

                <div class="boxcandidatos">

                    <?php foreach ($candidatos as $c): ?>
                        <div class="candidato">
                            
                            <img src="data:image/jpeg;base64,<?= base64_encode($c['imagem']) ?>" alt="Foto do candidato">

                            <div class="candidatotext">
                                <p>CANDIDATO(A):</p>
                                <p><?= htmlspecialchars($c['nomealuno']) ?></p>
                                <p><strong>RA:</strong> <?= htmlspecialchars($c['ra']) ?></p>
                                <p><strong>Email:</strong> <?= htmlspecialchars($c['email']) ?></p>

                                <div class="botaoremov">
                                    <button data-id="<?= $c['idcandidato'] ?>" data-nome="<?= htmlspecialchars($c['nomealuno']) ?>">
                                        Remover Candidatura
                                    </button>
                                </div>
                            </div>

                        </div>
                    <?php endforeach; ?>

                </div>

            <?php endif; ?>

            <div class="finalizarsessao">
                <a href="paineladministrativo.php">
                    <img src="images/log-out.png" alt="">
                    <p>Voltar Para Votações</p>
                </a>
            </div>

        </main>

        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>

    <!-- POPUP -->
    <div id="popupOverlay" class="overlay">
        <div class="popup">
            <img src="images/alert-triangle.png" alt="Alerta" class="popup-icon">

            <h2>Confirmação de Remoção</h2>

            <p>
                Ao remover o candidato, ele não poderá se candidatar novamente nesta votação.<br>
                Tem certeza que deseja remover <strong><span id="nomeCandidato"></span></strong>?
            </p>

            <button id="confirmarVoto">CONFIRMAR REMOÇÃO</button>
        </div>
    </div>

    <script>
        const overlay = document.getElementById("popupOverlay");
        const nomeCandidato = document.getElementById("nomeCandidato");
        const confirmarBtn = document.getElementById("confirmarVoto");

        let idSelecionado = null;

        // Seleciona os botões
        const botoesRemover = document.querySelectorAll(".botaoremov button");

        botoesRemover.forEach(botao => {
            botao.addEventListener("click", () => {

                const nome = botao.getAttribute("data-nome");
                const id = botao.getAttribute("data-id");

                nomeCandidato.textContent = nome;
                idSelecionado = id;

                overlay.style.display = "flex";
            });
        });

        // Fechar popup clicando no fundo
        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) overlay.style.display = "none";
        });

        // Confirmar remoção
        confirmarBtn.addEventListener("click", () => {
            if (idSelecionado) {
                window.location.href = "remover_candidato.php?id=" + idSelecionado + "&idvotacao=<?= $idvotacao ?>";
            }
        });
    </script>
</body>

</html>
