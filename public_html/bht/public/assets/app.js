const moduleCards = Array.from(document.querySelectorAll('[data-module-card]'));
const progressChip = document.querySelector('[data-progress-chip]');

const setModuleState = (card, open) => {
    const body = card.querySelector('[data-module-body]');
    const toggle = card.querySelector('[data-module-toggle]');
    if (!body || !toggle) return;
    card.classList.toggle('is-open', open);
    body.hidden = !open;
    toggle.textContent = open ? 'Collapse' : 'Open';
};

const updateProgress = () => {
    if (!progressChip) return;
    const open = moduleCards.filter((card) => card.classList.contains('is-open')).length;
    progressChip.textContent = `${open} / ${moduleCards.length} modules open`;
};

moduleCards.forEach((card) => {
    setModuleState(card, card.classList.contains('is-open'));
    card.querySelector('[data-module-toggle]')?.addEventListener('click', () => {
        setModuleState(card, !card.classList.contains('is-open'));
        updateProgress();
    });
});

document.querySelector('[data-expand-all]')?.addEventListener('click', () => {
    moduleCards.forEach((card) => setModuleState(card, true));
    updateProgress();
});

document.querySelector('[data-collapse-all]')?.addEventListener('click', () => {
    moduleCards.forEach((card) => setModuleState(card, false));
    updateProgress();
});

document.querySelectorAll('.section-nav a').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) return;
        event.preventDefault();
        setModuleState(target, true);
        updateProgress();
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

updateProgress();
