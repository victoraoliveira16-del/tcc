<?php
// Configurações do Banco de Dados
define('DB_HOST', '127.0.0.1'); // Usar IP é mais estável no Laragon/Windows
define('DB_PORT', '3306');      // A porta que você identificou
define('DB_NAME', 'livraria_livh');
define('DB_USER', 'root');
define('DB_PASS', '');          // Senha padrão do Laragon é vazia
define('DB_CHARSET', 'utf8mb4');

// Configurações de Erro (mude para false em produção)
define('DEBUG_MODE', true);