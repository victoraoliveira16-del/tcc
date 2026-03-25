<?php
require_once 'config.php';

try {
    $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['btn_confirmar'])) {
        $leitor = $_POST['leitor'];
        $livro = $_POST['livro'];
        $prazo_tempo = $_POST['prazo_tempo'];

        // VALIDAÇÃO: Verifica se o livro já possui um empréstimo ativo
        $checkSql = "SELECT COUNT(*) FROM emprestimos WHERE livro_nome = ? AND status = 'ativo'";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->execute([$livro]);
        $livroJaEmprestado = $checkStmt->fetchColumn();

        if ($livroJaEmprestado > 0) {
            echo "<script>alert('❌ Este livro já está emprestado no momento!');</script>";
        } else {
            // Calcula a data de devolução
            $data_devolucao = date('Y-m-d', strtotime("+$prazo_tempo"));

            $sql = "INSERT INTO emprestimos (leitor, livro_nome, data_devolucao_prevista, status) VALUES (?, ?, ?, 'ativo')";
            $stmt = $pdo->prepare($sql);

            if ($stmt->execute([$leitor, $livro, $data_devolucao])) {
                $data_formatada = date('d/m/Y', strtotime($data_devolucao));
                echo "<script>
                        alert('✅ Empréstimo realizado! Devolução em: $data_formatada');
                        window.location.href = window.location.href; 
                      </script>";
                exit;
            } else {
                echo "<script>alert('❌ Erro ao registrar no banco.');</script>";
            }
        }
    }
} catch (Exception $e) {
    echo "<script>alert('Erro: " . addslashes($e->getMessage()) . "');</script>";
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
    <header class="top-nav">
        <div class="logo">LIVH <span>BOOKSTORE</span></div>
        <nav>
            <button id="btn-aba-emp" class="nav-btn active">Empréstimo</button>
            <button id="btn-aba-dev" class="nav-btn">Devoluções/Atrasos</button>
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
                                $hoje = new DateTime();
                                $dataEntrega = new DateTime($linha['data_devolucao_prevista']);
                                $multaTexto = "No prazo";
                                $corMulta = "#4caf50";

                                if ($hoje > $dataEntrega) {
                                    $diasAtraso = $hoje->diff($dataEntrega)->days;
                                    $valorMulta = $diasAtraso * 2.50;
                                    $multaTexto = "R$ " . number_format($valorMulta, 2, ',', '.');
                                    $corMulta = "#ff5252";
                                }

                                echo "<tr>
                                        <td>{$contador}</td>
                                        <td>{$linha['leitor']}</td>
                                        <td>{$linha['livro_nome']}</td>
                                        <td style='color: {$corMulta}; font-weight: bold;'>{$multaTexto}</td>
                                        <td>
                                            <a href='finalizar_devolucao.php?id={$linha['id']}' class='btn-devolver'>Devolver</a>
                                        </td>
                                      </tr>";
                                $contador++;
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Seleção dos elementos (o seu "controle remoto")
        const btnEmp = document.getElementById('btn-aba-emp');
        const btnDev = document.getElementById('btn-aba-dev');
        const secEmp = document.getElementById('secao-emprestimo');
        const secDev = document.getElementById('secao-devolucoes');
        const subtitle = document.getElementById('card-subtitle');
        const card = document.getElementById('main-card');

        function trocarAba(aba) {
            if (aba === 'dev') {
                btnEmp.classList.remove('active');
                btnDev.classList.add('active');
                subtitle.innerText = "Devoluções e Atrasos";
                secEmp.style.display = "none"; 
                secDev.style.display = "block"; 
                card.style.maxWidth = "800px";
            } else {
                btnDev.classList.remove('active');
                btnEmp.classList.add('active');
                subtitle.innerText = "Novo Empréstimo";
                secDev.style.display = "none";
                secEmp.style.display = "block";
                card.style.maxWidth = "450px";
            }
        }

        btnDev.addEventListener('click', () => trocarAba('dev'));
        btnEmp.addEventListener('click', () => trocarAba('emp'));
    </script>
</body>

</html>