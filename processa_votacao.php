<?php
session_start();
require_once 'conexao.php';

// Verifica login do admin
if (!isset($_SESSION['admin'])) {
    die("Acesso negado.");
}

$idadmin = $_SESSION['admin']['idadmin'] ?? null;

// Se por algum motivo não veio, bloqueia:
if (!$idadmin) {
    die("Erro de sessão: admin não identificado.");
}

// Função de validação
function validarVotacao($dados) {
    $erros = [];

    if ($dados['curso'] === "0") {
        $erros[] = "Selecione um curso válido.";
    }

    if ($dados['semestre'] === "0") {
        $erros[] = "Selecione um semestre válido.";
    }

    if (empty($dados['datacand'])) {
        $erros[] = "Informe a data de candidatura.";
    }

    if (empty($dados['datainicio'])) {
        $erros[] = "Informe a data de início da votação.";
    }

    if (empty($dados['datafim'])) {
        $erros[] = "Informe a data de encerramento da votação.";
    }

    // Criar timestamps
    $c = strtotime($dados['datacand']);
    $i = strtotime($dados['datainicio']);
    $f = strtotime($dados['datafim']);

    if ($c > $i) {
        $erros[] = "A candidatura deve iniciar antes da votação.";
    }

    if ($i >= $f) {
        $erros[] = "A votação deve encerrar APÓS o início.";
    }

    return $erros;
}

// Apenas continua se for POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $erros = validarVotacao($_POST);

    if (empty($erros)) {
        try {
            $curso = $_POST['curso'];
            $semestre = $_POST['semestre'];

            // Datas em formato datetime
            $datacand = $_POST['datacand'] . " 00:00:00";
            $datainicio = $_POST['datainicio'] . " 08:00:00";
            $datafim = $_POST['datafim'] . " 23:59:59";

            $sql = "INSERT INTO tb_votacoes 
                    (curso, semestre, data_inicio, data_candidatura, data_final, idadmin)
                    VALUES (?, ?, ?, ?, ?, ?)";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $curso,
                $semestre,
                $datainicio,
                $datacand,
                $datafim,
                $idadmin
            ]);

            $_SESSION['sucesso_votacao'] = "Votação criada com sucesso!";
            header("Location: paineladministrativo.php");
            exit;

        } catch (PDOException $e) {
            $_SESSION['erro_votacao'] = "Erro ao criar votação!";
            header("Location: criarvotacao.php");
            exit;
        }

    } else {
        // Junta os erros e envia de volta
        $_SESSION['erro_votacao'] = implode("<br>", $erros);
        header("Location: criarvotacao.php");
        exit;
    }

} else {
    die("Método inválido.");
}
