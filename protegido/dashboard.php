<?php

// Iniciar sessão e configurar timezone
session_start();
date_default_timezone_set('America/Campo_Grande');
$role = $_SESSION["usuario_autenticado"]["role"] ?? 'guest'; // Pega o role do usuário ou define como 'guest' se não estiver logado

// Importa arquivos necessários
require '../api/login-jwt/auth/validate.php'; // Proteção com JWT
require '../api/login-jwt/auth/verificar_permissao.php';
require '../includes/conexoes.php'; // Conexão com o banco

// 🔹 Impede acesso de usuários com "padrao"
verificarPermissao(['admin']); // ⬅️ AGORA É CHAMADO NO INÍCIO!

// Captura os dados do usuário autenticado corretamente
$user = $_SESSION["usuario_autenticado"];

// 🔹 Consulta ao banco para contar usuários ativos
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM users");
$stmt->execute();
$total_usuarios = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

// 🔹 Define o horário do último acesso com o fuso correto
$ultimo_acesso = date("d/m/Y H:i");






try {
    // Consulta para contar os registros na tabela secretarias
    $querySecretarias = "SELECT COUNT(*) AS total_secretarias FROM secretarias";
    $stmtSecretarias = $conn->query($querySecretarias);
    $resultSecretarias = $stmtSecretarias->fetch(PDO::FETCH_ASSOC);
    $totalSecretarias = $resultSecretarias['total_secretarias'];

    // Consulta para contar os registros na tabela departamentos
    $queryDepartamentos = "SELECT COUNT(*) AS total_departamentos FROM departamentos";
    $stmtDepartamentos = $conn->query($queryDepartamentos);
    $resultDepartamentos = $stmtDepartamentos->fetch(PDO::FETCH_ASSOC);
    $totalDepartamentos = $resultDepartamentos['total_departamentos'];

    // Consulta para contar os registros na tabela item
    $queryItem = "SELECT COUNT(*) AS total_item FROM item";
    $stmtItem = $pdo->query($queryItem);
    $resultItem = $stmtItem->fetch(PDO::FETCH_ASSOC);
    $totalItem = $resultItem['total_item'];

    // Consulta para contar os registros na tabela termos_saida
    $queryTermosSaida = "SELECT COUNT(*) AS total_termos_saida FROM termos_saida";
    $stmtTermosSaida = $pdo->query($queryTermosSaida);
    $resultTermosSaida = $stmtTermosSaida->fetch(PDO::FETCH_ASSOC);
    $totalTermosSaida = $resultTermosSaida['total_termos_saida'];
    

     // Consulta para obter a quantidade de itens saindo por data
     $querySaidaPorData = "SELECT DATE(ts.data_saida) as data_saida, SUM(isd.quantidade) AS total_saida 
     FROM itens_saida isd
     JOIN termos_saida ts ON isd.termo_id = ts.id
     GROUP BY DATE(ts.data_saida) 
     ORDER BY DATE(ts.data_saida)";
$stmtSaidaPorData = $pdo->query($querySaidaPorData);
$saidaPorData = $stmtSaidaPorData->fetchAll(PDO::FETCH_ASSOC);

// Convertendo os dados para JSON para uso no gráfico
$labels = [];
$data = [];
foreach ($saidaPorData as $row) {
    $labels[] = date('Y-m-d', strtotime($row['data_saida'])); // Formata para Y-m-d
    $data[] = $row['total_saida']; // Mantém o valor numérico
}
} catch (PDOException $e) {
    die("Erro ao executar a consulta: " . $e->getMessage());
}


?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEM - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/core@1.6.9"></script>
    <script src="https://cdn.jsdelivr.net/npm/@floating-ui/dom@1.6.13"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <link rel="stylesheet" href="../css/styleMain.css">
</head>

<body>

    <?php include '../navbar.php'; ?>

    <div class="container mt-4">
        <h2>Bem-vindo, <?php echo htmlspecialchars($user["nome"]); ?>!</h2>
        <p>Você está na área protegida do SIGEM.</p>

        <!-- Exemplo de estatísticas -->
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Usuários Ativos</h5>
                        <p class="card-text">Total: <?php echo $total_usuarios; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Último Acesso</h5>
                        <p class="card-text"><?php echo $ultimo_acesso; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body d-flex" style="justify-content: space-between;">
                        <div>
                            <h5 class="card-title">Termos Gerados</h5>
                            <p class="card-text"><?php echo $totalTermosSaida; ?></p>
                        </div>
                        <div>
                            <h5 class="card-title">Itens Cadastrados</h5>
                            <p class="card-text"><?php echo $totalItem; ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Departamentos Cadastrados</h5>
                        <p class="card-text"><?php echo $totalDepartamentos; ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Secretarias Cadastradas</h5>
                        <p class="card-text"><?php echo $totalSecretarias; ?></p>
                    </div>
                </div>
            </div>
       
        </div>
        <div class="d-flex justify-content-center align-items-center" >
            <div class="col-md-7">
         <canvas id="saidaChart"></canvas>

        </div>
        </div>
        

        <!-- Botão de Logout -->
        <!-- <div class="mt-4">
            <form action="../api/login-jwt/auth/logout.php" method="POST">
                <button class="btn btn-danger">Sair</button>
            </form>
        </div> -->
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const ctx = document.getElementById('saidaChart').getContext('2d');
        const data = {
            labels: <?php echo json_encode($labels); ?>,
            datasets: [{
                label: 'Saída de Itens por Data',
                data: <?php echo json_encode($data); ?>,
                borderColor: 'rgba(75, 192, 192, 1)',
                backgroundColor: 'rgba(75, 192, 192, 0.2)',
                pointStyle: 'rectRot',
                pointRadius: 10,
                pointHoverRadius: 15,
                tension: 0.4 // Efeito de suavização
            }]
        };

        const config = {
            type: 'line',
            data: data,
            options: {
                responsive: true,
                animation: {
                    duration: 2000,
                    easing: 'easeInOutQuart'
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    }
                }
            }
        };

        new Chart(ctx, config);
    </script>
</body>

</html>
