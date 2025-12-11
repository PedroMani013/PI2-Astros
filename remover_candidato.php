<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login_adm.php");
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID inválido");
}

$id = (int)$_GET['id'];
$idvotacao = (int)$_GET['idvotacao'];

// PROTEÇÃO: Não permitir remover o candidato especial de voto nulo (ID=0)
if ($id === 0) {
    die("Erro: Não é permitido remover o candidato especial de voto nulo.");
}

// Remover votos desse candidato
$pdo->prepare("DELETE FROM tb_votos WHERE idcandidato = ?")->execute([$id]);

// Remover o candidato
$pdo->prepare("DELETE FROM tb_candidatos WHERE idcandidato = ?")->execute([$id]);

header("Location: administracao_candidatos.php?idvotacao=$idvotacao&msg=removido");
exit;
?>