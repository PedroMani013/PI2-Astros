<?php
session_start();

// Verifica se admin está logado
if (!isset($_SESSION['admin'])) {
    header('Location: login_adm.php');
    exit;
}

// Verifica se o arquivo foi especificado
if (!isset($_GET['arquivo'])) {
    die("Arquivo não especificado.");
}

$nomeArquivo = basename($_GET['arquivo']); // Sanitiza o nome do arquivo
$pastaTemp = __DIR__ . '/temp_pdfs/';
$caminhoCompleto = $pastaTemp . $nomeArquivo;

// Verifica se o arquivo existe
if (!file_exists($caminhoCompleto)) {
    die("Arquivo não encontrado.");
}

// Verifica se é um PDF
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mimeType = finfo_file($finfo, $caminhoCompleto);
finfo_close($finfo);

if ($mimeType !== 'application/pdf') {
    die("Tipo de arquivo inválido.");
}

// Configura headers para download
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="' . $nomeArquivo . '"');
header('Content-Length: ' . filesize($caminhoCompleto));
header('Cache-Control: private, max-age=0, must-revalidate');
header('Pragma: public');

// Limpa o buffer de saída
ob_clean();
flush();

// Envia o arquivo
readfile($caminhoCompleto);

// Opcional: Deletar o arquivo após download (descomente se desejar)
// unlink($caminhoCompleto);

exit;
?>