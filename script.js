document.addEventListener('DOMContentLoaded', () => {
    // 1. Definição de variáveis (dentro do escopo do DOMContentLoaded)
    const btnEmp = document.getElementById('btn-aba-emp');
    const btnDev = document.getElementById('btn-aba-dev');
    const btnPag = document.getElementById('btn-aba-pag');
    const secEmp = document.getElementById('secao-emprestimo');
    const secDev = document.getElementById('secao-devolucoes');
    const secPag = document.getElementById('secao-pagamento');
    const subtitle = document.getElementById('card-subtitle');
    const card = document.getElementById('main-card');
    const selectPagamento = document.getElementById('select-pagamento');
    const metodoPagamento = document.getElementById('metodo_pagamento');

    // 2. Função de troca de abas
    function trocarAba(aba) {
        const secoes = [secEmp, secDev, secPag];

        // Remove a classe de animação e esconde todas
        secoes.forEach(s => {
            s.style.display = "none";
            s.classList.remove('fade-in-active');
        });

        [btnEmp, btnDev, btnPag].forEach(b => b.classList.remove('active'));

        let secaoAtiva;
        if (aba === 'dev') {
            btnDev.classList.add('active');
            subtitle.innerText = "Devoluções e Atrasos";
            secaoAtiva = secDev;
            card.style.maxWidth = "800px";
        } else if (aba === 'pag') {
            btnPag.classList.add('active');
            subtitle.innerText = "Liquidar Multas";
            secaoAtiva = secPag;
            card.style.maxWidth = "450px";
        } else {
            btnEmp.classList.add('active');
            subtitle.innerText = "Novo Empréstimo";
            secaoAtiva = secEmp;
            card.style.maxWidth = "450px";
        }

        // Exibe e aplica animação
        secaoAtiva.style.display = "block";
        secaoAtiva.classList.add('fade-in-active');
    }

    // 3. Funções globais (atribuídas ao objeto window para o HTML acessá-las)
    window.gerenciarMetodosPagamento = function () {
        const metodo = metodoPagamento.value;
        document.getElementById('area-pix').style.display = (metodo === 'pix') ? 'block' : 'none';
        document.getElementById('area-cartao').style.display = (metodo === 'cartao') ? 'block' : 'none';
    };

    window.atualizarValorMulta = function () {
        const valor = selectPagamento.options[selectPagamento.selectedIndex].getAttribute('data-valor');
        if (valor) console.log("Multa selecionada: R$ " + valor);
    };

    // 4. Event Listeners
    btnDev.addEventListener('click', () => trocarAba('dev'));
    btnEmp.addEventListener('click', () => trocarAba('emp'));
    btnPag.addEventListener('click', () => trocarAba('pag'));

    // 5. Lógica do Toast
    const toast = document.querySelector('.toast-message');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 4000);
    }
});