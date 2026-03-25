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
}
