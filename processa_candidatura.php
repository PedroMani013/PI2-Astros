<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['aluno'])) {
    header('Location: logaluno.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: votacoesaluno.php');
    exit;
}

$idaluno = (int)$_SESSION['aluno']['idaluno'];
$idvotacao = (int)$_POST['idvotacao'];
$nome = trim($_POST['nomealuno'] ?? '');
$email = trim($_POST['email'] ?? '');
$ra = trim($_POST['ra'] ?? '');

if ($nome === '' || $ra === '') {
    die("Dados incompletos.");
}

if (!isset($_FILES['foto']) || $_FILES['foto']['error'] !== UPLOAD_ERR_OK) {
    die("Erro ao enviar a foto.");
}

$mime = mime_content_type($_FILES['foto']['tmp_name']);
$permitidos = ['image/jpeg','image/png','image/jpg'];
if (!in_array($mime, $permitidos)) {
    die("Formato de imagem não permitido.");
}

$imgData = file_get_contents($_FILES['foto']['tmp_name']);

// checa se já candidato
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM tb_candidatos WHERE ra = ? AND idvotacao = ?");
$stmt->execute([$ra, $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já está cadastrado como candidato.");
}

// insere candidato
$stmt = $pdo->prepare("INSERT INTO tb_candidatos (imagem, nomealuno, email, ra, idvotacao) VALUES (?, ?, ?, ?, ?)");
$ok = $stmt->execute([$imgData, $nome, $email, $ra, $idvotacao]);

if ($ok) {
    // sucesso -> redireciona para a tela de votação do aluno
    header('Location: votacoesaluno.php');
    exit;
} else {
    die("Erro ao cadastrar candidato.");
}
