<?php
require_once 'config.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);

    try {
        $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);
        
        // Em vez de deletar, apenas mudamos o status para 'devolvido'
        // Isso mantém o histórico no banco (o registro que conversamos!)
        $sql = "UPDATE emprestimos SET status = 'devolvido' WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $id]);

        echo "<script>alert('Livro devolvido com sucesso!'); window.location.href='painel.php';</script>";
    } catch (PDOException $e) {
        die("Erro ao devolver: " . $e->getMessage());
    }
}
?>