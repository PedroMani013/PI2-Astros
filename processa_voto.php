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
$idcandidato = (int)$_POST['idcandidato'];
$idvotacao = (int)$_POST['idvotacao'];
$voto_nulo = isset($_POST['voto_nulo']) && $_POST['voto_nulo'] == '1';

// verifica período de votação
$stmt = $pdo->prepare("SELECT data_inicio, data_final FROM tb_votacoes WHERE idvotacao = ?");
$stmt->execute([$idvotacao]);
$vot = $stmt->fetch();
if (!$vot) die("Votação inexistente.");

$agora = new DateTime();
$dataInicio = new DateTime($vot['data_inicio']);
$dataFinal = new DateTime($vot['data_final']);

if ($agora < $dataInicio || $agora > $dataFinal) {
    die("Fora do período de votação.");
}

// verifica se já votou nessa votação (incluindo voto nulo)
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total FROM tb_votos v
    WHERE v.idaluno = ? AND v.idcandidato IN (
        SELECT idcandidato FROM tb_candidatos WHERE idvotacao = ?
        UNION SELECT 0
    )
");
$stmt->execute([$idaluno, $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já votou nesta votação.");
}

// Valida o candidato
if ($voto_nulo) {
    // Para voto nulo, busca o candidato especial desta votação específica
    $stmt = $pdo->prepare("
        SELECT idcandidato FROM tb_candidatos 
        WHERE idvotacao = ? AND nomealuno = 'VOTO NULO'
    ");
    $stmt->execute([$idvotacao]);
    $candidatoNulo = $stmt->fetch();
    
    if (!$candidatoNulo) {
        die("Erro: Sistema de voto nulo não configurado para esta votação.");
    }
    $idcandidato = $candidatoNulo['idcandidato'];
} else {
    // Para voto normal, verifica se candidato pertence à votação
    $stmt = $pdo->prepare("SELECT idvotacao FROM tb_candidatos WHERE idcandidato = ?");
    $stmt->execute([$idcandidato]);
    $c = $stmt->fetch();
    if (!$c || (int)$c['idvotacao'] !== $idvotacao) {
        die("Candidato inválido para esta votação.");
    }
}

// insere voto
$stmt = $pdo->prepare("INSERT INTO tb_votos (datavoto, idaluno, idcandidato) VALUES (NOW(), ?, ?)");
if ($stmt->execute([$idaluno, $idcandidato])) {
    // Redireciona para página de confirmação de voto
    header('Location: popupvotoconcluido.php');
    exit;
} else {
    die("Erro ao registrar voto.");
}
?>