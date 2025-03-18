<?php
// conexoes.php

// Conexão com o banco de dados SIGEM
// Conexão com o banco de dados PMTL
try {
    $conn = new PDO('mysql:host=172.18.2.49;dbname=db_pmtl', 'dev', 'devpass');
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro ao conectar ao banco PMTL: " . $e->getMessage());
}


try {
    $pdo = new PDO('mysql:host=172.18.2.49;dbname=db_sigem', 'dev', 'devpass');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Query para buscar o último ID da tabela termos_saida
    $query = "SELECT MAX(id) AS ultimo_id FROM termos_saida";
    $stmt = $pdo->query($query);

    // Obtém o resultado
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // Verifica se o último ID foi encontrado
    if ($row && isset($row['ultimo_id'])) {
        $ultimo_id = $row['ultimo_id'];
        
    } else {
        // Apenas registra no log, sem exibir na tela
        error_log("Nenhum ID encontrado na tabela termos_saida.");
    }

} catch (PDOException $e) {
    die("Erro ao conectar ao banco SIGEM: " . $e->getMessage());
}


?>