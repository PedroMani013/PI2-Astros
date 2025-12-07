<?php
session_start();
require_once 'conexao.php';
require_once __DIR__ . '/Ata_PDF/TCPDF/tcpdf.php';
require_once __DIR__ . '/Ata_PDF/FPDI/src/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// Verifica se admin está logado
if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
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

// Buscar os 2 candidatos mais votados (EXCLUINDO votos nulos)
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

// Buscar alunos que votaram nesta votação (incluindo votos nulos)
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

// Separar representante e suplente
$representante = $vencedores[0] ?? null;
$suplente = $vencedores[1] ?? null;

// Preparar dados para o PDF
$semestre = $votacao['semestre'];
$curso = strtoupper(utf8_decode($votacao['curso']));
$dataFinal = new DateTime($votacao['data_final']);
$datafinal = $dataFinal->format('d/m/Y');
$dataano = $dataFinal->format('Y');

$eleito = $representante ? utf8_decode($representante['nomealuno']) : "NAO HOUVE CANDIDATO ELEITO";
$RAeleito = $representante ? $representante['ra'] : "N/A";

$suplenteNome = $suplente ? utf8_decode($suplente['nomealuno']) : "NAO HOUVE SUPLENTE ELEITO";
$RAsuplente = $suplente ? $suplente['ra'] : "N/A";

// Criar PDF usando FPDI
$pdf = new Fpdi();

// Arquivo modelo
$modelo = __DIR__ . '/Ata_PDF/Modelopdf/modelo_de_ata.pdf';

// Verifica se o arquivo modelo existe
if (!file_exists($modelo)) {
    die("Erro: Arquivo modelo não encontrado em: " . $modelo);
}

try {
    // Importa modelo - PÁGINA 1
    $pageCount = $pdf->setSourceFile($modelo);
    $tpl = $pdf->importPage(1);

    // Cria primeira página
    $pdf->AddPage();
    $pdf->useTemplate($tpl, 0, 0);

    // ========== TEXTO DA ATA ==========
    $pdf->SetFont('helvetica', '', 11);
    $pdf->SetTextColor(0, 0, 0);

    // Posicionamento do texto (no espaço em branco entre cabeçalho e tabela)
    $pdf->SetXY(20, 55);

    $texto = "ATA DE ELEICAO DE REPRESENTANTES DE TURMA DO {$semestre}o SEMESTRE DE {$dataano}, DO CURSO DE TECNOLOGIA EM {$curso} DA FACULDADE DE TECNOLOGIA DE ITAPIRA \"OGARI DE CASTRO PACHECO\". Ao dia {$datafinal}, foram apurados os votos dos alunos regularmente matriculados no {$semestre}o semestre de {$dataano} do Curso Superior de Tecnologia em {$curso} para eleicao de novos representantes de turma. Os representantes eleitos fazem a representacao dos alunos nos orgaos colegiados da Faculdade, com direito a voz e voto, conforme o disposto no artigo 69 da Deliberacao CEETEPS no 07, de 15 de dezembro de 2006. Foi eleito(a) como representante o(a) aluno(a) {$eleito}, R.A. no {$RAeleito} e eleito como vice o(a) aluno(a) {$suplenteNome}, R.A. no {$RAsuplente}. A presente ata, apos leitura e concordancia, sera assinada por todos os alunos participantes. Itapira, {$datafinal}.";

    $pdf->MultiCell(170, 6, $texto, 0, 'J', 0);

    // ========== PREENCHER TABELA ==========
    if (!empty($votantes)) {
        $pdf->SetFont('helvetica', '', 9);
        
        // Posições das colunas (baseado na tabela do modelo)
        $xNome = 25;      // Posição X da coluna NOME
        $xRA = 116;       // Posição X da coluna R.A
        
        // Altura da linha
        $alturaLinha = 9.8;
        
        // ===== PÁGINA 1: Linhas 1-18 =====
        $yInicial = 110; // Posição Y da primeira linha de dados
        $yAtual = $yInicial;
        
        $contador = 1;
        
        foreach ($votantes as $index => $votante) {
            // Se passou de 18 linhas, vai para página 2
            if ($contador > 18 && $contador <= 48) {
                if ($contador == 19) {
                    // Importa e cria página 2
                    if ($pageCount >= 2) {
                        $tpl2 = $pdf->importPage(2);
                        $pdf->AddPage();
                        $pdf->useTemplate($tpl2, 0, 0);
                        $yAtual = 47; // Posição inicial na página 2
                        $pdf->SetFont('helvetica', '', 9);
                    }
                }
            }
            
            // Limite máximo de 48 alunos (conforme modelo)
            if ($contador > 48) {
                break;
            }
            
            // Preparar dados
            $nomeAluno = strtoupper(utf8_decode($votante['nome']));
            $raAluno = $votante['ra'];
            
            // Ajuste vertical para centralizar texto na célula
            $yTexto = $yAtual + 2;
            
            // Coluna NOME
            $pdf->SetXY($xNome, $yTexto);
            $pdf->Cell(90, 5, $nomeAluno, 0, 0, 'L');
            
            // Coluna R.A
            $pdf->SetXY($xRA, $yTexto);
            $pdf->Cell(35, 5, $raAluno, 0, 0, 'C');
            
            $contador++;
            $yAtual += $alturaLinha;
        }
    }

    // Nome do arquivo
    $cursoLimpo = preg_replace('/[^A-Za-z0-9_\-]/', '_', $votacao['curso']);
    $nomeArquivo = "Ata_Eleicao_{$cursoLimpo}_{$semestre}Sem_{$dataano}.pdf";

    // Salvar o PDF em uma pasta temporária
    $pastaTemp = __DIR__ . '/temp_pdfs/';
    if (!file_exists($pastaTemp)) {
        mkdir($pastaTemp, 0777, true);
    }

    $caminhoCompleto = $pastaTemp . $nomeArquivo;
    $pdf->Output($caminhoCompleto, 'F');

    // Guardar informações na sessão para a página de popup
    $_SESSION['ata_gerada'] = [
        'arquivo' => $nomeArquivo,
        'caminho' => $caminhoCompleto,
        'curso' => $votacao['curso'],
        'semestre' => $semestre,
        'total_votantes' => count($votantes)
    ];

    // Redirecionar para popup
    header("Location: popupbaixandoata.php?idvotacao={$idvotacao}");
    exit;
    
} catch (Exception $e) {
    die("Erro ao processar PDF: " . $e->getMessage() . "<br><br>Solucao: O PDF pode estar com compressao nao suportada. Tente recriar o PDF ou usar uma ferramenta para descomprimi-lo.");
}
?>