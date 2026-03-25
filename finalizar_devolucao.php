<?php
session_start(); // Inicia a sessão para carregar a mensagem
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);

        $sql = "UPDATE emprestimos SET status = 'devolvido' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        // Define a mensagem de sucesso na sessão
        $_SESSION['toast_msg'] = "✅ Livro devolvido com sucesso!";
        $_SESSION['toast_type'] = "background-color: #28a745;";

        header("Location: painel.php?aba=dev"); // Redireciona de volta para a aba de devoluções
        exit;
    } catch (PDOException $e) {
        die("Erro ao devolver: " . $e->getMessage());
    }
}
?>