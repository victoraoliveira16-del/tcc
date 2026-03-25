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

    public function registrar($leitor, $livro, $prazo_tempo)
    {
        // Calcula a data baseada no valor do select (ex: "1 day", "2 weeks")
        $dataDevolucao = date('Y-m-d', strtotime("+$prazo_tempo"));

        $sql = "INSERT INTO emprestimos (leitor, livro_nome, data_devolucao_prevista, status) 
            VALUES (:leitor, :livro, :data, 'ativo')";

        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                'leitor' => $leitor,
                'livro'  => $livro,
                'data'   => $dataDevolucao
            ]);

            $dataBR = date('d/m/Y', strtotime($dataDevolucao));
            return "✅ Empréstimo de '$livro' realizado! 📅 Devolução em: $dataBR";
        } catch (PDOException $e) {
            return "❌ Erro ao salvar: " . $e->getMessage();
        }
    }
}
