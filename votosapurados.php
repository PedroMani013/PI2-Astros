<?php
session_start();
require_once 'conexao.php';

// Verificar se admin está logado
if (!isset($_SESSION['admin'])) {
    header('Location: logadm.php');
    exit;
}

// Verificar se idvotacao foi passado
if (!isset($_GET['idvotacao']) || !is_numeric($_GET['idvotacao'])) {
    die("ID de votação inválido.");
}

$idvotacao = (int)$_GET['idvotacao'];

// Buscar informações da votação
$stmtVot = $pdo->prepare("SELECT curso, semestre FROM tb_votacoes WHERE idvotacao = ?");
$stmtVot->execute([$idvotacao]);
$votacao = $stmtVot->fetch(PDO::FETCH_ASSOC);

if (!$votacao) {
    die("Votação não encontrada.");
}

// Buscar os 2 candidatos mais votados (EXCLUINDO o candidato especial ID=0)
$sql = $pdo->prepare("
    SELECT c.idcandidato, c.nomealuno, c.ra, c.email,
        (SELECT COUNT(*) FROM tb_votos v WHERE v.idcandidato = c.idcandidato) AS total_votos
    FROM tb_candidatos c
    WHERE c.idvotacao = ? AND c.nomealuno != 'VOTO NULO'
    ORDER BY total_votos DESC, c.nomealuno ASC
    LIMIT 2
");
$sql->execute([$idvotacao]);
$vencedores = $sql->fetchAll(PDO::FETCH_ASSOC);

// Separar representante e suplente
$representante = $vencedores[0] ?? null;
$suplente = $vencedores[1] ?? null;

// Total de votos válidos (excluindo nulos)
// Total de votos válidos (excluindo nulos)
$sqlTotal = $pdo->prepare("
    SELECT COUNT(*) as total FROM tb_votos v
    INNER JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE c.idvotacao = ? AND c.nomealuno != 'VOTO NULO'
");
$sqlTotal->execute([$idvotacao]);
$totalVotos = (int)$sqlTotal->fetch()['total'];

// Votos nulos DESTA votação
$sqlNulos = $pdo->prepare("
    SELECT COUNT(*) as total FROM tb_votos v
    INNER JOIN tb_candidatos c ON v.idcandidato = c.idcandidato
    WHERE c.idvotacao = ? AND c.nomealuno = 'VOTO NULO'
");
$sqlNulos->execute([$idvotacao]);
$votosNulos = (int)$sqlNulos->fetch()['total'];

// Total geral (válidos + nulos)
$totalGeral = $totalVotos + $votosNulos;
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Votos Apurados</title>
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <style>
        .info-votacao {
            background-color: #e8f4f8;
            padding: 15px;
            border-radius: 10px;
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
        
        .info-votacao p {
            margin: 5px 0;
            font-size: 1.2rem;
        }
        
        .resumo-votos {
            background-color: #6986C5;
            color: white;
            padding: 15px;
            border-radius: 10px;
            margin: 20px auto;
            width: 80%;
            text-align: center;
        }
        
        .resumo-votos h3 {
            margin: 10px 0;
            font-size: 1.5rem;
        }
        
        .votos-nulos {
            background-color: #6c757d;
            color: white;
            padding: 10px;
            border-radius: 10px;
            margin: 10px auto;
            width: 80%;
            text-align: center;
        }
        
        .sem-votos {
            text-align: center;
            font-size: 1.3rem;
            color: #666;
            margin: 30px 0;
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
        
        <main class="index">
            <h1>Votos Apurados</h1>
            
            <!-- Informações da Votação -->
            <div class="info-votacao">
                <p><strong>Votação:</strong> <?= htmlspecialchars($votacao['curso']) ?> - <?= htmlspecialchars($votacao['semestre']) ?>º Semestre</p>
            </div>
            
            <!-- Resumo de Votos -->
            <div class="resumo-votos">
                <h3>📊 Resumo da Eleição</h3>
                <p><strong>Total de votos válidos:</strong> <?= $totalVotos ?></p>
                <p><strong>Votos nulos:</strong> <?= $votosNulos ?></p>
                <p><strong>Total geral:</strong> <?= $totalGeral ?></p>
            </div>
            
            <?php if ($representante || $suplente): ?>
            
            <div class="boxvotos">
                <!-- Representante -->
                <?php if ($representante): ?>
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>🏆 Representante</h2>
                    </div>
                    <div class="boxalunotexto">
                        <p><strong>Nome:</strong> <?= htmlspecialchars($representante['nomealuno']) ?></p>
                        <p><strong>RA:</strong> <?= htmlspecialchars($representante['ra']) ?></p>
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($representante['email']) ?></p>
                        <p><strong>Quantidade de votos:</strong> <span style="font-size: 1.5em; color: #147C0E;"><?= $representante['total_votos'] ?></span></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>🏆 Representante</h2>
                    </div>
                    <div class="boxalunotexto">
                        <p style="padding: 20px; color: #666;">Nenhum candidato votado</p>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Suplente -->
                <?php if ($suplente): ?>
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>🥈 Suplente</h2>
                    </div>
                    <div class="boxalunotexto">
                        <p><strong>Nome:</strong> <?= htmlspecialchars($suplente['nomealuno']) ?></p>
                        <p><strong>RA:</strong> <?= htmlspecialchars($suplente['ra']) ?></p>
                        <p><strong>E-mail:</strong> <?= htmlspecialchars($suplente['email']) ?></p>
                        <p><strong>Quantidade de votos:</strong> <span style="font-size: 1.5em; color: #147C0E;"><?= $suplente['total_votos'] ?></span></p>
                    </div>
                </div>
                <?php else: ?>
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>🥈 Suplente</h2>
                    </div>
                    <div class="boxalunotexto">
                        <p style="padding: 20px; color: #666;">Nenhum candidato em segundo lugar</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
            
            <?php else: ?>
            
            <p class="sem-votos">⚠️ Nenhum voto foi registrado nesta eleição ainda.</p>
            
            <?php endif; ?>
            
            <!-- Botões de Ação -->
            <div class="gerarata">
                <p><a href="gerar_ata.php?idvotacao=<?= $idvotacao ?>">Gerar Ata</a></p>
            </div>
            
            <!-- ALTERAÇÃO: Link para finalizar votação -->
            <div class="apurarvotos">
                <p><a href="popupfinalizarvotacao.php?idvotacao=<?= $idvotacao ?>">Finalizar eleição</a></p>
            </div>
            
            <div class="finalizarsessao">
                <a href="paineladministrativo.php">
                    <img src="images/log-out.png" alt="">
                    <p>Voltar Para eleições</p>
                </a>
            </div>
            
        </main>
        
        <footer class="rodape">
            <img src="images/govsp.png" alt="" class="logosp">
            <img src="images/astros.png" alt="" class="logobottom">
        </footer>
    </div>
</body>

</html>