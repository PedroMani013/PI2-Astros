<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

// Captura mensagens de erro/sucesso
$erros = $_SESSION['erros_votacao'] ?? [];
unset($_SESSION['erros_votacao']);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <title>ASTROS - Criar Votação</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">

<header class="topo">
    <img src="images/fatec.png" class="logotop">
    <h1>Sistema de Eleição para Representante de Sala</h1>
    <img src="images/cps.png" class="logotop">
</header>

<main class="formmain">
    <div id="formbox">
        <h2>Nova Votação</h2>
        
        <?php if (!empty($erros)): ?>
            <?php foreach ($erros as $erro): ?>
                <div class="erro">
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="POST" action="processa_votacao.php">
            
            <label>Curso</label>
            <select name="curso" required>
                <option value="">Selecione o curso...</option>
                <option value="Desenvolvimento de Software Multiplataforma">Desenvolvimento de software multiplataforma</option>
                <option value="Gestão de Produção Industrial">Gestão de produção industrial</option>
                <option value="Gestão Empresarial">Gestão empresarial</option>
            </select>

            <label>Semestre</label>
            <select name="semestre" required>
                <option value="0">Selecione o semestre...</option>
                <option value="1">1º Semestre</option>
                <option value="2">2º Semestre</option>
                <option value="3">3º Semestre</option>
                <option value="4">4º Semestre</option>
                <option value="5">5º Semestre</option>
                <option value="6">6º Semestre</option>
            </select>

            <label>Data candidatura (início)</label>
            <input type="date" name="data_candidatura" required>

            <label>Data início (votação)</label>
            <input type="date" name="data_inicio" required>

            <label>Data final (votação)</label>
            <input type="date" name="data_final" required>

            <input type="submit" value="Criar votação">
        </form>
    </div>

    <div class="finalizarsessao">
        <a href="paineladministrativo.php"><img src="images/log-out.png"> <p>Voltar</p></a>
    </div>
</main>

<footer class="rodape">
    <img src="images/govsp.png" class="logosp">
    <img src="images/astros.png" class="logobottom">
</footer>

</div>
</body>
</html>