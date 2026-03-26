<?php
session_start();
require_once 'config.php';
require_once 'emprestimo.php';

$servico = new Emprestimo();

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $listaMultas = [];
    $consultaMultas = $pdo->query("SELECT * FROM emprestimos WHERE status = 'ativo'");
    while ($row = $consultaMultas->fetch(PDO::FETCH_ASSOC)) {
        $hoje = new DateTime('today');
        $prevista = new DateTime($row['data_devolucao_prevista']);
        if ($hoje > $prevista) {
            $dias = $hoje->diff($prevista)->days;
            $valor = $dias * 2.50;
            $listaMultas[] = [
                'id' => $row['id'],
                'leitor' => $row['leitor'],
                'valor' => $valor,
                'valor_formatado' => number_format($valor, 2, ',', '.')
            ];
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_confirmar'])) {
        $leitor = trim($_POST['leitor']);
        $livro = $_POST['livro'];
        $prazo_tempo = $_POST['prazo_tempo'];

        $checkSql = "SELECT COUNT(*) FROM emprestimos WHERE livro_nome = ? AND status = 'ativo'";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$livro]);

        if ($checkStmt->fetchColumn() > 0) {
            $mensagem_toast = "❌ Este livro já está emprestado!";
            $tipo_toast = "background-color: #ff5252;";
        } else {
            $resultado = $servico->registrar($leitor, $livro, $prazo_tempo);
            $mensagem_toast = "✅ " . $resultado;
            $tipo_toast = "background-color: #28a745;";
        }
    }

    // No topo do painel.php, logo após o session_start() e a conexão PDO
    if (isset($_SESSION['toast_msg'])) {
        $mensagem_toast = $_SESSION['toast_msg'];
        $tipo_toast = $_SESSION['toast_type'];
        unset($_SESSION['toast_msg']); // Limpa para não repetir a mensagem ao atualizar
        unset($_SESSION['toast_type']);
    }
} catch (Exception $e) {
    $mensagem_toast = "Erro: " . $e->getMessage();
    $tipo_toast = "background-color: #ff5252;";
}
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LIVH Bookstore - Painel</title>
    <link rel="stylesheet" href="style.css">

</head>

