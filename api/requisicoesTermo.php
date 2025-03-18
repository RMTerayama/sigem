<?php


session_start();
// Inclui a conexão com o banco de dados
include './includes/conexoes.php';

// Definir o número de itens por página
$itens_por_pagina = 10;

// Capturar a página atual pela URL (padrão = 1)
$pagina_atual = isset($_GET['pagina']) && is_numeric($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_atual - 1) * $itens_por_pagina;

// Consultar a quantidade total de termos
$sql_total = "SELECT COUNT(*) AS total FROM db_sigem.termos_saida";
$total_result = $pdo->query($sql_total)->fetch(PDO::FETCH_ASSOC);
$total_itens = $total_result['total'];

// Calcular o total de páginas
$total_paginas = ceil($total_itens / $itens_por_pagina);

// Busca as secretarias cadastradas no banco de dados
$sql_secretarias = "SELECT * FROM secretarias";
$stmt_secretarias = $conn->query($sql_secretarias);
$secretarias = $stmt_secretarias->fetchAll(PDO::FETCH_ASSOC);

// Busca os departamentos cadastrados no banco de dados
$sql_departamentos = "SELECT d.id, d.nome AS departamento_nome, s.nome AS secretaria_nome 
                      FROM departamentos d
                      INNER JOIN secretarias s ON d.secretaria_id = s.id";
$stmt_departamentos = $conn->query($sql_departamentos);
$departamentos = $stmt_departamentos->fetchAll(PDO::FETCH_ASSOC);

// Consulta os termos de entrega já gravados, filtrando pelo departamento_responsavel do usuário autenticado
$sql_termos_gravados = "
    SELECT 
        ts.id, 
        ts.numero_termo, 
        s.nome AS secretaria_nome, 
        d.nome AS departamento_nome, 
        ts.responsavel, 
        ts.data_saida,
        ts.assinado,
        ts.dir_pdf
    FROM 
        db_sigem.termos_saida ts
    INNER JOIN 
        db_pmtl.secretarias s ON ts.secretaria = s.id
    INNER JOIN 
        db_pmtl.departamentos d ON ts.departamento = d.id
    WHERE 
        ts.departamento_responsavel = :departamento_responsavel
    ORDER BY 
        ts.data_saida DESC
    LIMIT :limit OFFSET :offset
";

$stmt_termos_gravados = $pdo->prepare($sql_termos_gravados);
$stmt_termos_gravados->bindValue(':departamento_responsavel', $_SESSION["usuario_autenticado"]["departamento_id"], PDO::PARAM_INT);
$stmt_termos_gravados->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
$stmt_termos_gravados->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_termos_gravados->execute();

$termos_gravados = $stmt_termos_gravados->fetchAll(PDO::FETCH_ASSOC);




// Busca os itens cadastrados no banco de dados, filtrando pelo departamento_id do usuário autenticado
$sql_itens = "SELECT * FROM item WHERE departamento_id = :departamento_id";

$stmt = $pdo->prepare($sql_itens);
$stmt->bindValue(':departamento_id', $_SESSION["usuario_autenticado"]["departamento_id"], PDO::PARAM_INT);
$stmt->execute();

$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>