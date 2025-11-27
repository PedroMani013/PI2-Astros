<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ASTROS - Sistema De Votação</title>
    <link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div id="tudo">
        <header class="topo">
            <img src="images/fatec.png" alt="Logo FATEC" class="logotop">
            <h1>Votação Para Representante de Sala</h1>
            <img src="images/cps.png" alt="Logo Cps" class="logotop">
        </header>
        <main class="index">
            <h1>Votos Apurados</h1>
            <div class="boxvotos">
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>Representante: </h2>
                    </div>
                    <div class="boxalunotexto">
                        <p>RA: 0000000000000</p>
                        <p>E-mail: @fatec.sp.gov.br</p>
                        <p>Quantidade de votos: </p>
                    </div>
                </div>
                <div class="boxaluno">
                    <div class="boxalunotitulo">
                        <h2>Suplente: </h2>
                    </div>
                    <div class="boxalunotexto">
                        <p>RA: 0000000000000</p>
                        <p>E-mail: @fatec.sp.gov.br</p>
                        <p>Quantidade de votos: </p>
                    </div>
                </div>
            </div>
            <div class="gerarata">
                <p><a href="popupbaixandoata.php">Gerar Ata</a></p>
            </div>
            <div class="apurarvotos">
                <p><a href="popupfinalizarvotacao.php">Finalizar Votação</a></p>
            </div>
            <div class="finalizarsessao">
                <a href="paineladministrativo.php">
                    <img src="images/log-out.png" alt="">
                    <p>Voltar Para Votações</p>
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