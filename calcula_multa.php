<?php
require_once 'config.php';

function calcularMultaPDO($pdo, $id_emprestimo, $valor_diaria = 2.50)
{
    // 1. Busca os dados usando PDO (compatível com seu sistema)
    $sql = "SELECT data_devolucao_prevista, data_devolucao_real 
            FROM emprestimos 
            WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id_emprestimo]);
    $dados = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dados) return ['atrasado' => false, 'dias' => 0, 'multa' => 0];

    // 2. Comparação de datas
    $hoje = new DateTime('today'); // 'today' zera as horas para o cálculo ser exato por dia
    $prevista = new DateTime($dados['data_devolucao_prevista']);

    $data_final = ($dados['data_devolucao_real'])
        ? new DateTime($dados['data_devolucao_real'])
        : $hoje;

    // 3. Cálculo
    if ($data_final > $prevista) {
        $intervalo = $data_final->diff($prevista);
        $dias_atraso = $intervalo->days;
        $valor_total = $dias_atraso * $valor_diaria;

        return [
            'atrasado' => true,
            'dias' => $dias_atraso,
            'multa' => $valor_total
        ];
    }

    return ['atrasado' => false, 'dias' => 0, 'multa' => 0.00];
}

// Exemplo de integração com sua conexão existente
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $pdo = new PDO($dsn, DB_USER, DB_PASS);

    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        $resultado = calcularMultaPDO($pdo, $id);

        if ($resultado['atrasado']) {
            echo "Atenção: " . $resultado['dias'] . " dias de atraso. Multa: R$ " . number_format($resultado['multa'], 2, ',', '.');
        } else {
            echo "Em dia.";
        }
    }
} catch (PDOException $e) {
    echo "Erro de conexão: " . $e->getMessage();
}
?>