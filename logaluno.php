<?php

session_start();

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

<?php
require_once 'conexao.php';
?>

<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Votação Para Representante de Sala</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>
        <main class="index">
            <div id="login">
                <?php if (isset($_SESSION['erro_login'])): ?>
                    <div class="erro">
                        <?php 
                        echo $_SESSION['erro_login'];
                        unset($_SESSION['erro_login']); // Limpa a mensagem após exibir
                        ?>
                    </div>
                <?php endif; ?>
                <div class="loginhead">
                    <img src="images/user_login.png" alt="user">
                    <h2>LOGIN</h2>
                    <h3>Portal do aluno</h3>

                </div>
                <form action="processalogaluno.php" method="post" class="loginbody">
                    <input type="email" name="email" id="email" placeholder="Login (Email)" required>
                    <input type="password" name="password" id="" placeholder="Senha" required>
                    <input type="submit" value="Entrar">
                </form>
            </div>
        </main>
        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
</body>

</html>