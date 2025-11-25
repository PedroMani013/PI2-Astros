<?php
session_start();

// Verificar se o usuário está logado
if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

$nome_aluno = $_SESSION['aluno']['nome'];
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
            <div class="boxpadrao">
                <h1>VOTAÇÕES EM ANDAMENTO</h1>
                <div id="caixavotacao">
                    <div class="headcaixavotacao">
                        <h2>Votação para representante de sala e suplente</h2>
                    </div>
                    <div class="maincaixavotacao">
                        <p>Curso: <?php?></p>
                        <p>Semestre: <?php?></p>
                        <p>Data para candidatura: <?php?></p>
                        <p>Data de inicio da votação: <?php?></p>
                        <p>Data de encerramento: <?php?></p>
                        <p>Candidatos: <?php?></p>
                        <div class="botaocaixavotacao">
                            <a href="candidatura.php">Candidatar</a>
                            <a href="areaeleicao.php">Votar</a>
                        </div>
                    </div>
                </div>
                <div class="finalizarsessao">
                    <a href="logout.php">
                        <img src="images/log-out.png" alt="">
                        <p>Finalizar Sessão</p>
                    </a>
                </div>
            </div>
        </main>
        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
</body>

</html>