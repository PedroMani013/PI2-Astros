<?php
session_start();
require_once 'conexao.php';
require_once __DIR__ . '/Ata_PDF/TCPDF/tcpdf.php';
require_once __DIR__ . '/Ata_PDF/FPDI/src/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Verifica se admin está logado
if (!isset($_SESSION['admin'])) {
    header('Location: login_adm.php');
    exit;
}

// Verifica se idvotacao foi passado
if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID de votação inválido.");
}

$idvotacao = (int)$_GET['idvotacao'];

// Buscar informações da votação
$stmtVot = $pdo->prepare("SELECT curso, semestre, data_final FROM tb_votacoes WHERE idvotacao = ?");
$stmtVot->execute([$idvotacao]);
$votacao = $stmtVot->fetch(PDO::FETCH_ASSOC);

if (!$votacao) {
    die("Votação não encontrada.");
}

// Buscar vencedores
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ? AND c.nomealuno != 'VOTO NULO'
    ORDER BY total_votos DESC, c.nomealuno ASC
    LIMIT 2
");
$sql->execute([$idvotacao]);
$vencedores = $sql->fetchAll(PDO::FETCH_ASSOC);

// ✅ CORRIGIDO: Buscar TODOS os alunos votantes (incluindo quem votou nulo)
$sqlVotantes = $pdo->prepare("
    SELECT DISTINCT a.nome, a.ra
    FROM tb_alunos a
    INNER JOIN tb_votos v ON a.idaluno = v.idaluno
    INNER JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE c.idvotacao = ?
    ORDER BY a.nome ASC
");
$sqlVotantes->execute([$idvotacao]);
$votantes = $sqlVotantes->fetchAll(PDO::FETCH_ASSOC);

// Representante e suplente
$representante = $vencedores[0] ?? null;
$suplente = $vencedores[1] ?? null;

$semestre = $votacao['semestre'];

// ✅ CORRIGIDO: Melhor tratamento de encoding para evitar corrupção
// Remove acentos do curso para o PDF
$curso_original = $votacao['curso'];
$curso = mb_strtoupper($curso_original, 'UTF-8');

// Remove acentos: "Gestão" vira "GESTAO"
$curso_pdf = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $curso);
if ($curso_pdf === false || empty($curso_pdf)) {
    $curso_pdf = str_replace(
        ['Á', 'É', 'Í', 'Ó', 'Ú', 'Â', 'Ê', 'Ô', 'Ã', 'Õ', 'Ç', 'À'],
        ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'A', 'O', 'C', 'A'],
        $curso
    );
}

$dataFinal = new DateTime($votacao['data_final']);
$datafinal = $dataFinal->format('d/m/Y');
$dataano = $dataFinal->format('Y');

// ✅ CORRIGIDO: Remove acentos dos nomes para evitar problemas no PDF
$eleito = $representante ? mb_strtoupper($representante['nomealuno'], 'UTF-8') : "NAO HOUVE CANDIDATO ELEITO";
$eleito = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $eleito);
if ($eleito === false || empty($eleito)) {
    $eleito = $representante ? 
        str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Â', 'Ê', 'Ô', 'Ã', 'Õ', 'Ç', 'À'],
            ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'A', 'O', 'C', 'A'],
            mb_strtoupper($representante['nomealuno'], 'UTF-8')
        ) : "NAO HOUVE CANDIDATO ELEITO";
}
$RAeleito = $representante ? $representante['ra'] : "N/A";

$suplenteNome = $suplente ? mb_strtoupper($suplente['nomealuno'], 'UTF-8') : "NAO HOUVE SUPLENTE ELEITO";
$suplenteNome = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $suplenteNome);
if ($suplenteNome === false || empty($suplenteNome)) {
    $suplenteNome = $suplente ? 
        str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Â', 'Ê', 'Ô', 'Ã', 'Õ', 'Ç', 'À'],
            ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'A', 'O', 'C', 'A'],
            mb_strtoupper($suplente['nomealuno'], 'UTF-8')
        ) : "NAO HOUVE SUPLENTE ELEITO";
}
$RAsuplente = $suplente ? $suplente['ra'] : "N/A";

// Criar PDF
$pdf = new Fpdi();
$modelo = __DIR__ . '/Ata_PDF/Modelopdf/modelo_de_ata.pdf';

if (!file_exists($modelo)) {
    die("Erro: Arquivo modelo não encontrado em: " . $modelo);
}

