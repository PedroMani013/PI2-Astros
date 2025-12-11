<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin'])) {
    header('Location: login_adm.php');
    exit;
}

if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID inválido.");
}

$idvotacao = (int) $_GET['idvotacao'];

/* Verifica se existe */
$stmt = $pdo->prepare("SELECT * FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$votacao = $stmt->fetch();

if (!$votacao) {
    die("Votação não encontrada.");
}

/* 1. Apagar votos dos candidatos dessa votação */
$pdo->prepare("
    DELETE v FROM tb_votos v
    INNER JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE c.idvotacao = ?
")->execute([$idvotacao]);

/* 2. Apagar candidatos da votação */
$pdo->prepare("DELETE FROM tb_candidatos WHERE idvotacao = ?")
    ->execute([$idvotacao]);

/* 3. Desvincular alunos da votação */
$pdo->prepare("UPDATE tb_alunos SET idvotacao = NULL WHERE idvotacao = ?")
    ->execute([$idvotacao]);

/* 4. Excluir a votação */
$pdo->prepare("DELETE FROM tb_votacoes WHERE idvotacao = ?")
    ->execute([$idvotacao]);

header("Location: painel_administrativo.php?msg=removida");
exit;
?>
