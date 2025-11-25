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

// verifica candidato pertence a votação
$stmt = $pdo->prepare("SELECT idvotacao FROM tb_candidatos WHERE idcandidato = ?");
$stmt->execute([$idcandidato]);
$c = $stmt->fetch();
if (!$c || (int)$c['idvotacao'] !== $idvotacao) {
    die("Candidato inválido para esta votação.");
}

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

// verifica se já votou nessa votação
$stmt = $pdo->prepare("
    SELECT COUNT(*) AS total FROM tb_votos v
    JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE v.idaluno = ? AND c.idvotacao = ?
");
$stmt->execute([$idaluno, $idvotacao]);
if ((int)$stmt->fetch()['total'] > 0) {
    die("Você já votou nesta votação.");
}

// insere voto
$stmt = $pdo->prepare("INSERT INTO tb_votos (datavoto, idaluno, idcandidato) VALUES (NOW(), ?, ?)");
if ($stmt->execute([$idaluno, $idcandidato])) {
    // redirecionar de volta com mensagem (vamos usar uma página simples com seu layout)
    $_SESSION['mensagem_sucesso'] = "Voto registrado com sucesso!";
    header('Location: votacoesaluno.php');
    exit;
} else {
    die("Erro ao registrar voto.");
}
