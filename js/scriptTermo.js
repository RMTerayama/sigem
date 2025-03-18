
document.addEventListener('DOMContentLoaded', function() {
    $('.select2').select2({
        placeholder: "Selecione uma opção",
        allowClear: true
    });
});

$(document).ready(function () {
    // Inicializa o Select2
    if ($.fn.select2) {
        $('.select2').each(function () {
            if (!$(this).data('select2')) {
                $(this).select2({
                    theme: 'bootstrap-5',
                    placeholder: "Selecione ou digite para buscar...",
                    allowClear: true,
                });
            }
        });
    } else {
        console.error("❌ Erro: Select2 não foi carregado corretamente!");
    }

    // Filtro de pesquisa na tabela
    $("#searchInput").on("keyup", function () {
        const value = $(this).val().toLowerCase();
        $("table tbody tr").filter(function () {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
    });

    // Evento de envio do formulário de termo
    document.getElementById("termoForm").addEventListener("submit", function (event) {
        event.preventDefault();

        const formData = new FormData(this);
        const itensSaida = [];

        // Captura os itens de saída dinamicamente
        document.querySelectorAll(".item").forEach((row) => {
            const id = row.querySelector("select").value;
            const quantidade = row.querySelector(".quantidade-item").value;
            const itemId = row.dataset.itemId;
            const identificacao = identificacoes[itemId] || [];

            if (id && quantidade > 0) {
                itensSaida.push({ id, quantidade, identificacao });
            }
        });

        formData.append("itens_saida", JSON.stringify(itensSaida));

        fetch("/sigem/api/salvar_termo.php", {
            method: "POST",
            body: formData,
        })
            .then(response => response.text()) // Captura a resposta bruta antes de converter
            .then(text => {
                console.log("🔹 Resposta do servidor (RAW):", text); // Depuração
        
                try {
                    let data = JSON.parse(text); // Tenta converter para JSON
                    console.log("✅ Resposta JSON:", data);
        
                    const mensagemDiv = document.getElementById("mensagem");
        
                    if (data.sucesso) {
                        mensagemDiv.innerHTML = `<div class="alert alert-success">${data.mensagem}</div>`;
                        document.getElementById("termoForm").reset();
                        document.getElementById("itens-container").innerHTML = "";
                    } else {
                        mensagemDiv.innerHTML = `<div class="alert alert-danger">${data.mensagem}</div>`;
                    }
                } catch (error) {
                    console.error("❌ Erro ao converter resposta para JSON:", error, "Resposta recebida:", text);
                    document.getElementById("mensagem").innerHTML = `<div class="alert alert-danger">
                        Erro inesperado: Resposta inválida do servidor. Verifique o console para mais detalhes.
                    </div>`;
                }
            })
            .catch(error => {
                console.error("❌ Erro na requisição:", error);
                document.getElementById("mensagem").innerHTML = `<div class="alert alert-danger">Erro ao processar a solicitação.</div>`;
            });
        
    });

    // Evento de mudança na seleção de secretaria
    document.getElementById("secretaria").addEventListener("change", function () {
        const secretariaId = this.value.trim();
        const departamentoSelect = document.getElementById("departamento");

        departamentoSelect.innerHTML = '<option value="">Selecione um Departamento</option>';

        if (secretariaId) {
            fetch(`/sigem/api/buscar_departamentos.php?secretaria_id=${secretariaId}`)
                .then((response) => response.json())
                .then((data) => {
                    if (data.error) {
                        console.warn("Erro do servidor:", data.error);
                        return;
                    }
                    data.forEach((departamento) => {
                        const option = document.createElement("option");
                        option.value = departamento.id;
                        option.textContent = departamento.nome;
                        departamentoSelect.appendChild(option);
                    });
                })
                .catch((error) => console.error("Erro ao buscar departamentos:", error));
        }
    });

    // Botões para alternar entre as seções
    document.getElementById("btn-gerar-novo-termo").addEventListener("click", () => {
        document.getElementById("div-termos-cadastrados").style.display = "none";
        document.getElementById("div-gerar-novo-termo").style.display = "block";
    });

    document.getElementById("btn-termos-cadastrados").addEventListener("click", () => {
        // document.getElementById("div-gerar-novo-termo").style.display = "none";
        // document.getElementById("div-termos-cadastrados").style.display = "block";
        window.location.reload();

    });
});

// Lógica de adição e remoção de itens
let itemCount = 0;
let identificacoes = {};

function adicionarItem() {
    const container = document.getElementById("itens-container");
    if (!container) {
        console.error("Container 'itens-container' não encontrado.");
        return;
    }

    const newItem = document.createElement("div");
    newItem.classList.add("item", "mb-3");
    newItem.dataset.itemId = itemCount;

    fetch('/sigem/api/buscar_itens.php')
        .then(response => response.json())
        .then(itens => {
            if (itens.error) {
                console.error("Erro no PHP:", itens.error);
                return;
            }

            let options = '<option value=""></option>';
            itens.forEach(item => {
                options += `<option value="${item.id_item}">${item.nome} - ${item.modelo}</option>`;
            });

            newItem.innerHTML = `
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Item</label>
                        <select class="form-select select2" name="itens_saida[${itemCount}][id]" required>
                            ${options}
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

            // Inicializa o Select2 para o novo item
            setTimeout(() => {
                if ($.fn.select2) {
                    $(newItem).find(".select2").select2({
                        theme: "bootstrap-5",
                        placeholder: "Selecione ou digite para buscar...",
                        allowClear: true,
                    });
                }
            }, 50);

            itemCount++;
        })
        .catch(error => {
            console.error("Erro ao carregar itens:", error);
        });
}


function abrirModalIdentificacao(itemId) {
    const quantidadeInput = document.querySelector(`.quantidade-item[data-id='${itemId}']`);
    const quantidade = parseInt(quantidadeInput.value, 10);

    if (isNaN(quantidade) || quantidade <= 0) {
        alert("Digite uma quantidade válida antes de identificar o item.");
        return;
    }

    const inputsContainer = document.getElementById("identificacaoInputs");
    inputsContainer.innerHTML = "";

    for (let i = 0; i < quantidade; i++) {
        const inputGroup = document.createElement("div");
        inputGroup.classList.add("mb-3");
        inputGroup.innerHTML = `
            <label class="form-label">Identificação ${i + 1}</label>
            <input type="text" class="form-control identificacao-input" data-item="${itemId}" required>
        `;
        inputsContainer.appendChild(inputGroup);
    }

    if (identificacoes[itemId]) {
        document.querySelectorAll(".identificacao-input").forEach((input, index) => {
            input.value = identificacoes[itemId][index] || "";
        });
    }

    new bootstrap.Modal(document.getElementById("modalIdentificacao")).show();
}

function salvarIdentificacao() {
    const inputs = document.querySelectorAll(".identificacao-input");
    const itemId = inputs.length > 0 ? inputs[0].getAttribute("data-item") : null;

    if (!itemId) {
        alert("Erro ao salvar identificações. Tente novamente.");
        return;
    }

    identificacoes[itemId] = [];
    inputs.forEach((input) => identificacoes[itemId].push(input.value));

    document.getElementById(`identificacao-${itemId}`).innerHTML = `<strong>Identificações:</strong> ${identificacoes[itemId].join(", ")}`;
    document.getElementById(`btn-identificar-${itemId}`).innerText = "Editar";

    bootstrap.Modal.getInstance(document.getElementById("modalIdentificacao")).hide();
}

function removerItem(button) {
    const item = button.closest(".item");
    item.remove();
}

// Lógica de assinatura
let canvas = document.getElementById("assinaturaCanvas");
let ctx = canvas.getContext("2d");
let drawing = false;

function ajustarTamanhoCanvas() {
    const rect = canvas.getBoundingClientRect();
    canvas.width = rect.width;
    canvas.height = rect.height;
    limparAssinatura();
}

$("#modalAssinatura").on("shown.bs.modal", ajustarTamanhoCanvas);

function startDrawing(e) {
    e.preventDefault();
    drawing = true;
    ctx.beginPath();
    const { x, y } = getCoordenadas(e);
    ctx.moveTo(x, y);
}

function draw(e) {
    if (!drawing) return;
    e.preventDefault();
    const { x, y } = getCoordenadas(e);
    ctx.lineTo(x, y);
    ctx.stroke();
}

function stopDrawing() {
    drawing = false;
    ctx.closePath();
}

function getCoordenadas(e) {
    const rect = canvas.getBoundingClientRect();
    const scaleX = canvas.width / rect.width;
    const scaleY = canvas.height / rect.height;

    let x, y;
    if (e.type.includes("mouse")) {
        x = (e.clientX - rect.left) * scaleX;
        y = (e.clientY - rect.top) * scaleY;
    } else if (e.type.includes("touch")) {
        x = (e.touches[0].clientX - rect.left) * scaleX;
        y = (e.touches[0].clientY - rect.top) * scaleY;
    }
    return { x, y };
}

function limparAssinatura() {
    ctx.clearRect(0, 0, canvas.width, canvas.height);
}

canvas.addEventListener("mousedown", startDrawing);
canvas.addEventListener("mousemove", draw);
canvas.addEventListener("mouseup", stopDrawing);
canvas.addEventListener("mouseleave", stopDrawing);

canvas.addEventListener("touchstart", startDrawing, { passive: false });
canvas.addEventListener("touchmove", draw, { passive: false });
canvas.addEventListener("touchend", stopDrawing);

window.addEventListener("resize", ajustarTamanhoCanvas);

function salvarAssinatura() {
    const termoId = document.getElementById("modalAssinatura").getAttribute("data-termo-id");
    const assinatura = canvas.toDataURL();

    fetch("/sigem/api/salvar_assinatura.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ termoId, assinatura }),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                alert("Assinatura salva com sucesso!");
                location.reload();
            } else {
                alert("Erro ao salvar a assinatura.");
            }
        })
        .catch((error) => console.error("Erro:", error));
}

function assinarTermo(termoId) {
    limparAssinatura();

    fetch(`/sigem/api/buscar_termo.php?id=${termoId}`)
        .then((response) => response.json())
        .then((data) => {
            if (data.success && data.termo) {
                const termo = data.termo;

                document.getElementById("termo-numero").textContent = termo.numero_termo;
                document.getElementById("termo-secretaria").textContent = termo.secretaria_nome;
                document.getElementById("termo-departamento").textContent = termo.departamento_nome;
                document.getElementById("termo-responsavel").textContent = termo.responsavel;
                document.getElementById("termo-data-saida").textContent = termo.data_saida;

                const itensLista = document.getElementById("itens-lista");
                itensLista.innerHTML = "";

                if (data.itens && Array.isArray(data.itens)) {
                    data.itens.forEach((item) => {
                        const row = document.createElement("tr");
                        row.innerHTML = `
                            <td>${item.nome} - ${item.modelo}</td>
                            <td>${item.quantidade}</td>
                            <td>${item.patrimonio || "N/A"}</td>
                        `;
                        itensLista.appendChild(row);
                    });
                }

                document.getElementById("modalAssinatura").setAttribute("data-termo-id", termoId);
                new bootstrap.Modal(document.getElementById("modalAssinatura")).show();
            } else {
                alert("Erro ao carregar informações do termo: " + (data.message || "Dados inválidos."));
            }
        })
        .catch((error) => console.error("Erro:", error));
}

// Lógica de geração de PDF
async function gerarPDF(termoId) {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({ orientation: "p", unit: "mm", format: "a4", compress: true });

    try {
        const response = await fetch(`/sigem/api/buscar_termo.php?id=${termoId}`);
        const data = await response.json();
        if (!data.success) throw new Error(data.message || "Erro ao buscar os dados do termo.");

        const termo = data.termo;
        const itens_saida = data.itens;

        termo.data_saida = new Intl.DateTimeFormat("pt-BR", {
            day: "2-digit",
            month: "2-digit",
            year: "numeric",
        }).format(new Date(termo.data_saida));

        // 🔹 Comprimir logo mantendo qualidade
        const logoSrc = "/sigem/src/logopref.png";
        const logoBase64 = await comprimirImagem(logoSrc, 600, 0.92, "image/png");
        doc.addImage(logoBase64, "PNG", 80, 10, 50, 20);

        doc.setFont("times", "normal").setFontSize(16).text("TERMO DE RESPONSABILIDADE", 105, 40, { align: "center" });

        doc.setFontSize(12);
        let posY = 50; // Começa no mesmo ponto
        let espacamento = 7; // 🔹 Reduzido de 10 para 6mm

        doc.text(`Número do Termo: ${termo.numero_termo}`, 10, posY);
        doc.text(`Secretaria: ${termo.secretaria_nome}`, 10, (posY += espacamento));
        doc.text(`Departamento: ${termo.departamento_nome}`, 10, (posY += espacamento));
        doc.text(`Responsável: ${termo.responsavel}`, 10, (posY += espacamento));
        doc.text(`Data de Saída: ${termo.data_saida}`, 10, (posY += espacamento));


        doc.setFontSize(10);
        doc.text(
            "O equipamento entregue é um bem público, destinado exclusivamente para fins profissionais.\n\n" +
            "Qualquer dúvida quanto ao uso, manutenção ou instalação de softwares deve ser comunicada imediatamente ao Departamento de Tecnologia da prefeitura.\n\n" +
            "A instalação de softwares sem autorização formal do setor de tecnologia é estritamente proibida e pode acarretar sanções administrativas e legais.\n\n" +
            "Este termo reforça a obrigatoriedade do cumprimento das normas de uso, garantindo a integridade do equipamento e a conformidade com as diretrizes municipais vigentes.",
            10, 100, { maxWidth: 190 }
        );


        doc.autoTable({
            startY: 150,
            head: [["Item", "Modelo", "Qtd", "Identificação"]],
            body: itens_saida.map(item => [item.nome, item.modelo, item.quantidade, item.identificacao || "N/A"]),
            margin: { top: 5, bottom: 5 }, // Reduz margens para economia de espaço
            styles: { fontSize: 11 }, // Mantém uma fonte legível e compacta
            columnStyles: {
                0: { cellWidth: 45 }, // 🔹 Ajusta a largura da coluna "Item"
                1: { cellWidth: 45 }, // 🔹 Ajusta a largura da coluna "Modelo"
                2: { cellWidth: 10 }, // 🔹 Mantém "Quantidade" menor
                3: { cellWidth: 81.80 }, // 🔹 Ajusta "Identificação"
            },
        });
        
        if (termo.assinatura && termo.assinatura.startsWith("iVBOR")) { 
            const assinaturaBase64 = await comprimirImagem(`data:image/png;base64,${termo.assinatura}`, 600, 0.92, "image/png");
            doc.addImage(assinaturaBase64, "PNG", 80, 270, 50, 20);
        }

        doc.setFontSize(8).setTextColor(150);
        doc.text("Este é um documento oficial...", 105, 290, { align: "center" });

        const blob = doc.output("blob");
        const compressedPDF = new Blob([blob], { type: "application/pdf" });
        window.open(URL.createObjectURL(compressedPDF), "_blank");

    } catch (error) {
        console.error("❌ Erro ao gerar PDF:", error);
    }
}

// 🔹 Melhor função de compressão de imagem
function comprimirImagem(src, maxWidth = 600, quality = 0.92, format = "image/png") {
    return new Promise((resolve, reject) => {
        const img = new Image();
        img.src = src;
        img.onload = function () {
            const canvas = document.createElement("canvas");
            const ctx = canvas.getContext("2d");

            const scaleFactor = Math.min(1, maxWidth / img.width);
            canvas.width = img.width * scaleFactor;
            canvas.height = img.height * scaleFactor;

            ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
            resolve(canvas.toDataURL(format, quality)); 
        };
        img.onerror = reject;
    });
}



async function fetchTermoData(termoId) {
    try {
        const response = await fetch(`/sigem/api/buscar_termo.php?id=${termoId}`);
        const data = await response.json();

        if (!data.success) {
            throw new Error(data.message || "Erro ao buscar os dados do termo.");
        }

        return data;
    } catch (error) {
        console.error("❌ Erro ao buscar termo:", error);
        throw error;
    }
}


function abrirPDF(caminhoArquivo) {
    if (caminhoArquivo) {
        window.open(`/sigem/termos/${caminhoArquivo}`, '_blank');
    } else {
        alert("Nenhum PDF anexado para este termo.");
    }
}

