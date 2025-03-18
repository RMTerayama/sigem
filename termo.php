<?php
session_start(); // Inicia a sessão para acessar o role do usuário

require './api/requisicoesTermo.php'; 
require './api/login-jwt/auth/validate.php';

$role = $_SESSION["usuario_autenticado"]["role"] ?? 'guest'; // Pega o role do usuário ou define como 'guest' se não estiver logado
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIGEM - Sistema de Gestão</title>




    <!-- Normalize CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">

    <!-- Bootstrap CSS (apenas uma versão) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css"
        rel="stylesheet">

    <!-- Estilo personalizado -->
    <link rel="stylesheet" href="./css/styleMain.css">

    <!-- <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/normalize/8.0.1/normalize.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link rel="stylesheet" href="./css/styleMain.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"> -->
</head>

<body>
    <style>
    #div-termos-cadastrados {
        margin-left: 0 !important;
        padding-left: 0 !important;
        width: 100%;
    }

    #div-gerar-novo-termo .bg-light {
        margin-bottom: 10vw;
    }

    .page-item {
        margin-right: 0;
        margin-bottom: 5vw;
    }

    .page-item a,
    .page-item span {
        font-size: 15px;
    }

    table {
        font-size: 13px;
    }

    .table-responsive {
        margin-left: 0 !important;
        padding-left: 0 !important;
        width: 100%;
    }
    #searchInput{
            margin-bottom: 2vw;
        }
    @media only screen and (max-width: 600px) {
        #div-gerar-novo-termo .bg-light {
            margin-bottom: 30vw;
        }

        .paginacao {
            margin-bottom: 20vw;

        }
        #searchInput{
            margin-bottom: 5vw;
        }
    }
    </style>
    <?php include 'navbar.php'; ?>

    <div class="container mt-3 text-center">
        <button id="btn-gerar-novo-termo" class="btn btn-primary">Gerar Novo Termo</button>
        <button id="btn-termos-cadastrados" class="btn btn-secondary">Termos de Entrega</button>

    </div>


    <div class="modal fade" id="modalEnvio" tabindex="-1" aria-labelledby="" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                
                <div class="modal-body">
                    <p id="messageDialog">Arquivo enviado com sucesso</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>


    <div id="div-termos-cadastrados" class="container-fluid mt-5">
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

                            <!-- 🔹 Botão de "Anexo", sempre presente mas oculto se não houver PDF -->
                            <a href="<?php echo !empty($termo['dir_pdf']) ? htmlspecialchars($termo['dir_pdf']) : '#'; ?>" 
                            target="_blank"
                            class="btn btn-info btn-sm anexo-btn"
                            style="<?php echo empty($termo['dir_pdf']) ? 'display: none;' : ''; ?>">
                                <i class="fas fa-file"></i> Anexo
                            </a>

                            <!-- 🔹 O botão recebe um identificador único pelo "data-numero-termo" -->
                            <button class="btn btn-secondary btn-sm button-upload"
                                data-numero-termo="<?php echo htmlspecialchars($termo['numero_termo']); ?>">
                                <i class="fa-solid fa-file-import"></i>
                            </button>

                            <!-- 🔹 Cada botão tem seu próprio input oculto -->
                            <input type="file" class="file-upload" style="display: none;">

                            <button class="btn btn-success btn-sm" onclick="assinarTermo(<?php echo $termo['id']; ?>)">
                                <i class="fas fa-signature"></i> Assinar
                            </button>
                        </td>
                        <td>
                            <!-- 🔹 Ícone de status agora tem uma classe para ser atualizado via JS -->
                            <?php if ($termo['assinado'] || !empty($termo['dir_pdf'])): ?>
                                <i class="fas fa-check-circle text-success status-icon" title="Assinado ou documento anexado"></i>
                            <?php else: ?>
                                <i class="fas fa-times-circle text-danger status-icon" title="Não Assinado e sem anexo"></i>
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
        <nav class="paginacao">
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
    <div class="modal fade" id="modalAssinatura" tabindex="-1" aria-labelledby="modalAssinaturaLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-lg modal-fullscreen-md-down">
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
                        <p><strong>Emissor:</strong> <span id="termo-responsavel"></span></p>
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
                                    <th>Identificação</th>
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
                        <div class="border p-2" style="width: 100%; max-width: 100%;">
                            <canvas id="assinaturaCanvas"
                                style="width: 100%; height: 200px; border: 1px solid #000;"></canvas>
                        </div>
                        <button type="button" class="btn btn-danger mt-2 w-100" onclick="limparAssinatura()">Limpar
                            Assinatura</button>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                    <button type="button" class="btn btn-primary" onclick="salvarAssinatura()">Salvar
                        Assinatura</button>
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
                    <option value="">Selecione uma Departamento</option>
                    <?php foreach ($departamentos as $departamento): ?>
                    <option value="<?php echo htmlspecialchars($departamento['id']); ?>">
                        <?php echo htmlspecialchars($departamento['nome']); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <!-- <label for="responsavel" class="form-label fw-bold">Responsável</label>
                <input type="text" class="form-control" id="responsavel" name="responsavel" required> -->
                 <label for="responsavel" class="form-label fw-bold">Emissor</label>
            <input type="text" class="form-control" id="responsavel" name="responsavel" required value="<?php echo htmlspecialchars($_SESSION["usuario_autenticado"]["nome"]); ?>" disabled> 
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
    <div class="modal fade" id="modalIdentificacao" tabindex="-1" aria-labelledby="modalIdentificacaoLabel"
        aria-hidden="true">
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
    <footer>
        <p>&copy; 2025 SIGEM - Sistema Integrado de Gestão de Materiais</p>
    </footer>

    <!-- jQuery (apenas uma versão) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <!-- Bibliotecas JS externas -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>
    <!-- Script customizado -->
    <script src="./js/scriptTermo.js"></script>
    <script src="./js/scriptUpload.js"></script>

    <!-- CSS do Select2
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    JS do Select2 
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.1.1/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet">
    <script src="./js/scriptTermo.js"></script>                    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.24/jspdf.plugin.autotable.min.js"></script>-!>
</body>

</html>