<?php
require_once 'config.php';

class Emprestimo
{
    private $pdo;

    public function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Erro de conexão na classe: " . $e->getMessage());
        }
    }

    public function registrar($leitor, $livro, $semanas)
    {
        // 1. Calcula a data de devolução baseada nas semanas escolhidas
        $dataDevolucao = date('Y-m-d', strtotime("+$semanas weeks"));

        // 2. Prepara o SQL para inserir no banco
        $sql = "INSERT INTO emprestimos (leitor, livro_nome, data_devolucao_prevista, status) 
                VALUES (:leitor, :livro, :data, 'ativo')";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'leitor' => $leitor,
                'livro'  => $livro,
                'data'   => $dataDevolucao
            ]);
            return "Empréstimo de '$livro' para $leitor realizado com sucesso!";
        } catch (PDOException $e) {
            return "Erro ao salvar no banco: " . $e->getMessage();
        }
    }
}
