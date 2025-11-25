<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $curso = trim($_POST['curso'] ?? '');
    $semestre = (int)($_POST['semestre'] ?? 0);
    $data_inicio = trim($_POST['data_inicio'] ?? '');
    $data_candidatura = trim($_POST['data_candidatura'] ?? '');
    $data_final = trim($_POST['data_final'] ?? '');

    if ($curso === '' || $semestre <= 0 || $data_inicio === '' || $data_candidatura === '' || $data_final === '') {
        $erro = "Preencha todos os campos.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO tb_votacoes 
        (curso, semestre, data_inicio, data_candidatura, data_final, idadmin) 
        VALUES (?, ?, ?, ?, ?, ?)");

        if ($stmt->execute([$curso, $semestre, $data_inicio, $data_candidatura, $data_final, $_SESSION['admin']['idadmin']])) {

        // 1. Pegar o id da votação criada
        $idvotacao = $pdo->lastInsertId();

        // 2. Atualizar todos os alunos do mesmo curso/semestre
        $sqlUpdate = "UPDATE tb_alunos 
                    SET idvotacao = ? 
                    WHERE curso = ? AND semestre = ?";
                    
        $stmtUpdate = $pdo->prepare($sqlUpdate);
        $stmtUpdate->execute([$idvotacao, $curso, $semestre]);

        header('Location: paineladministrativo.php');
        exit;
    } else {
        $erro = "Erro ao criar votação.";
    }

    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <title>Criar Votação</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Criar Votação</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="formmain">
        <div id="formbox">
            <h2>Nova Votação</h2>
            <?php if ($erro): ?><div class="erro"><span><?= htmlspecialchars($erro) ?></span></div><?php endif; ?>

            <form method="POST">
                <label>Curso</label>
                    <select name="curso">
                        <option value="0">Curso...</option>
                        <option value="Desenvolvimento de Software Multiplataforma">
                            Desenvolvimento de software multiplataforma</option>
                        <option value="Gestão de Produção Industrial">
                            Gestão de produção industrial</option>
                        <option value="Gestão Empresarial">
                            Gestão empresarial</option>
                    </select>

                <label>Semestre</label>
                    <select name="semestre">
                        <option value="0">Semestre...</option>
                        <option value="1">1º Semestre</option>
                        <option value="2">2º Semestre</option>
                        <option value="3">3º Semestre</option>
                        <option value="4">4º Semestre</option>
                        <option value="5">5º Semestre</option>
                        <option value="6">6º Semestre</option>
                    </select>
                <label>Data candidatura (início)</label>
                <input type="date" name="data_candidatura" placeholder="YYYY-MM-DD HH:MM:SS" required>

                <label>Data início (votação)</label>
                <input type="date" name="data_inicio" placeholder="YYYY-MM-DD HH:MM:SS" required>

                <label>Data final (votação)</label>
                <input type="date" name="data_final" placeholder="YYYY-MM-DD HH:MM:SS" required>

                <input type="submit" value="Criar votação">
            </form>
        </div>

        <div class="finalizarsessao">
            <a href="paineladministrativo.php"><img src="images/log-out.png" alt=""> <p>Voltar</p></a>
        </div>
    </main>

    <footer class="rodape">
        <img src="images/govsp.png" alt="" class="logosp">
        <img src="images/astros.png" alt="" class="logobottom">
    </footer>
</div>
</body>
</html>
