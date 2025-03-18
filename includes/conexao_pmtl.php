<?php
// Configuração do banco de dados
define('DB_HOST2', '172.18.2.49');
define('DB_NAME2', 'db_pmtl');
define('DB_USER2', 'dev');
define('DB_PASS2', 'devpass');

try {
    // Criando conexão PDO
    $conn = new PDO("mysql:host=" . DB_HOST2 . ";dbname=" . DB_NAME2 . ";charset=utf8", DB_USER2, DB_PASS2);
    
    // Configura o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Exibe mensagem apenas se o arquivo for acessado diretamente
    if (basename(__FILE__) == basename($_SERVER["SCRIPT_FILENAME"])) {
        echo "✅ Conexão com o banco de dados estabelecida com sucesso!";
    }

} catch (PDOException $e) {
    die("❌ Erro na conexão: " . $e->getMessage());
}
?>
