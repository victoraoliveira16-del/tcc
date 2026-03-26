<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['emprestimo_id'] ?? 0);
    $metodo = $_POST['metodo'] ?? '';

    try {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $pdo = new PDO($dsn, DB_USER, DB_PASS);

        // Opcional: Aqui você pode marcar a multa como paga no banco de dados

        // Define a mensagem de agradecimento personalizada
        $_SESSION['toast_msg'] = "🙏 Obrigado! Recebemos seu pagamento via " . ucfirst($metodo) . ".";
        $_SESSION['toast_type'] = "background-color: #28a745;"; // Verde de sucesso

        // Redireciona de volta para a aba de pagamentos no painel
        header("Location: painel.php");
        exit;
    } catch (PDOException $e) {
        $_SESSION['toast_msg'] = "❌ Erro ao processar pagamento: " . $e->getMessage();
        $_SESSION['toast_type'] = "background-color: #ff5252;";
        header("Location: painel.php");
        exit;
    }
}
