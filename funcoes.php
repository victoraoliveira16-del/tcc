<?php
function calcularMulta($dataPrevista)
{
    $hoje = new DateTime();
    $prevista = new DateTime($dataPrevista);

    if ($hoje > $prevista) {
        $diferenca = $hoje->diff($prevista);
        $diasAtraso = $diferenca->days;
        $valorMultaPorDia = 2.50; // Exemplo: R$ 2,50 por dia
        return $diasAtraso * $valorMultaPorDia;
    }
    return 0;

    // Dentro do seu while no painel.php
    require_once 'funcoes.php';

    while ($linha = $consulta->fetch(PDO::FETCH_ASSOC)) {
        $valorMulta = calcularMulta($linha['data_devolucao_prevista']); // Usa sua função

        if ($valorMulta > 0) {
            $multaTexto = "R$ " . number_format($valorMulta, 2, ',', '.');
            $corMulta = "#ff5252";
        } else {
            $multaTexto = "No prazo";
            $corMulta = "#4caf50";
        }
        // ... restante do echo tr ...
    }
}
