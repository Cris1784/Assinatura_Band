<?php
// Função para ler o CSV e puxar os dados pele e-mail
function buscarDadosPorEmail($email, $arquivoCSV) {
    $dados = [];
    if (($handle = fopen($arquivoCSV, "r")) !== FALSE) {
        while (($linha = fgetcsv($handle, 1000, ",")) !== FALSE) {
            if (count($linha) >= 6 && strtolower($linha[5]) === strtolower($email)) {
                $dados = [
                    'nome' => $linha[0],
                    'cargo' => $linha[1],
                    'cidade' => $linha[2],
                    'telefone' => $linha[3],
                    'celular' => $linha[4],
                    'email' => $linha[5],
                ];
                break;
            }
        }
        fclose($handle);
    }
    return $dados;
}

// Função para formatar o telefone com a máscara adequada
function formatarTelefone($numero) {
    $numero = preg_replace('/\D/', '', $numero);
    if (strlen($numero) === 10) {
        return sprintf("(%s) %s-%s",
            substr($numero, 0, 2),
            substr($numero, 2, 4),
            substr($numero, 6, 4)
        );
    } elseif (strlen($numero) === 11) {
        return sprintf("(%s) %s-%s",
            substr($numero, 0, 2),
            substr($numero, 2, 5),
            substr($numero, 7, 4)
        );
    }
    return $numero;
}

$emailCorporativo = "";
$mostrarCelular = false;
$novoCelular = "";
$dados = [];
$erro = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $emailCorporativo = $_POST['emailCorporativo'];
    $mostrarCelular = isset($_POST['mostrarCelular']);
    $novoCelular = $_POST['novoCelular'] ?? '';
    $arquivoCSV = 'BD_Assinatura_Band2.csv';

    if (!str_ends_with(strtolower($emailCorporativo), '@band.com.br')) {
        $erro = "O e-mail fornecido é inválido. Deve conter o domínio @band.com.br.";
    } else {
  
        $dados = buscarDadosPorEmail($emailCorporativo, $arquivoCSV);

        if (empty($dados)) {
            $erro = "Não foram encontrados dados correspondentes ao e-mail fornecido.";
        } elseif ($mostrarCelular && empty($dados['celular']) && empty($novoCelular)) {

            $erro = "Erro: Por favor, insira o celular ou utilize um número existente.";
        }
    }
}


?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Assinatura de E-mail</title>
    <link rel="icon" href="favicon.ico" type="image/x-icon"> <!-- Adiciona o icone -->
    <link rel="stylesheet" type="text/css" href="styles.css">
    <style>
        .container {
            display: flex;
            justify-content: space-between;
        }
        .coluna {
            width: 45%;
        }
        .opcao-novo-celular {
            margin-top: 10px;
        }
        footer {
            margin-top: 200px;
            text-align: center;
            font-size: 0.9em;
            color: #555;
        }
    </style>
    
   <!-- Importa a biblioteca html2canvas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
</head>
<body>
    <div class="main-content">
    </div>
    <div class="box">
        <div class="header">
            <h1 class="titulo">Gerador de Assinatura de E-mail</h1>
        </div>
        <div class="input-conteiner">
            <form method="post">
                <div class="input-email">
                    <label for="emailCorporativo" class="label-custom">E-mail Corporativo:</label>
                    <input type="email" name="emailCorporativo" id="emailCorporativo" value="<?= htmlspecialchars($emailCorporativo) ?>" required>
                </div>
                <br>
                
                <div class="input-celular">
                    <input type="checkbox" name="mostrarCelular" id="mostrarCelular" <?= $mostrarCelular ? 'checked' : '' ?>>
                    <label for="mostrarCelular" class="label-custom">Mostrar Celular na assinatura</label>
                </div>
                <br>
                <button class="btn_gerar" type="submit">GERAR ASSINATURA</button>
            
    
            <?php if ($mostrarCelular): ?>
                <div class="opcao-novo-celular">
                     <?php if (!empty($dados['celular'])): ?>
                        <label for="novoCelular">Celular Atual: <?= htmlspecialchars(formatarTelefone($dados['celular'])) ?></label><br>
                     <?php else: ?>
                    <?php endif; ?>
                    <label for="novoCelular">Novo Celular (deixe em branco para usar o atual):</label>
                    <input type="text" name="novoCelular" id="novoCelular" value="<?= htmlspecialchars($novoCelular) ?>">
                </div>
            <?php endif; ?>

            </div>        
        <br>
    </div>
        
    </form>

    <?php if ($erro): ?> 
        <div class="erro">
            <?= htmlspecialchars($erro) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($dados) && !$erro): ?>  
            <div class="container" id="resultado">
                <div class="dados">
                    <p id="nome"><?= htmlspecialchars($dados['nome']) ?></p>
                    <p><?= htmlspecialchars($dados['cargo']) ?></p>
                    <p><?= htmlspecialchars($dados['cidade']) ?></p>
                    <p>T: +55 <?= htmlspecialchars(formatarTelefone($dados['telefone'])) ?></p>
                    <?php if ($mostrarCelular): ?>
                        <p>C: +55 <?= htmlspecialchars(formatarTelefone(!empty($novoCelular) ? $novoCelular : $dados['celular'])) ?></p>
                    <?php endif; ?>
                    <p><?= htmlspecialchars($dados['email']) ?></p>
                    
                </div>
                <div class="coluna">
                    <img class="imagem-assinatura" src="Assinatura_v3.png" alt="Imagem da assinatura">
                </div>
            </div>
            <br>
            <button class= "bnt-download"onclick="baixarImagem()">DOWNLOAD DA ASSINATURA</button>  
        <?php endif; ?>

    <footer>
        <img class="logo_band" src="vertical_branco_bandrs.png" alt="logo band">
        <img class="logo_bandnews" src="BandNews FM branco.png" alt="logo band news">
        <img class="logo_rb" src="Logo RB branco.png" alt="logo rb">
        <p><Strong>Desenvolvido por Cristiano Mello e Rafael Cerutti</strong><br>
        Dep. de Engenharia / TI - BAND RS<br>
        Coordenação Fabio Veloso<br>
        © 2024 Todos os direitos reservados.</p>
    </footer>

    <script>
        function baixarImagem() {
    var elemento = document.getElementById("resultado");
    html2canvas(elemento, {
        scale: 1, 
        backgroundcolor: null,
        width: 1536, 
        height: 320  
    }).then(function(canvas) {
        var resizedCanvas = document.createElement("canvas");
        resizedCanvas.width = 1536;
        resizedCanvas.height = 320;
        var ctx = resizedCanvas.getContext("2d");
        ctx.drawImage(canvas, 0, 0, 1536, 320);
        var link = document.createElement("a");
        link.href = resizedCanvas.toDataURL("image/png");
        link.download = "assinatura.png";
        link.click();
    });
}
    </script>
</body>
</html>
