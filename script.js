document.addEventListener('DOMContentLoaded', () => {
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

    function trocarAba(aba) {
        [btnEmp, btnDev, btnPag].forEach(b => b.classList.remove('active'));
        [secEmp, secDev, secPag].forEach(s => s.style.display = "none");

        if (aba === 'dev') {
            btnDev.classList.add('active');
            subtitle.innerText = "Devoluções e Atrasos";
            secDev.style.display = "block";
            card.style.maxWidth = "800px";
        } else if (aba === 'pag') {
            btnPag.classList.add('active');
            subtitle.innerText = "Liquidar Multas";
            secPag.style.display = "block";
            card.style.maxWidth = "450px";
        } else {
            btnEmp.classList.add('active');
            subtitle.innerText = "Novo Empréstimo";
            secEmp.style.display = "block";
            card.style.maxWidth = "450px";
        }
    }

    window.gerenciarMetodosPagamento = function () {
        const metodo = metodoPagamento.value;
        document.getElementById('area-pix').style.display = (metodo === 'pix') ? 'block' : 'none';
        document.getElementById('area-cartao').style.display = (metodo === 'cartao') ? 'block' : 'none';
    };

    window.atualizarValorMulta = function () {
        const valor = selectPagamento.options[selectPagamento.selectedIndex].getAttribute('data-valor');
        if (valor) console.log("Multa selecionada: R$ " + valor);
    };

    btnDev.addEventListener('click', () => trocarAba('dev'));
    btnEmp.addEventListener('click', () => trocarAba('emp'));
    btnPag.addEventListener('click', () => trocarAba('pag'));

    const toast = document.querySelector('.toast-message');
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => toast.style.display = 'none', 500);
        }, 4000);
    }
});