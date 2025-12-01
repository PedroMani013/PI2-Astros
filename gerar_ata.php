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

// Buscar os 2 candidatos mais votados (EXCLUINDO o candidato especial ID=0)
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ? AND c.idcandidato != 0
    ORDER BY total_votos DESC, c.nomealuno ASC
    LIMIT 2
");
$sql->execute([$idvotacao]);
$vencedores = $sql->fetchAll(PDO::FETCH_ASSOC);

// Separar representante e suplente
$representante = $vencedores[0] ?? null;
$suplente = $vencedores[1] ?? null;

// Preparar dados para o PDF
$semestre = $votacao['semestre'];
$curso = strtoupper($votacao['curso']);
$dataFinal = new DateTime($votacao['data_final']);
$datafinal = $dataFinal->format('d/m/Y');
$dataano = $dataFinal->format('Y');

$eleito = $representante ? $representante['nomealuno'] : "NÃO HOUVE CANDIDATO ELEITO";
$RAeleito = $representante ? $representante['ra'] : "N/A";

$suplenteNome = $suplente ? $suplente['nomealuno'] : "NÃO HOUVE SUPLENTE ELEITO";
$RAsuplente = $suplente ? $suplente['ra'] : "N/A";

// Criar PDF usando FPDI
$pdf = new Fpdi();

// Arquivo modelo
$modelo = __DIR__ . '/Ata_PDF/Modelopdf/modelo_de_ata.pdf';

// Verifica se o arquivo modelo existe
if (!file_exists($modelo)) {
    die("Erro: Arquivo modelo não encontrado em: " . $modelo);
}

// Importa modelo
$pageCount = $pdf->setSourceFile($modelo);
$tpl = $pdf->importPage(1);

// Cria página igual ao modelo
$pdf->AddPage();
$pdf->useTemplate($tpl, 0, 0);

// Texto por cima do modelo da fatec
$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

// Posicionamento do texto (ajuste conforme necessário)
$pdf->SetXY(20, 55);

$texto = "ATA DE ELEIÇÃO DE REPRESENTANTES DE TURMA DO {$semestre}º SEMESTRE DE {$dataano}, DO CURSO DE TECNOLOGIA EM {$curso} DA FACULDADE DE TECNOLOGIA DE ITAPIRA OGARI DE CASTRO PACHECO. Ao dia {$datafinal}, foram apurados os votos dos alunos regularmente matriculados no {$semestre}º semestre de {$dataano} do Curso Superior de Tecnologia em {$curso} para eleição de novos representantes de turma. Os representantes eleitos fazem a representação dos alunos nos órgãos colegiados da Faculdade, com direito a voz e voto, conforme o disposto no artigo 69 da Deliberação CEETEPS nº 07, de 15 de dezembro de 2006. Foi eleito(a) como representante o(a) aluno(a) {$eleito}, R.A. nº {$RAeleito} e eleito como vice o(a) aluno(a) {$suplenteNome}, R.A. nº {$RAsuplente}. A presente ata, após leitura e concordância, será assinada por todos os alunos participantes. Itapira, {$datafinal}.";

$pdf->MultiCell(170, 6, $texto, 0, 'J', 0);

// Nome do arquivo
$nomeArquivo = "Ata_Eleicao_{$curso}_{$semestre}Sem_{$dataano}.pdf";

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
    'semestre' => $semestre
];

// Redirecionar para popup
header("Location: popupbaixandoata.php?idvotacao={$idvotacao}");
exit;
?>