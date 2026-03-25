<?php
session_start();
require_once 'config.php';
require_once 'emprestimo.php';

$servico = new Emprestimo();

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // --- COLOQUE O CÓDIGO AQUI ---
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
    // --- FIM DO BLOCO DE MULTAS ---

    // Lógica de Processamento de Empréstimo (já existente no seu código)
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_confirmar'])) {
        // ... seu código de confirmação de empréstimo ...
    }

    // Captura mensagens de outras páginas
    if (isset($_SESSION['toast_msg'])) {
        $mensagem_toast = $_SESSION['toast_msg'];
        $tipo_toast = $_SESSION['toast_type'];
        unset($_SESSION['toast_msg']);
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
                                        <td style='color: {$corMulta}; font-weight: bold;'>{$multaTexto}</td>
                                        <td>
                                            <a href='finalizar_devolucao.php?id={$linha['id']}' class='btn-devolver' onclick='return confirm(\"Confirmar?\")'>Devolver</a>
                                        </td>
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
                        <select name="metodo" id="metodo_pagamento" required onchange="verificarPix()">
                            <option value="pix">Pix</option>
                            <option value="dinheiro">Dinheiro</option>
                        </select>
                    </div>

                    <div id="area-pix" style="display: none; text-align: center; margin: 20px 0;">
                        <p style="font-size: 12px; color: #666; margin-bottom: 10px;">Escaneie o QR Code abaixo para pagar:</p>
                        <img src="_imagens/qrcode.png" alt="QR Code Pix" style="width: 200px; border: 1px solid #ddd; padding: 10px; border-radius: 10px; background: white;">
                        <p style="font-weight: bold; color: #1a237e; margin-top: 5px;">Chave Pix: seu@email.com</p>
                    </div>
                    <button type="submit" class="btn-submit" style="background-color: #28a745;">Confirmar Pagamento</button>
                </form>
            </div>

        </div>
    </main>

    <script>
        const btnEmp = document.getElementById('btn-aba-emp');
        const btnDev = document.getElementById('btn-aba-dev');
        const btnPag = document.getElementById('btn-aba-pag');
        const secEmp = document.getElementById('secao-emprestimo');
        const secDev = document.getElementById('secao-devolucoes');
        const secPag = document.getElementById('secao-pagamento');
        const subtitle = document.getElementById('card-subtitle');
        const card = document.getElementById('main-card');

        function trocarAba(aba) {
            [btnEmp, btnDev, btnPag].forEach(b => b.classList.remove('active'));
            [secEmp, secDev, secPag].forEach(s => s.style.display = "none");

            if (aba === 'dev') {
                btnDev.classList.add('active');
                subtitle.innerText = "Devoluções e Atrasos";
                secDev.style.display = "block";
                card.style.maxWidth = "800px";
            } else if (aba === 'pag') {
                btnPag.classList.add('active');
                subtitle.innerText = "Liquidar Multas";
                secPag.style.display = "block";
                card.style.maxWidth = "450px";
            } else {
                btnEmp.classList.add('active');
                subtitle.innerText = "Novo Empréstimo";
                secEmp.style.display = "block";
                card.style.maxWidth = "450px";
            }
        }

        btnDev.addEventListener('click', () => trocarAba('dev'));
        btnEmp.addEventListener('click', () => trocarAba('emp'));
        btnPag.addEventListener('click', () => trocarAba('pag'));
    </script>
</body>

</html>