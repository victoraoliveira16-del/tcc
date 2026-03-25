<?php
require_once 'Emprestimo.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $servico = new Emprestimo();

    $leitor = $_POST['leitor'] ?? '';
    $livro  = $_POST['livro'] ?? '';
    $prazo  = intval($_POST['prazo_semanas'] ?? 2);

    $resultado = $servico->registrar($leitor, $livro, $prazo);

    // Alerta de confirmação estilizado no texto
    echo "<script>
            alert('LIVH BOOKSTORE \\n---------------------------\\n' + '$resultado');
            window.location.href = 'painel.php?aba=dev';
          </script>";
}
