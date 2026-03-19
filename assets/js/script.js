document.addEventListener("DOMContentLoaded", () => {

    // ============================================================
    //  Filtri categoria
    // ============================================================
    const btnFiltri = document.querySelectorAll('.btn-filtro');
    const cards = document.querySelectorAll('.griglia-prodotti .card');
    const msgVuoti = document.querySelector('.filtri-vuoti');

    if (btnFiltri.length > 0) {
        btnFiltri.forEach(btn => {
            btn.addEventListener('click', function () {
                const categoriaSelezionata = this.dataset.categoria;

                // Aggiorna bottone attivo
                btnFiltri.forEach(b => b.classList.remove('attivo'));
                this.classList.add('attivo');

                // Mostra/nascondi le card
                let visibili = 0;
                cards.forEach(card => {
                    const corrisponde =
                        categoriaSelezionata === 'tutti' ||
                        card.dataset.categoria === categoriaSelezionata;

                    if (corrisponde) {
                        card.classList.remove('nascosta');
                        card.classList.add('visibile');
                        visibili++;
                    } else {
                        card.classList.add('nascosta');
                        card.classList.remove('visibile');
                    }
                });

                // Messaggio "nessun prodotto" se la categoria è vuota
                if (msgVuoti) {
                    msgVuoti.style.display = visibili === 0 ? 'block' : 'none';
                }
            });
        });
    }


    // ============================================================
    //  Bottoni acquisto
    // ============================================================
    const bottoniAcquisto = document.querySelectorAll('.btn-acquista');

    bottoniAcquisto.forEach(bottone => {
        bottone.addEventListener('click', function () {
            const prodottoId = this.getAttribute('data-id');
            alert(`Hai cliccato "Acquista" sul prodotto con ID: ${prodottoId}.\n\nIn futuro, questo tasto aprirà la pagina di pagamento di Stripe!`);
            // Esempio logica futura:
            // window.location.href = `api/stripe-checkout.php?id=${prodottoId}`;
        });
    });


    // ============================================================
    //  Carousel immagini
    // ============================================================
    const carousel = document.querySelector('.carousel');
    if (!carousel) return; // se non siamo sulla pagina prodotto, esci

    const slides = carousel.querySelectorAll('.carousel-slide');
    const dots = carousel.querySelectorAll('.carousel-dot');
    const btnPrev = carousel.querySelector('.carousel-prev');
    const btnNext = carousel.querySelector('.carousel-next');

    // Nessun carousel se c'è solo una foto
    if (slides.length <= 1) return;

    let indiceCorrente = 0;
    let intervallo;

    // --- Vai a una slide specifica ---
    function vaiASlide(nuovoIndice) {
        // Rimuovi classe attiva dalla slide e dal dot correnti
        slides[indiceCorrente].classList.remove('attiva');
        dots[indiceCorrente]?.classList.remove('attivo');

        // Calcola il nuovo indice (ciclico)
        indiceCorrente = (nuovoIndice + slides.length) % slides.length;

        // Attiva la nuova slide e il nuovo dot
        slides[indiceCorrente].classList.add('attiva');
        dots[indiceCorrente]?.classList.add('attivo');
    }

    // --- Autoplay ---
    const intervalloDurata = parseInt(carousel.dataset.autoplay) || 5000;

    function avviaAutoplay() {
        clearInterval(intervallo); // cancella SEMPRE il timer precedente prima di crearne uno
        intervallo = setInterval(() => {
            vaiASlide(indiceCorrente + 1);
        }, intervalloDurata);
    }

    function resetAutoplay() {
        avviaAutoplay(); // clearInterval è già dentro avviaAutoplay
    }

    // --- Frecce ---
    btnPrev.addEventListener('click', () => {
        vaiASlide(indiceCorrente - 1);
        resetAutoplay(); // resetta il timer quando l'utente clicca
    });

    btnNext.addEventListener('click', () => {
        vaiASlide(indiceCorrente + 1);
        resetAutoplay();
    });

    // --- Puntini ---
    dots.forEach((dot, i) => {
        dot.addEventListener('click', () => {
            vaiASlide(i);
            resetAutoplay();
        });
    });

    // --- Swipe touch (per mobile) ---
    let touchStartX = 0;

    carousel.addEventListener('touchstart', e => {
        touchStartX = e.changedTouches[0].clientX;
    }, { passive: true });

    carousel.addEventListener('touchend', e => {
        const diff = touchStartX - e.changedTouches[0].clientX;
        if (Math.abs(diff) > 50) {          // soglia minima 50px
            diff > 0 ? vaiASlide(indiceCorrente + 1) : vaiASlide(indiceCorrente - 1);
            resetAutoplay();
        }
    }, { passive: true });

    // --- Pausa autoplay al passaggio del mouse ---
    carousel.addEventListener('mouseenter', () => clearInterval(intervallo));
    carousel.addEventListener('mouseleave', avviaAutoplay);

    // --- Avvia ---
    avviaAutoplay();

});