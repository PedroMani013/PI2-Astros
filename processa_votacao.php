<?php
session_start();
require_once 'conexao.php';
date_default_timezone_set('America/Sao_Paulo');

if (!isset($_SESSION['admin'])) {
    die("Acesso negado.");
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: criarvotacao.php");
    exit;
}

$idadmin = $_SESSION['admin']['idadmin'];

$curso = trim($_POST['curso'] ?? '');
$semestre = (int)($_POST['semestre'] ?? 0);
$data_candidatura = $_POST['data_candidatura'] ?? '';
$data_inicio = $_POST['data_inicio'] ?? '';
$data_final = $_POST['data_final'] ?? '';

$erros = [];

// Validações básicas
if ($curso === '' || $curso === '0') {
    $erros[] = "Selecione um curso válido.";
}

if ($semestre === 0) {
    $erros[] = "Selecione um semestre válido.";
}

if (!$data_candidatura) {
    $erros[] = "Informe a data de candidatura.";
}

if (!$data_inicio) {
    $erros[] = "Informe a data de início da votação.";
}

if (!$data_final) {
    $erros[] = "Informe a data final da votação.";
}

// Se já tem erros básicos, retorna
if (!empty($erros)) {
    $_SESSION['erros_votacao'] = $erros;
    header("Location: criarvotacao.php");
    exit;
}

// Validações de lógica de datas
$datacand = strtotime($data_candidatura);
$datainicio = strtotime($data_inicio);
$datafim = strtotime($data_final);

if ($datacand > $datainicio) {
    $erros[] = "A data de candidatura deve ser anterior à data de início da votação.";
}

if ($datainicio >= $datafim) {
    $erros[] = "A data final deve ser posterior à data de início.";
}

if (!empty($erros)) {
    $_SESSION['erros_votacao'] = $erros;
    header("Location: criarvotacao.php");
    exit;
}

// Formata datas para o banco
$datacandStr = $data_candidatura . " 00:00:00";
$datainicioStr = $data_inicio . " 00:00:00";
$datafimStr = $data_final . " 23:59:59";

// Sempre ativa ao criar
$ativa = "sim";

try {
    $stmt = $pdo->prepare("
        INSERT INTO tb_votacoes 
        (curso, semestre, ativa, data_inicio, data_candidatura, data_final, idadmin)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    if ($stmt->execute([$curso, $semestre, $ativa, $datainicioStr, $datacandStr, $datafimStr, $idadmin])) {
        
        $idvotacao = $pdo->lastInsertId();

        // Vincula alunos à votação
        $upd = $pdo->prepare("
            UPDATE tb_alunos 
            SET idvotacao = ?
            WHERE curso = ? AND semestre = ?
        ");
        $upd->execute([$idvotacao, $curso, $semestre]);

        $_SESSION['sucesso_votacao'] = "Votação criada com sucesso!";
        header("Location: paineladministrativo.php");
        exit;
        
    } else {
        $_SESSION['erros_votacao'] = ["Erro ao criar votação no banco de dados."];
        header("Location: criarvotacao.php");
        exit;
    }
    
} catch (PDOException $e) {
    error_log("Erro ao criar votação: " . $e->getMessage());
    $_SESSION['erros_votacao'] = ["Erro no sistema. Por favor, contate o administrador."];
    header("Location: criarvotacao.php");
    exit;
}
?>