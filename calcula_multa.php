<?php
require_once 'config.php';
require_once 'funcoes.php';

/**
 * Função para calcular a multa de um empréstimo específico
 * @param int $id_emprestimo ID do registro na tabela 'emprestimos'
 * @param float $valor_diaria Valor da multa por dia de atraso (ex: 2.00)
 * @return array Retorna os dias de atraso e o valor total da multa
 */
function calcularMulta($conn, $id_emprestimo, $valor_diaria = 2.50)
{
    // 1. Busca os dados do empréstimo
    $sql = "SELECT data_devolucao_prevista, data_devolucao_real 
            FROM emprestimos 
            WHERE id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_emprestimo);
    $stmt->execute();
    $resultado = $stmt->get_result();
    $dados = $resultado->fetch_assoc();

    if (!$dados) return ['dias' => 0, 'multa' => 0];

    // 2. Define a data de comparação (Se já devolveu, usa a real. Se não, usa a data de hoje)
    $hoje = new DateTime();
    $prevista = new DateTime($dados['data_devolucao_prevista']);

    // Se ainda não foi devolvido, comparamos com a data atual
    $data_final = ($dados['data_devolucao_real'])
        ? new DateTime($dados['data_devolucao_real'])
        : $hoje;

    // 3. Lógica do atraso
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

// Exemplo de uso se este arquivo for acessado via GET (ex: calcula_multa.php?id=5)
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $resultado = calcularMulta($conn, $id);

    if ($resultado['atrasado']) {
        echo "Atenção: Livro com " . $resultado['dias'] . " dias de atraso.";
        echo " Valor da multa: R$ " . number_format($resultado['multa'], 2, ',', '.');
    } else {
        echo "Empréstimo em dia ou devolvido no prazo.";
    }
}
?>