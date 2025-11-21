<?php
session_start();
require_once 'conexao.php';
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
        <main class="formmain">
            <?php
                if (isset($_SESSION['erro_votacao'])) {
                    echo "<div class='erro'><span>".$_SESSION['erro_votacao']."</span></div>";
                    unset($_SESSION['erro_votacao']);
                }

                if (isset($_SESSION['sucesso_votacao'])) {
                    echo "<div class='sucesso'><span>".$_SESSION['sucesso_votacao']."</span></div>";
                    unset($_SESSION['sucesso_votacao']);
                }
            ?>
            <div id="formbox">
                <h2>Criar Votação</h2>
                <form action="processa_votacao.php" method="post">
                    <label for="curso">Selecione o curso que a votação será realizada</label><br>
                    <select name="curso">
                        <option value="0">Curso...</option>
                        <option value="Desenvolvimento de software multiplataforma">
                            Desenvolvimento de software multiplataforma</option>
                        <option value="Gestão de produção industrial">
                            Gestão De Produção Industrial</option>
                        <option value="Gestão empresarial">
                            Gestão Empresarial</option>
                    </select><br>
                    <label for="semestre">Selecione o semestre que a votação será realizada</label><br>
                    <select name="semestre">
                        <option value="0">Semestre...</option>
                        <option value="1">1º Semestre</option>
                        <option value="2">2º Semestre</option>
                        <option value="3">3º Semestre</option>
                        <option value="4">4º Semestre</option>
                        <option value="5">5º Semestre</option>
                        <option value="6">6º Semestre</option>
                    </select><br>
                    <label for="datacandidatura">Informe a data para candidatura:</label><br>
                    <input type="date" name="datacand" id=""><br>
                    <label for="datainicio">Informe a data de inicio da votação:</label><br>
                    <input type="date" name="datainicio" id=""><br>
                    <label for="datafim">Informe a data de encerramento da votação:</label><br>
                    <input type="date" name="datafim" id=""><br>
                    <input type="submit" class="criarvot" value="Enviar Formulário"><br>
                </form>
                </div>
                <div class="finalizarsessao">
                    <a href="paineladministrativo.php">
                        <img src="images/log-out.png" alt="">
                        <p>Voltar Para Painel Administrativo</p>
                    </a>
                </div>
        </main>
        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
</body>

</html>