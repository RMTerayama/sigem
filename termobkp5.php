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

// Consulta os termos de entrega já gravados
$sql_termos_gravados = "
    SELECT 
        ts.id, 
        ts.numero_termo, 
        s.nome AS secretaria_nome, 
        d.nome AS departamento_nome, 
        ts.responsavel, 
        ts.data_saida,
        ts.assinado
    FROM 
        db_sigem.termos_saida ts
    INNER JOIN 
        db_pmtl.secretarias s ON ts.secretaria = s.id
    INNER JOIN 
        db_pmtl.departamentos d ON ts.departamento = d.id
    ORDER BY 
        ts.data_saida DESC
    LIMIT :limit OFFSET :offset
";

$stmt_termos_gravados = $pdo->prepare($sql_termos_gravados);
$stmt_termos_gravados->bindValue(':limit', $itens_por_pagina, PDO::PARAM_INT);
$stmt_termos_gravados->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt_termos_gravados->execute();
$termos_gravados = $stmt_termos_gravados->fetchAll(PDO::FETCH_ASSOC);


// Busca os itens cadastrados no banco de dados
$sql_itens = "SELECT * FROM item";
$stmt = $pdo->query($sql_itens);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEM - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/styleMain.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <style>
        #div-gerar-novo-termo .bg-light{
            margin-bottom: 10vw;
        }
        @media only screen and (max-width: 600px){
            #div-gerar-novo-termo .bg-light{
            margin-bottom: 30vw;
        } 
        }
    </style>
    <?php include 'navbar.php'; ?>

    <div class="container mt-3 text-center">
        <button id="btn-gerar-novo-termo" class="btn btn-primary">Gerar Novo Termo</button>
        <button id="btn-termos-cadastrados" class="btn btn-secondary">Termos de Entrega</button>

    </div>




    <div id="div-termos-cadastrados" class="container mt-5">
    <h2 class="mb-4 text-center">Termos de Entrega Cadastrados</h2>


    <div class="container mt-3">
        <input type="text" id="searchInput" class="form-control" placeholder="🔍 Pesquise na tabela...">
    </div>

    <!-- Tabela responsiva -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered table-striped">
            <thead class="table-dark text-center">
                <tr>
                    <th>Nº do Termo</th>
                    <th>Secretaria</th>
                    <th>Departamento</th>
                    <th>Emissor</th>
                    <th>Data/Hora</th>
                    <th>Ações</th>
                    <th>Assinado</th>
                </tr>
            </thead>
            <tbody class="text-center">
                <?php if (count($termos_gravados) > 0): ?>
                    <?php foreach ($termos_gravados as $termo): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($termo['numero_termo']); ?></td>
                            <td><?php echo htmlspecialchars($termo['secretaria_nome']); ?></td>
                            <td><?php echo htmlspecialchars($termo['departamento_nome']); ?></td>
                            <td><?php echo htmlspecialchars($termo['responsavel']); ?></td>
                            <td><?php echo htmlspecialchars($termo['data_saida']); ?></td>
                            <td>
                                <button onclick="gerarPDF(<?php echo $termo['id']; ?>)" class="btn btn-primary btn-sm">
                                    <i class="fas fa-file-pdf"></i> PDF
                                </button>                           
                                <button class="btn btn-success btn-sm" onclick="assinarTermo(<?php echo $termo['id']; ?>)">
                                    <i class="fas fa-signature"></i> Assinar
                                </button>
                            </td>
                            <td>
                                <?php if ($termo['assinado']): ?>
                                    <i class="fas fa-check-circle text-success" title="Assinado"></i>
                                <?php else: ?>
                                    <i class="fas fa-times-circle text-danger" title="Não Assinado"></i>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center text-muted">Nenhum termo encontrado.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <!-- Paginação -->
    <nav>
        <ul class="pagination pagination-lg justify-content-center">
            <?php if ($pagina_atual > 1): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=1">Primeira</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual - 1; ?>">Anterior</a>
                </li>
            <?php endif; ?>

            <li class="page-item active">
                <span class="page-link">Página <?php echo $pagina_atual; ?> de <?php echo $total_paginas; ?></span>
            </li>

            <?php if ($pagina_atual < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $pagina_atual + 1; ?>">Próxima</a>
                </li>
                <li class="page-item">
                    <a class="page-link" href="?pagina=<?php echo $total_paginas; ?>">Última</a>
                </li>
            <?php endif; ?>
        </ul>
    </nav>
</div>








    <!-- Modal de Assinatura com Informações do Termo de Saída -->
    <div class="modal fade" id="modalAssinatura" tabindex="-1" aria-labelledby="modalAssinaturaLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAssinaturaLabel">Assinar Termo de Saída</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Informações do Termo de Saída -->
                    <div id="termo-info">
                        <h5>Informações do Termo</h5>
                        <p><strong>Número do Termo:</strong> <span id="termo-numero"></span></p>
                        <p><strong>Secretaria:</strong> <span id="termo-secretaria"></span></p>
                        <p><strong>Departamento:</strong> <span id="termo-departamento"></span></p>
                        <p><strong>Responsável:</strong> <span id="termo-responsavel"></span></p>
                        <p><strong>Data de Saída:</strong> <span id="termo-data-saida"></span></p>
                    </div>

                    <!-- Lista de Itens e Patrimônios -->
                    <div id="itens-termo" class="mt-4">
                        <h5>Itens do Termo</h5>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th>Item</th>
                                    <th>Quantidade</th>
                                    <th>Patrimônio(s)</th>
                                </tr>
                            </thead>
                            <tbody id="itens-lista">
                                <!-- Itens serão preenchidos dinamicamente aqui -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Campo de Assinatura -->
                    <div class="mt-4">
                        <h5>Assinatura</h5>
                        <canvas id="assinaturaCanvas" width="400" height="200" style="border: 1px solid #000;"></canvas>
                        <button type="button" class="btn btn-danger mt-2" onclick="limparAssinatura()">Limpar</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarAssinatura()">Salvar Assinatura</button>
                </div>
            </div>
        </div>
    </div>



<!-- Formulário para Gerar Novo Termo -->
<div id="div-gerar-novo-termo" class="container mt-5" style="display: none;">
    <h2 class="mb-4 text-center">Novo Termo de Responsabilidade</h2>
    <form id="termoForm" method="POST" class="bg-light p-4 rounded shadow">
    <div class="mb-3">
            <label for="secretaria" class="form-label fw-bold">Secretaria</label>
            <select class="form-select" id="secretaria" name="secretaria" required>
                <option value="">Selecione uma Secretaria</option>
                <?php foreach ($secretarias as $secretaria): ?>
                    <option value="<?php echo htmlspecialchars($secretaria['id']); ?>">
                        <?php echo htmlspecialchars($secretaria['nome']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="mb-3">
            <label for="departamento" class="form-label fw-bold">Departamento</label>
            <select class="form-select" id="departamento" name="departamento" required>
                <option value="">Selecione um Departamento</option>
            </select>
        </div>

        <div class="mb-3">
            <label for="responsavel" class="form-label fw-bold">Responsável</label>
            <input type="text" class="form-control" id="responsavel" name="responsavel" required>
            <!-- <label for="responsavel" class="form-label fw-bold">Responsável</label>
            <input type="text" class="form-control" id="responsavel" name="responsavel" required value="<?php echo htmlspecialchars($_SESSION['nome']); ?>" readonly> -->
        </div>

        <div class="mb-3">
            <label for="destinatario" class="form-label fw-bold">Destinatário</label>
            <input type="text" class="form-control" id="destinatario" name="destinatario" required>
        </div>

        <h4 class="mt-4 mb-3 fw-bold">Itens do Termo</h4>
        <div id="itens-container"></div>

        <div class="mb-4">
            <button type="button" class="btn btn-secondary" onclick="adicionarItem()">
                <i class="fas fa-plus"></i> Adicionar Item
            </button>
        </div>

        <div class="d-grid">
        <div id="mensagem" class="mt-3"></div> <!-- Mensagem de retorno -->

        <button type="submit" class="btn btn-success">Salvar Termo</button>

        </div>
    </form>
</div>

<!-- Modal de Identificação -->
<div class="modal fade" id="modalIdentificacao" tabindex="-1" aria-labelledby="modalIdentificacaoLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalIdentificacaoLabel">Inserir Identificação do Item</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="formIdentificacao">
                    <div id="identificacaoInputs"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                <button type="button" class="btn btn-primary" onclick="salvarIdentificacao()">Salvar</button>
            </div>
        </div>
    </div>
</div>






    <script>

$(document).ready(function () {
        $("#searchInput").on("keyup", function () {
            var value = $(this).val().toLowerCase();
            $("table tbody tr").filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
            });
        });
    });
