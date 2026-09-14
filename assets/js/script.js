document.addEventListener("DOMContentLoaded", function () {
    const container = document.getElementById("servicos-container");
    const adicionar = document.getElementById("adicionar-servico");
    const total = document.getElementById("total");

    if (!container || !adicionar || !total) return;

    function calcularEncadernacao(folhas) {
        if (folhas <= 0) return 0;
        return Math.ceil(folhas / 50) * 30;
    }

    function calcularTotal() {
        let totalPedido = 0;

        document.querySelectorAll(".item-pedido").forEach(function (item) {
            const select = item.querySelector(".servico-select");
            const quantidade = item.querySelector(".quantidade");
            const subtotal = item.querySelector(".subtotal");

            if (!select || !quantidade || !subtotal) return;

            const option = select.options[select.selectedIndex];
            if (!option || !option.value) {
                subtotal.textContent = "0,00 MT";
                return;
            }

            const nome = option.textContent.trim();
            const preco = parseFloat(option.dataset.preco) || 0;
            const qtd = parseFloat(quantidade.value) || 0;

            let valor = 0;
            if (nome === "Encadernação") {
                valor = calcularEncadernacao(qtd);
            } else {
                valor = qtd * preco;
            }

            subtotal.textContent = valor.toFixed(2).replace(".", ",") + " MT";
            totalPedido += valor;
        });

        total.textContent = totalPedido.toFixed(2).replace(".", ",") + " MT";
    }

    function adicionarEventos(item) {
        item.querySelector(".servico-select").addEventListener("change", calcularTotal);
        item.querySelector(".quantidade").addEventListener("input", calcularTotal);
    }

    const primeiro = document.querySelector(".item-pedido");
    if (primeiro) {
        adicionarEventos(primeiro);
    }

    adicionar.addEventListener("click", function () {
        const novo = primeiro.cloneNode(true);
        novo.querySelector(".servico-select").value = "";
        novo.querySelector(".quantidade").value = "";
        novo.querySelector(".subtotal").textContent = "0,00 MT";

        container.appendChild(novo);
        adicionarEventos(novo);
    });
});