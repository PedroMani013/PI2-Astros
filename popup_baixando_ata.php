<?php
session_start();

// Verifica se há uma ata gerada
if (!isset($_SESSION['ata_gerada'])) {
    header('Location: painel_administrativo.php');
    exit;
}

$ataInfo = $_SESSION['ata_gerada'];
$idvotacao = $_GET['idvotacao'] ?? null;

// Limpa a sessão após pegar as informações
unset($_SESSION['ata_gerada']);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Baixando Ata</title>
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        .info-ata {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
        
        .info-ata p {
            margin: 8px 0;
            font-size: 1.1rem;
        }
        
        .btn-download {
            display: inline-block;
            background-color: #147C0E;
            color: white;
            padding: 12px 30px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            margin: 10px;
            transition: background-color 0.3s ease;
        }
        
        .btn-download:hover {
            background-color: #0c5808;
        }
    </style>
</head>
<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Sistema de Eleição para Representante de Sala</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>
        
        <main>
            <div class="greenpopup">
                <img src="images/download.png" alt="Download">
                <h2>ATA GERADA COM SUCESSO!</h2>
                <h3>Sua ata está pronta para download</h3>
                
                <div class="info-ata">
                    <p><strong>Curso:</strong> <?= htmlspecialchars($ataInfo['curso']) ?></p>
                    <p><strong>Semestre:</strong> <?= htmlspecialchars($ataInfo['semestre']) ?>º</p>
                    <p><strong>Arquivo:</strong> <?= htmlspecialchars($ataInfo['arquivo']) ?></p>
                </div>
                
                <a href="download_ata.php?arquivo=<?= urlencode($ataInfo['arquivo']) ?>" class="btn-download">
                    📥 BAIXAR ATA EM PDF
                </a>
                
                <p style="margin-top: 20px;">
                    <a href="votos_apurados.php<?= $idvotacao ? '?idvotacao=' . $idvotacao : '' ?>">
                        Clique aqui para voltar para a eleição
                    </a>
                </p>
                <span>.</span>
            </div> 
        </main>
        
        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
    
    <script>
        // Auto-download após 2 segundos
        setTimeout(function() {
            window.location.href = 'download_ata.php?arquivo=<?= urlencode($ataInfo['arquivo']) ?>';
        }, 2000);
    </script>
</body>
</html>