<body>
    <?php if (isset($mensagem_toast)): ?>
        <div class="toast-message" style="<?php echo $tipo_toast; ?>">
            <?php echo $mensagem_toast; ?>
        </div>
        <script>
            setTimeout(() => {
                const toast = document.querySelector('.toast-message');
                if (toast) toast.style.display = 'none';
            }, 4000);
        </script>
    <?php endif; ?>

    <header class="top-nav">
        <div class="logo">LIVH <span>BOOKSTORE</span></div>
        <nav>
            <button id="btn-aba-emp" class="nav-btn active">Empréstimo</button>
            <button id="btn-aba-dev" class="nav-btn">Devoluções/Atrasos</button>
            <button id="btn-aba-pag" class="nav-btn">Pagamentos</button>
        </nav>
    </header>

    <main class="container">
        <div class="card" id="main-card">
            <h3>Livraria LIVH</h3>
            <h4 id="card-subtitle">Novo Empréstimo</h4>

            <div id="secao-emprestimo">
                <form action="" method="POST">
                    <div class="input-group">
                        <label>👤 Nome do Leitor</label>
                        <input type="text" name="leitor" placeholder="Digite o nome..." required>
                    </div>
                    <div class="input-group">
                        <label>📘 Selecione o Livro</label>
                        <select name="livro" required>
                            <option value="" disabled selected>Escolha um título...</option>
                            <option value="Dom Casmurro">Dom Casmurro</option>
                            <option value="1984">1984</option>
                            <option value="O Pequeno Príncipe">O Pequeno Príncipe</option>
                            <option value="O Alquimista">O Alquimista</option>
                            <option value="A Menina que Roubava Livros">A Menina que Roubava Livros</option>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>📅 Prazo de Devolução</label>
                        <select name="prazo_tempo">
                            <option value="1 day">1 Dia (Entrega amanhã)</option>
                            <option value="2 weeks">2 Semanas</option>
                            <option value="3 weeks">3 Semanas</option>
                        </select>
                    </div>
                    <p class="warning">⚠️ Multa diária de R$ 2,50 em caso de atraso.</p>
                    <button type="submit" name="btn_confirmar" class="btn-submit">Confirmar Empréstimo</button>
                </form>
            </div>

            <div id="secao-devolucoes" style="display: none;">
                <div style="overflow-x: auto;">
                    <table>
                        <thead>
                            <tr>
                                <th>Nº</th>
                                <th>Leitor</th>
                                <th>Livro</th>
                                <th>Entrega Prevista</th>
                                <th>Multa</th>
                                <th>Ação</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $consulta = $pdo->query("SELECT * FROM emprestimos WHERE status = 'ativo'");
                            $contador = 1;
                            while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
                                $hoje = new DateTime('today');
                                $dataEntrega = new DateTime($linha['data_devolucao_prevista']);

                                // Formata a data para o padrão brasileiro (DD/MM/AAAA)
                                $dataFormatada = $dataEntrega->format('d/m/Y');

                                $multaTexto = "No prazo";
                                $corMulta = "#4caf50";

                                if ($hoje > $dataEntrega) {
                                    $diferenca = $hoje->diff($dataEntrega);
                                    $valorMulta = $diferenca->days * 2.50;
                                    $multaTexto = "R$ " . number_format($valorMulta, 2, ',', '.');
                                    $corMulta = "#ff5252";
                                }

                                echo "<tr>
                            <td>{$contador}</td>
                            <td>" . htmlspecialchars($linha['leitor']) . "</td>
                            <td>" . htmlspecialchars($linha['livro_nome']) . "</td>
                            <td>{$dataFormatada}</td> 
                            <td style='color: {$corMulta}; font-weight: bold;'>{$multaTexto}</td>
                            <td><a href='finalizar_devolucao.php?id={$linha['id']}' class='btn-devolver' onclick='return confirm(\"Confirmar devolução?\")'>Devolver</a></td>
                          </tr>";
                                $contador++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="secao-pagamento" style="display: none;">
                <form action="processa_pagamento.php" method="POST">
                    <div class="input-group">
                        <label>👤 Selecione o Leitor em Atraso</label>
                        <select id="select-pagamento" name="emprestimo_id" required onchange="atualizarValorMulta()">
                            <option value="" disabled selected>Escolha um leitor...</option>
                            <?php foreach ($listaMultas as $multa): ?>
                                <option value="<?= $multa['id'] ?>" data-valor="<?= $multa['valor'] ?>">
                                    <?= htmlspecialchars($multa['leitor']) ?> (R$ <?= $multa['valor_formatado'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="input-group">
                        <label>💳 Método de Pagamento</label>
                        <select name="metodo" id="metodo_pagamento" required onchange="gerenciarMetodosPagamento()">
                            <option value="" disabled selected>Escolha um método...</option>
                            <option value="pix">Pix</option>
                            <option value="cartao">Crédito/Débito</option>
                        </select>
                    </div>
                    <div id="area-pix" style="display: none; text-align: center; margin: 20px 0;">
                        <p style="font-size: 12px; color: #666;">Escaneie o QR Code abaixo:</p>
                        <img src="_imagens/qr-code.png" alt="QR Code" style="width: 150px; padding: 10px; background: white;">
                    </div>
                    <div id="area-cartao" style="display: none;">
                        <p class="cartao-titulo">💳 Dados do Cartão</p>
                        <input type="text" class="input-cartao" placeholder="0000 0000 0000 0000" style="margin-bottom:10px; width: 100%; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                        <div style="display: flex; gap: 5px;">
                            <input type="text" class="input-cartao" placeholder="MM/AA" style="flex:1; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                            <input type="text" class="input-cartao" placeholder="CVV" style="flex:1; padding: 10px; border-radius: 5px; border: 1px solid #ccc;">
                        </div>
                    </div>
                    <button type="submit" class="btn-submit" style="background-color: #28a745; margin-top: 15px;">Confirmar Pagamento</button>
                </form>
            </div>
        </div>
    </main>
    <script src="script.js"></script>
</body>

</html>