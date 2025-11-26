<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin'])) {
    header("Location: logadm.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido");
}

$id = (int)$_GET['id'];
$idvotacao = (int)$_GET['idvotacao'];

// Remover votos desse candidato
$pdo->prepare("DELETE FROM tb_votos WHERE idcandidato = ?")->execute([$id]);

// Remover o candidato
$pdo->prepare("DELETE FROM tb_candidatos WHERE idcandidato = ?")->execute([$id]);

header("Location: administracaocandidatos.php?idvotacao=$idvotacao&msg=removido");
exit;
?>
