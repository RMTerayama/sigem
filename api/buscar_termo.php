<?php
include '../includes/conexoes.php';

$termoId = $_GET['id'];

try {
    // Busca as informações do termo
    $sqlTermo = "
        SELECT 
            ts.id, 
            ts.numero_termo, 
            s.nome AS secretaria_nome, 
            d.nome AS departamento_nome, 
            ts.responsavel, 
            ts.data_saida,
            ts.assinatura
        FROM 
            db_sigem.termos_saida ts
        INNER JOIN 
            db_pmtl.secretarias s ON ts.secretaria = s.id
        INNER JOIN 
            db_pmtl.departamentos d ON ts.departamento = d.id
        WHERE 
            ts.id = :termoId
    ";
    $stmtTermo = $pdo->prepare($sqlTermo);
    $stmtTermo->execute([':termoId' => $termoId]);
    $termo = $stmtTermo->fetch(PDO::FETCH_ASSOC);

    if (!$termo) {
        throw new Exception("Termo não encontrado.");
    }

    // Busca os itens do termo
    $sqlItens = "
        SELECT 
            i.nome, 
            i.modelo, 
            isa.quantidade, 
            isa.identificacao
        FROM 
            db_sigem.itens_saida isa
        INNER JOIN 
            db_sigem.item i ON isa.item_id = i.id_item
        WHERE 
            isa.termo_id = :termoId
    ";


    $stmtItens = $pdo->prepare($sqlItens);
    $stmtItens->execute([':termoId' => $termoId]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    // Retorna os dados
    echo json_encode([
        'success' => true,
        'termo' => $termo,
        'itens' => $itens // Certifique-se de que 'itens' está sendo retornado
    ]);
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}