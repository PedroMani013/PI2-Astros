<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];

if (!isset($_GET['idvotacao'])) {
    die("Votação inválida.");
}
$idvotacao = (int)$_GET['idvotacao'];

// Busca votação
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();

if (!$vot) {
    die("Votação não encontrada.");
}

// Verifica período de candidatura
$agora = new DateTime();
$dataCandidatura = new DateTime($vot['data_candidatura']);
$dataInicio = new DateTime($vot['data_inicio']);

if ($agora < $dataCandidatura) {
    die("O período de candidatura ainda não iniciou.");
}

if ($agora >= $dataInicio) {
    die("O período de candidatura já encerrou.");
}

// Busca dados do aluno
$stmt = $pdo->prepare("SELECT nome, email, ra, curso, semestre FROM tb_alunos WHERE idaluno = ?");
$stmt->execute([$idaluno]);
$aluno = $stmt->fetch();

if (!$aluno) {
    die("Aluno não encontrado.");
}

// Verifica se o aluno é do mesmo curso/semestre da votação
if ($aluno['curso'] !== $vot['curso'] || (int)$aluno['semestre'] !== (int)$vot['semestre']) {
    die("Você não pode se candidatar nesta votação. Curso ou semestre incompatível.");
}

// Verifica se já é candidato
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_candidatos WHERE ra = ? AND idvotacao = ?");
$stmt->execute([$aluno['ra'], $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já está cadastrado como candidato nesta votação.");
}

// Pega mensagem de erro se houver
$erro = $_SESSION['erro_candidatura'] ?? '';
unset($_SESSION['erro_candidatura']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Cadastro de Candidatura</title>
    <link rel="shortcut icon" href="images/astros.png">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div id="tudo">
    <header class="topo">
        <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
        <h1>Inscrição de Candidatura</h1>
        <img src="images/cps.png" alt="Logo Cps" class="logotop">
    </header>

    <main class="formmain">
        <div id="formbox">
            <h2>Cadastro de Candidato</h2>
            
            <div style="background-color: #e8f4f8; padding: 15px; border-radius: 10px; margin-bottom: 20px;">
                <p style="margin: 5px 0; font-size: 1.1rem;"><strong>Votação:</strong> <?= htmlspecialchars($vot['curso']) ?></p>
                <p style="margin: 5px 0; font-size: 1.1rem;"><strong>Semestre:</strong> <?= htmlspecialchars($vot['semestre']) ?>º</p>
                <p style="margin: 5px 0; font-size: 1.1rem;"><strong>Período de candidatura até:</strong> <?= (new DateTime($vot['data_inicio']))->format('d/m/Y') ?></p>
            </div>

            <?php if ($erro): ?>
                <div class="erro">
                    <span><?= htmlspecialchars($erro) ?></span>
                </div>
            <?php endif; ?>

            <form method="POST" action="processa_candidatura.php" enctype="multipart/form-data" id="formCandidatura">
                <input type="hidden" name="idvotacao" value="<?= $idvotacao ?>">

                <label>Nome Completo</label>
                <input type="text" name="nomealuno" value="<?= htmlspecialchars($aluno['nome']) ?>" required readonly style="background-color: #f0f0f0;">

                <label>Email Institucional</label>
                <input type="email" name="email" value="<?= htmlspecialchars($aluno['email']) ?>" required readonly style="background-color: #f0f0f0;">

                <label>RA (Registro Acadêmico)</label>
                <input type="text" name="ra" value="<?= htmlspecialchars($aluno['ra']) ?>" required readonly style="background-color: #f0f0f0;">

                <label>Foto do Candidato</label>
                <p style="font-size: 0.9rem; color: #666; margin: 5px 0 10px;">
                    📸 Envie uma foto sua (JPG ou PNG, máximo 5MB)
                </p>
                
                <label class="btn-upload" for="foto">
                    📁 Escolher foto
                </label>
                <input type="file" id="foto" name="foto" accept="image/jpeg,image/jpg,image/png" required>
                
                <p id="nomeArquivo" style="font-size: 0.9rem; color: #333; margin-top: 10px; font-style: italic;">
                    Nenhum arquivo selecionado
                </p>

                <input type="submit" value="Enviar Candidatura">
            </form>
        </div>

        <div class="finalizarsessao">
            <a href="votacoesaluno.php">
                <img src="images/log-out.png" alt=""> 
                <p>Voltar</p>
            </a>
        </div>
    </main>

    <footer class="rodape">
        <img src="images/govsp.png" alt="" class="logosp">
        <img src="images/astros.png" alt="" class="logobottom">
    </footer>
</div>

<script>
// Mostra o nome do arquivo selecionado
document.getElementById('foto').addEventListener('change', function(e) {
    const nomeArquivo = document.getElementById('nomeArquivo');
    if (this.files && this.files[0]) {
        nomeArquivo.textContent = '✓ ' + this.files[0].name;
        nomeArquivo.style.color = '#147C0E';
        nomeArquivo.style.fontWeight = 'bold';
    } else {
        nomeArquivo.textContent = 'Nenhum arquivo selecionado';
        nomeArquivo.style.color = '#333';
        nomeArquivo.style.fontWeight = 'normal';
    }
});

// Validação antes de enviar
document.getElementById('formCandidatura').addEventListener('submit', function(e) {
    const foto = document.getElementById('foto');
    
    if (!foto.files || !foto.files[0]) {
        e.preventDefault();
        alert('Por favor, selecione uma foto antes de enviar.');
        return false;
    }
    
    // Verifica tamanho
    if (foto.files[0].size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('A foto não pode ter mais de 5MB.');
        return false;
    }
    
    // Confirma envio
    return confirm('Confirma o envio da sua candidatura? Após confirmado, não será possível editar.');
});
</script>

</body>
</html>