document.getElementById("termoForm").addEventListener("submit", function(event) {
    event.preventDefault(); // Evita o recarregamento da página

    let formData = new FormData(this);

    // Captura os itens de saída dinamicamente
    let itensSaida = [];
    document.querySelectorAll(".item").forEach((row) => {
        let id = row.querySelector("select").value;
        let quantidade = row.querySelector(".quantidade-item").value;
        let itemId = row.dataset.itemId;
        let identificacao = identificacoes[itemId] || [];

        if (id && quantidade > 0) {
            itensSaida.push({ id, quantidade, identificacao });
        }
    });

    // Adiciona os itens ao FormData
    formData.append("itens_saida", JSON.stringify(itensSaida));

    fetch("./api/salvar_termo.php", {
        method: "POST",
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        let mensagemDiv = document.getElementById("mensagem");

        if (data.sucesso) {
            mensagemDiv.innerHTML = `<div class="alert alert-success">${data.mensagem}</div>`;
            document.getElementById("termoForm").reset(); // Limpa o formulário após sucesso
            document.getElementById("itens-container").innerHTML = ""; // Remove os itens adicionados
        } else {
            mensagemDiv.innerHTML = `<div class="alert alert-danger">${data.mensagem}</div>`;
        }
    })
    .catch(error => {
        document.getElementById("mensagem").innerHTML = 
            `<div class="alert alert-danger">Erro ao processar a solicitação: ${error}.</div>`;
    });
});






