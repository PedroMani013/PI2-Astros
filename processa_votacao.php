<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['admin'])) {
    die("Acesso negado.");
}

$idadmin = $_SESSION['admin']['idadmin'];

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método inválido.");
}

$curso = $_POST['curso'] ?? '';
$semestre = $_POST['semestre'] ?? '';
$datacand = $_POST['datacand'] ?? '';
$datainicio = $_POST['datainicio'] ?? '';
$datafim = $_POST['datafim'] ?? '';

$erros = [];

if ($curso == "0") $erros[] = "Selecione um curso válido.";
if ($semestre == "0") $erros[] = "Selecione um semestre válido.";
if (!$datacand) $erros[] = "Informe a data de candidatura.";
if (!$datainicio) $erros[] = "Informe a data de início da votação.";
if (!$datafim) $erros[] = "Informe a data de fim.";

$c = strtotime($datacand);
$i = strtotime($datainicio);
$f = strtotime($datafim);

if ($c > $i) $erros[] = "Candidatura começa antes da votação.";
if ($i >= $f) $erros[] = "Data final deve ser após o início.";

if (!empty($erros)) {
    $_SESSION['erro_votacao'] = implode("<br>", $erros);
    header("Location: criarvotacao.php");
    exit;
}

// Sempre ativa ao criar
$ativa = "sim";

$datacand = "$datacand 00:00:00";
$datainicio = "$datainicio 00:00:00";
$datafim = "$datafim 23:59:59";

$stmt = $pdo->prepare("
    INSERT INTO tb_votacoes 
    (curso, semestre, ativa, data_inicio, data_candidatura, data_final, idadmin)
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

$stmt->execute([$curso, $semestre, $ativa, $datainicio, $datacand, $datafim, $idadmin]);

$idvotacao = $pdo->lastInsertId();

$upd = $pdo->prepare("
    UPDATE tb_alunos SET idvotacao = ?
    WHERE curso = ? AND semestre = ?
");
$upd->execute([$idvotacao, $curso, $semestre]);

$_SESSION['sucesso_votacao'] = "Votação criada com sucesso!";
header("Location: paineladministrativo.php");
exit;
