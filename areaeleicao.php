<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID de votação inválido.");
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];
$idvotacao = (int)$_GET['idvotacao'];

// Busca votação
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();

if (!$vot) {
    die("Votação não encontrada.");
}

// Verifica período de votação
$agora = new DateTime();
$dataInicio = new DateTime($vot['data_inicio']);
$dataFinal = new DateTime($vot['data_final']);

if ($agora < $dataInicio || $agora > $dataFinal) {
    die("Fora do período de votação.");
}

// Verifica se já votou
$stmt = $pdo->prepare("
    SELECT COUNT(*) as total FROM tb_votos v
    JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE v.idaluno = ? AND c.idvotacao = ?
");
$stmt->execute([$idaluno, $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já votou nesta votação.");
}

// Busca candidatos
$stmt = $pdo->prepare("SELECT idcandidato, nomealuno, ra, imagem FROM tb_candidatos WHERE idvotacao = ? ORDER BY nomealuno ASC");
$stmt->execute([$idvotacao]);
$candidatos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Sistema De Votação</title>
    <link rel="shortcut icon" href="images/astros.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Votação Para Representante de Sala</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>

        <main class="index">
            <h1>Área De Eleição</h1>

            <?php if (empty($candidatos)): ?>
                <p style="text-align:center; font-size:20px; margin:20px;">
                    Não há candidatos cadastrados nesta votação.
                </p>
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
                                <p><?= htmlspecialchars($c['nomealuno']) ?></p>
                                <div class="botaovot">
                                    <button class="btn-votar" 
                                            data-id="<?= $c['idcandidato'] ?>"
                                            data-nome="<?= htmlspecialchars($c['nomealuno']) ?>">
                                        Votar
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

            <?php endif; ?>

            <div class="finalizarsessao">
                <a href="votacoesaluno.php">
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

    <!-- Popup de confirmação -->
    <div id="popupOverlay" class="overlay">
        <div class="popup">
            <img src="images/alert-triangle.png" alt="Alerta" class="popup-icon">
            <h2>Confirmação de voto</h2>
            <p>
                Você só tem direito a <strong>UM voto</strong>.<br>
                Após a confirmação, não será possível removê-lo.<br>
                Tem certeza que deseja votar em <strong><span id="nomeCandidato"></span></strong>?
            </p>
            <button id="confirmarVoto">CONFIRMAR VOTO</button>
        </div>
    </div>

    <script>
        const overlay = document.getElementById("popupOverlay");
        const nomeCandidato = document.getElementById("nomeCandidato");
        const confirmarBtn = document.getElementById("confirmarVoto");

        const botoesVotar = document.querySelectorAll(".btn-votar");

        botoesVotar.forEach(botao => {
            botao.addEventListener("click", (e) => {
                const nome = botao.getAttribute("data-nome");
                const idcandidato = botao.getAttribute("data-id");
                
                nomeCandidato.textContent = nome;
                confirmarBtn.setAttribute("data-idcandidato", idcandidato);
                
                overlay.style.display = "flex";
            });
        });

        overlay.addEventListener("click", (e) => {
            if (e.target === overlay) overlay.style.display = "none";
        });

        confirmarBtn.addEventListener("click", () => {
            const idcandidato = confirmarBtn.getAttribute("data-idcandidato");
            
            // Cria um form e envia
            const form = document.createElement("form");
            form.method = "POST";
            form.action = "processa_voto.php";
            
            const inputVotacao = document.createElement("input");
            inputVotacao.type = "hidden";
            inputVotacao.name = "idvotacao";
            inputVotacao.value = "<?= $idvotacao ?>";
            
            const inputCandidato = document.createElement("input");
            inputCandidato.type = "hidden";
            inputCandidato.name = "idcandidato";
            inputCandidato.value = idcandidato;
            
            form.appendChild(inputVotacao);
            form.appendChild(inputCandidato);
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</body>
</html>