document.getElementById("secretaria").addEventListener("change", function() {
    var secretariaId = this.value.trim(); // Removendo espaços extras
    var departamentoSelect = document.getElementById("departamento");

    departamentoSelect.innerHTML = '<option value="">Selecione um Departamento</option>';

    if (secretariaId) {
        console.log("ID da Secretaria Selecionada:", secretariaId); // Depuração

        fetch("./api/buscar_departamentos.php?secretaria_id=" + secretariaId)
            .then(response => response.json())
            .then(data => {
                console.log("Departamentos recebidos:", data); // Depuração
                data.forEach(departamento => {
                    var option = document.createElement("option");
                    option.value = departamento.id;
                    option.textContent = departamento.nome;
                    departamentoSelect.appendChild(option);
                });
            })
            .catch(error => console.error("Erro ao buscar departamentos:", error));
    } else {
        console.warn("Nenhuma secretaria selecionada!");
    }
});






let itemCount = 0;
    let identificacoes = {};

    function adicionarItem() {
        const container = document.getElementById('itens-container');
        const newItem = document.createElement('div');
        newItem.classList.add('item', 'mb-3');
        newItem.dataset.itemId = itemCount;

        newItem.innerHTML = `
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Item</label>
                    <select class="form-select" name="itens_saida[${itemCount}][id]" required>
                        <?php foreach ($itens as $item): ?>
                            <option value="<?php echo $item['id_item']; ?>">
                                <?php echo $item['nome'] . ' - ' . $item['modelo']; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Quantidade</label>
                    <input type="number" class="form-control quantidade-item" data-id="${itemCount}" min="1" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-info w-50" id="btn-identificar-${itemCount}" onclick="abrirModalIdentificacao(${itemCount})">Identificar</button>
                    <button type="button" class="btn btn-danger w-50" onclick="removerItem(this)">Remover</button>
                </div>
                <div class="col-12 identificacao-visual mt-2" id="identificacao-${itemCount}"></div>
            </div>
        `;

        container.appendChild(newItem);
        itemCount++;
    }

    function abrirModalIdentificacao(itemId) {
        const quantidadeInput = document.querySelector(`.quantidade-item[data-id='${itemId}']`);
        const quantidade = parseInt(quantidadeInput.value, 10);
        
        if (isNaN(quantidade) || quantidade <= 0) {
            alert("Digite uma quantidade válida antes de identificar o item.");
            return;
        }

        const inputsContainer = document.getElementById('identificacaoInputs');
        inputsContainer.innerHTML = '';

        for (let i = 0; i < quantidade; i++) {
            const inputGroup = document.createElement('div');
            inputGroup.classList.add('mb-3');
            inputGroup.innerHTML = `
                <label class="form-label">Identificação ${i + 1}</label>
                <input type="text" class="form-control identificacao-input" data-item="${itemId}" required>
            `;
            inputsContainer.appendChild(inputGroup);
        }

        if (identificacoes[itemId]) {
            document.querySelectorAll('.identificacao-input').forEach((input, index) => {
                input.value = identificacoes[itemId][index] || '';
            });
        }

        const modalInstance = new bootstrap.Modal(document.getElementById('modalIdentificacao'));
        modalInstance.show();
    }

    function salvarIdentificacao() {
        const inputs = document.querySelectorAll('.identificacao-input');
        const itemId = inputs.length > 0 ? inputs[0].getAttribute("data-item") : null;

        if (!itemId) {
            alert("Erro ao salvar identificações. Tente novamente.");
            return;
        }

        identificacoes[itemId] = [];
        inputs.forEach(input => identificacoes[itemId].push(input.value));

        document.getElementById(`identificacao-${itemId}`).innerHTML = `<strong>Identificações:</strong> ${identificacoes[itemId].join(', ')}`;
        document.getElementById(`btn-identificar-${itemId}`).innerText = "Editar";

        const modalInstance = bootstrap.Modal.getInstance(document.getElementById('modalIdentificacao'));
        modalInstance.hide();
    }

    function removerItem(button) {
        const item = button.closest('.item');
        item.remove();
    }

    document.getElementById('btn-gerar-novo-termo').addEventListener('click', () => {
        document.getElementById('div-termos-cadastrados').style.display = 'none';
        document.getElementById('div-gerar-novo-termo').style.display = 'block';
    });

    document.getElementById('btn-termos-cadastrados').addEventListener('click', () => {
        document.getElementById('div-gerar-novo-termo').style.display = 'none';
        document.getElementById('div-termos-cadastrados').style.display = 'block';
    });
    </script>


    <script>
        let assinaturaSalva = false;
        let canvas = document.getElementById('assinaturaCanvas');
        let ctx = canvas.getContext('2d');
        let drawing = false;

        // Função para começar a desenhar
        function startDrawing(e) {
            drawing = true;
            ctx.beginPath();
            let x, y;
            if (e.type === 'mousedown' || e.type === 'touchstart') {
                x = (e.type === 'mousedown') ? e.offsetX : e.touches[0].clientX - canvas.getBoundingClientRect().left;
                y = (e.type === 'mousedown') ? e.offsetY : e.touches[0].clientY - canvas.getBoundingClientRect().top;
            }
            ctx.moveTo(x, y);
        }

        // Função para desenhar
        function draw(e) {
            if (!drawing) return;
            let x, y;
            if (e.type === 'mousemove' || e.type === 'touchmove') {
                x = (e.type === 'mousemove') ? e.offsetX : e.touches[0].clientX - canvas.getBoundingClientRect().left;
                y = (e.type === 'mousemove') ? e.offsetY : e.touches[0].clientY - canvas.getBoundingClientRect().top;
            }
            ctx.lineTo(x, y);
            ctx.stroke();
        }

        // Função para parar de desenhar
        function stopDrawing() {
            drawing = false;
            ctx.closePath();
        }

        // Eventos para desktop
        canvas.addEventListener('mousedown', startDrawing);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDrawing);
        canvas.addEventListener('mouseleave', stopDrawing);

        // Eventos para dispositivos móveis
        canvas.addEventListener('touchstart', startDrawing);
        canvas.addEventListener('touchmove', draw);
        canvas.addEventListener('touchend', stopDrawing);

        // Função para limpar a assinatura
        function limparAssinatura() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
        }

        // Função para salvar a assinatura
        function salvarAssinatura() {
            const termoId = document.getElementById('modalAssinatura').getAttribute('data-termo-id');
            const assinatura = canvas.toDataURL(); // Converte a assinatura para base64

            fetch('./api/salvar_assinatura.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ termoId, assinatura })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Assinatura salva com sucesso!');
                    location.reload(); // Recarrega a página para atualizar o ícone
                } else {
                    alert('Erro ao salvar a assinatura.');
                }
            })
            .catch(error => {
                console.error('Erro:', error);
            });
        }

        function assinarTermo(termoId) {
            // Limpa o canvas de assinatura
            limparAssinatura();

            // Busca as informações do termo de saída
            fetch(`./api/buscar_termo.php?id=${termoId}`)
                .then(response => response.json())
                .then(data => {
                    console.log("Resposta da API:", data); // Inspecione a resposta no console

                    if (data.success && data.termo) {
                        const termo = data.termo;

                        // Preenche as informações do termo
                        document.getElementById('termo-numero').textContent = termo.numero_termo;
                        document.getElementById('termo-secretaria').textContent = termo.secretaria_nome;
                        document.getElementById('termo-departamento').textContent = termo.departamento_nome;
                        document.getElementById('termo-responsavel').textContent = termo.responsavel;
                        document.getElementById('termo-data-saida').textContent = termo.data_saida;

                        // Preenche a lista de itens
                        const itensLista = document.getElementById('itens-lista');
                        itensLista.innerHTML = ''; // Limpa a lista anterior

                        // Verifica se a propriedade 'itens' existe e é um array
                        if (data.itens && Array.isArray(data.itens)) {
                            data.itens.forEach(item => {
                                const row = document.createElement('tr');
                                row.innerHTML = `
                                    <td>${item.nome} - ${item.modelo}</td>
                                    <td>${item.quantidade}</td>
                                    <td>${item.patrimonio || 'N/A'}</td>
                                `;
                                itensLista.appendChild(row);
                            });
                        } else {
                            console.error("A propriedade 'itens' não foi encontrada ou não é um array.");
                        }

                        // Define o ID do termo no modal para uso posterior
                        document.getElementById('modalAssinatura').setAttribute('data-termo-id', termoId);

                        // Abre o modal
                        new bootstrap.Modal(document.getElementById('modalAssinatura')).show();
                    } else {
                        alert('Erro ao carregar informações do termo: ' + (data.message || 'Dados inválidos.'));
                    }
                })
                .catch(error => {
                    console.error('Erro:', error);
                    alert('Erro ao carregar informações do termo.');
                });
        }
    </script>

    <script>
        // Função para buscar os dados do termo de entrega
        async function fetchTermoData(termoId) {
            const response = await fetch(`./api/gerar_pdf.php?id=${termoId}`);
            const data = await response.json();
            
            if (data.error) {
                alert("Erro ao buscar os dados: " + data.error);
                throw new Error(data.error);
            }
            
            return data;
        }

        // Função para gerar o PDF
        async function gerarPDF(termoId) {
            const { jsPDF } = window.jspdf;
            const doc = new jsPDF('p', 'mm', 'a4');

            // Busca os dados do termo
            const data = await fetchTermoData(termoId);
            const termo = data.termo;
            const itens_saida = data.itens_saida;

            // Adiciona o cabeçalho do termo
            doc.setFontSize(12);
            doc.text('Termo de Responsabilidade', 10, 10);
            doc.text(`Número do Termo: ${termo.numero_termo}`, 10, 20);
            doc.text(`Secretaria: ${termo.secretaria_nome}`, 10, 30);
            doc.text(`Departamento: ${termo.departamento_nome}`, 10, 40);
            doc.text(`Responsável: ${termo.responsavel}`, 10, 50);
            doc.text(`Data de Saída: ${termo.data_saida}`, 10, 60);

            // Adiciona a tabela de itens
            doc.autoTable({
                startY: 70,
                head: [['Item', 'Modelo', 'Quantidade', 'Identificação']],
                body: itens_saida.map(item => [
                    item.item_nome,
                    item.item_modelo,
                    item.quantidade,
                    item.identificacao || 'N/A'
                ])
            });

            // Adiciona a assinatura se existir
            if (termo.assinatura) {
                const assinaturaBase64 = `data:image/png;base64,${termo.assinatura}`;
                doc.addImage(assinaturaBase64, 'PNG', 10, doc.autoTable.previous.finalY + 10, 50, 20);
            }

            // Salva o PDF
            window.open(doc.output('bloburl'), '_blank');

        }

    </script>

    <footer>
        <p>&copy; 2025 SIGEM - Sistema Integrado de Gestão de Materiais</p>
    </footer>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="./js/script.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
</body>
</html>