try {
    $pageCount = $pdf->setSourceFile($modelo);
    $tpl = $pdf->importPage(1);

    $pdf->AddPage();
    $pdf->useTemplate($tpl, 0, 0);

    // Texto
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);
    $pdf->SetXY(20, 50);

    $texto = "ATA DE ELEICAO DE REPRESENTANTES DE TURMA DO {$semestre}o SEMESTRE DE {$dataano}, DO CURSO DE TECNOLOGIA EM {$curso_pdf} DA FACULDADE DE TECNOLOGIA DE ITAPIRA \"OGARI DE CASTRO PACHECO\". Ao dia {$datafinal}, foram apurados os votos dos alunos regularmente matriculados no {$semestre}o semestre de {$dataano} do Curso Superior de Tecnologia em {$curso_pdf} para eleicao de novos representantes de turma. Os representantes eleitos fazem a representacao dos alunos nos orgaos colegiados da Faculdade, com direito a voz e voto, conforme o disposto no artigo 69 da Deliberacao CEETEPS no 07, de 15 de dezembro de 2006. Foi eleito(a) como representante o(a) aluno(a) {$eleito}, R.A. no {$RAeleito} e eleito como vice o(a) aluno(a) {$suplenteNome}, R.A. no {$RAsuplente}. A presente ata, apos leitura e concordancia, sera assinada por todos os alunos participantes. Itapira, {$datafinal}.";

    $pdf->MultiCell(170, 6, $texto, 0, 'J', 0);
    $pdf->Ln(10);

    // TABELA CENTRALIZADA
    if (!empty($votantes)) {

        // Larguras
        $w1 = 15;
        $w2 = 75;
        $w3 = 35;
        $w4 = 45;

        // Centralizar
        $larguraTotal = $w1 + $w2 + $w3 + $w4;
        $Xcentral = (210 - $larguraTotal) / 2;

        // Cabeçalho
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->SetFillColor(202, 202, 202);

        $pdf->SetX($Xcentral);
        $pdf->Cell($w1, 8, 'No', 1, 0, 'C', true);
        $pdf->Cell($w2, 8, 'NOME', 1, 0, 'C', true);
        $pdf->Cell($w3, 8, 'R.A COMPLETO', 1, 0, 'C', true);
        $pdf->Cell($w4, 8, 'ASSINATURA', 1, 1, 'C', true);

        // Linhas
        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetFillColor(255, 255, 255);

        $contador = 1;
        $linhasPagina1 = 0;

        foreach ($votantes as $votante) {

            if ($linhasPagina1 >= 20 && $contador > 20) {
                if ($pageCount >= 2) {

                    $tpl2 = $pdf->importPage(2);
                    $pdf->AddPage();
                    $pdf->useTemplate($tpl2, 0, 0);

                    // Cabeçalho da página 2
                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->SetFillColor(237, 197, 198);

                    $pdf->SetX($Xcentral);
                    $pdf->Cell($w1, 8, 'No', 1, 0, 'C', true);
                    $pdf->Cell($w2, 8, 'NOME', 1, 0, 'C', true);
                    $pdf->Cell($w3, 8, 'R.A COMPLETO', 1, 0, 'C', true);
                    $pdf->Cell($w4, 8, 'ASSINATURA', 1, 1, 'C', true);

                    $pdf->SetFont('helvetica', '', 9);
                    $linhasPagina1 = 0;
                }
            }

            // ✅ SOLUÇÃO DEFINITIVA: Converte caracteres especiais para ASCII
            $nomeAluno = mb_strtoupper($votante['nome'], 'UTF-8');
            
            // Remove acentos e caracteres especiais
            $nomeAluno = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nomeAluno);
            
            // Se iconv falhar, usa substitutos manuais
            if ($nomeAluno === false || empty($nomeAluno)) {
                $nomeAluno = str_replace(
                    ['Á', 'É', 'Í', 'Ó', 'Ú', 'Â', 'Ê', 'Ô', 'Ã', 'Õ', 'Ç', 'À'],
                    ['A', 'E', 'I', 'O', 'U', 'A', 'E', 'O', 'A', 'O', 'C', 'A'],
                    mb_strtoupper($votante['nome'], 'UTF-8')
                );
            }
            
            $raAluno = $votante['ra'];

            $pdf->SetX($Xcentral);
            $pdf->Cell($w1, 7, $contador . '.', 1, 0, 'C');
            $pdf->Cell($w2, 7, $nomeAluno, 1, 0, 'L');
            $pdf->Cell($w3, 7, $raAluno, 1, 0, 'C');
            $pdf->Cell($w4, 7, '', 1, 1, 'C');

            $contador++;
            $linhasPagina1++;
        }

    } else {
        $pdf->SetFont('helvetica', 'I', 10);
        $pdf->Cell(0, 10, 'Nenhum aluno votou nesta eleicao.', 0, 1, 'C');
    }

    // Salvar PDF
    $cursoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $curso_original);
    $nomeArquivo = "Ata_Eleicao_{$cursoLimpo}_{$semestre}Sem_{$dataano}.pdf";

    $pastaTemp = __DIR__ . '/temp_pdfs/';
    if (!file_exists($pastaTemp)) {
        mkdir($pastaTemp, 0777, true);
    }

    $caminhoCompleto = $pastaTemp . $nomeArquivo;
    $pdf->Output($caminhoCompleto, 'F');

    $_SESSION['ata_gerada'] = [
        'arquivo' => $nomeArquivo,
        'caminho' => $caminhoCompleto,
        'curso' => $votacao['curso'],
        'semestre' => $semestre,
        'total_votantes' => count($votantes)
    ];

    header("Location: popup_baixando_ata.php?idvotacao={$idvotacao}");
    exit;

} catch (Exception $e) {
    die("Erro ao processar PDF: " . $e->getMessage());
}
?>