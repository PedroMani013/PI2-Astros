<?php
require_once __DIR__ . '/TCPDF/tcpdf.php';
require_once __DIR__ . '/FPDI/src/autoload.php';

use setasign\Fpdi\Tcpdf\Fpdi;

// esse cara aqui usa o Fpdi
$pdf = new Fpdi();

// arquivo modelo
$modelo = __DIR__ . '/Modelopdf/modelo_de_ata.pdf';

// importa modelo
$pageCount = $pdf->setSourceFile($modelo);
$tpl = $pdf->importPage(1);

// cria página igual ao modelo
$pdf->AddPage();
$pdf->useTemplate($tpl, 0, 0);

// pega do banco os nomes corretos das variaveis, estes aqui são representativos.

$semestre = "";
$datafinal = "";
$dataano = "2025";
$eleito = "";
$suplente = "";
$RAeleito = "";
$RAsuplente = "";
$curso = "";

// texto por cima do modelo da fatec 

$pdf->SetFont('helvetica', '', 11);
$pdf->SetTextColor(0, 0, 0);

$pdf->SetXY(20, 55);

$texto = "ATA DE ELEIÇÃO DE REPRESENTANTES DE TURMA DO {$semestre} SEMESTRE DE {$dataano}, 
DO CURSO DE TECNOLOGIA EM {$curso} DA FACULDADE DE TECNOLOGIA DE ITAPIRA “OGARI DE CASTRO PACHECO”. Ao dia {$datafinal}, foram apurados os votos dos alunos regularmente matriculados no {$semestre} semestre de {$dataano} do Curso Superior de Tecnologia em {$curso} para eleição de novos representantes de turma. Os representantes eleitos fazem a representação dos alunos nos órgãos colegiados da Faculdade,  com direito a voz e voto, conforme o disposto no artigo 69 da Deliberação CEETEPS nº 07, de 15 de dezembro de 2006.  Foi eleito(a) como representante o(a) aluno(a) {$eleito} , R.A. nº {$RAeleito} e eleito como vice o(a) aluno(a) {$suplente}, R.A. nº {$RAsuplente}. A presente ata, após leitura e concordância, será assinada por todos os alunos participantes. Itapira,{$datafinal}.";

$pdf->MultiCell(170, 6, $texto, 0, 'J', 0);

$pdf->Output("ata_resultado.pdf", "I");
?>