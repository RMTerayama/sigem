<?php
// Configuração do banco de dados
define('DB_HOST', '172.18.2.49');
define('DB_NAME', 'db_sigem');
define('DB_USER', 'dev');
define('DB_PASS', 'devpass');

try {
    // Criando conexão PDO
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8", DB_USER, DB_PASS);
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Exibe mensagem apenas se o arquivo for acessado diretamente
    if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
        echo "✅ Conexão com o banco de dados estabelecida com sucesso!";
    }

} catch (PDOException $e) {
    die("❌ Erro na conexão: " . $e->getMessage());
}
?>
