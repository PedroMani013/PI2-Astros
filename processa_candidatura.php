<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

// Verifica se o aluno está logado
if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

// Verifica se é POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: votacoesaluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];
$idvotacao = (int)($_POST['idvotacao'] ?? 0);
$nome = trim($_POST['nomealuno'] ?? '');
$email = trim($_POST['email'] ?? '');
$ra = trim($_POST['ra'] ?? '');

// Função para redirecionar com erro
function redirecionarComErro($idvotacao, $mensagem) {
    $_SESSION['erro_candidatura'] = $mensagem;
    header("Location: candidatura.php?idvotacao=$idvotacao");
    exit;
}

// Validação básica
if (empty($idvotacao)) {
    die("ID de votação inválido.");
}

if (empty($nome) || empty($email) || empty($ra)) {
    redirecionarComErro($idvotacao, "Todos os campos são obrigatórios.");
}

// Busca votação
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();

if (!$vot) {
    redirecionarComErro($idvotacao, "Votação não encontrada.");
}

// Verifica período de candidatura
$agora = new DateTime();
$dataCandidatura = new DateTime($vot['data_candidatura']);
$dataInicio = new DateTime($vot['data_inicio']);

if ($agora < $dataCandidatura) {
    redirecionarComErro($idvotacao, "O período de candidatura ainda não iniciou.");
}

if ($agora >= $dataInicio) {
    redirecionarComErro($idvotacao, "O período de candidatura já encerrou.");
}

// Busca dados do aluno
$stmt = $pdo->prepare("SELECT curso, semestre FROM tb_alunos WHERE idaluno = ?");
$stmt->execute([$idaluno]);
$aluno = $stmt->fetch();

if (!$aluno) {
    redirecionarComErro($idvotacao, "Aluno não encontrado.");
}

// Verifica se o aluno é do mesmo curso/semestre da votação
if ($aluno['curso'] !== $vot['curso'] || (int)$aluno['semestre'] !== (int)$vot['semestre']) {
    redirecionarComErro($idvotacao, "Você não pode se candidatar nesta votação. Curso ou semestre incompatível.");
}

// Verifica se já é candidato
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_candidatos WHERE ra = ? AND idvotacao = ?");
$stmt->execute([$ra, $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    redirecionarComErro($idvotacao, "Você já está cadastrado como candidato nesta votação.");
}

// Valida upload da foto
if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    redirecionarComErro($idvotacao, "É necessário enviar uma foto.");
}

// Valida tipo de arquivo
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $_FILES['foto']['tmp_name']);
finfo_close($finfo);

$permitidos = ['image/jpeg', 'image/png', 'image/jpg'];

if (!in_array($mime, $permitidos)) {
    redirecionarComErro($idvotacao, "Formato de imagem não permitido. Use apenas JPG ou PNG.");
}

// Valida tamanho (max 5MB)
if ($_FILES['foto']['size'] > 5 * 1024 * 1024) {
    redirecionarComErro($idvotacao, "A foto não pode ter mais de 5MB.");
}

// Valida dimensões mínimas da imagem (opcional)
list($width, $height) = getimagesize($_FILES['foto']['tmp_name']);
if ($width < 200 || $height < 200) {
    redirecionarComErro($idvotacao, "A foto deve ter no mínimo 200x200 pixels.");
}

// Lê o conteúdo da imagem
$imgData = file_get_contents($_FILES['foto']['tmp_name']);

if ($imgData === false) {
    redirecionarComErro($idvotacao, "Erro ao processar a imagem.");
}

// Insere candidato no banco de dados
try {
    $stmt = $pdo->prepare("
        INSERT INTO tb_candidatos (imagem, nomealuno, email, ra, idvotacao) 
        VALUES (?, ?, ?, ?, ?)
    ");
    
    $sucesso = $stmt->execute([$imgData, $nome, $email, $ra, $idvotacao]);
    
    if ($sucesso) {
        // Redireciona para página de sucesso
        header('Location: popupcandidatura.php');
        exit;
    } else {
        redirecionarComErro($idvotacao, "Erro ao cadastrar candidatura. Tente novamente.");
    }
    
} catch (PDOException $e) {
    // Log do erro (em produção, salvar em arquivo de log)
    error_log("Erro ao cadastrar candidato: " . $e->getMessage());
    redirecionarComErro($idvotacao, "Erro no sistema. Por favor, contate o administrador.");
}
?>