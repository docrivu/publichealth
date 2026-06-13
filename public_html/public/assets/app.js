document.querySelectorAll('[data-repeater]').forEach((repeater) => {
    const addButton = repeater.querySelector('[data-add-row]');
    const tbody = repeater.querySelector('[data-rows]');
    const template = repeater.querySelector('template');

    const toggleFieldCell = (input, visible, hiddenHint = '') => {
        if (!input) {
            return;
        }

        const cell = input.closest('td');
        if (cell) {
            cell.classList.toggle('field-hidden', !visible);
            cell.classList.toggle('cell-muted', !visible);
        }

        input.disabled = !visible;
        if (input.tagName === 'SELECT') {
            input.required = false;
        }

        if (!visible) {
            input.value = '';
        }

        if (hiddenHint && input.tagName === 'INPUT') {
            input.placeholder = visible ? input.dataset.defaultPlaceholder || '' : hiddenHint;
        }
    };

    const updateHouseholdMemberVisibility = () => {
        const familyType = document.querySelector('[name="family_type"]')?.value || '';
        document.querySelectorAll('[data-household-member-row]').forEach((row) => {
            const ageInput = row.querySelector('[data-member-age]');
            const relationInput = row.querySelector('[data-relation-to-mother]');
            if (!ageInput || !relationInput) {
                return;
            }

            const age = parseFloat(ageInput.value);
            const shouldShow = familyType === 'Joint' && !Number.isNaN(age) && age < 5;
            relationInput.disabled = !shouldShow;
            relationInput.closest('td')?.classList.toggle('cell-muted', !shouldShow);
            relationInput.closest('td')?.classList.toggle('field-hidden', false);
            relationInput.placeholder = shouldShow ? 'Specify mother' : 'Only for U5 in joint family';

            if (!shouldShow) {
                relationInput.value = '';
            }
        });
    };

    const updateEligibleCoupleVisibility = () => {
        document.querySelectorAll('[data-eligible-couple-row]').forEach((row) => {
            const menstruation = row.querySelector('[data-ec-menstruation]');
            const pregnancyLam = row.querySelector('[data-ec-pregnancy-lam]');
            const wantBaby = row.querySelector('[data-ec-want-baby]');
            const usingFp = row.querySelector('[data-ec-using-fp]');
            const reasonNotFp = row.querySelector('[data-ec-reason-not-fp]');
            const methodCategory = row.querySelector('[data-ec-method-category]');
            const temporaryMethod = row.querySelector('[data-ec-temporary-method]');
            const sterilization = row.querySelector('[data-ec-sterilization]');
            const childrenSterilization = row.querySelector('[data-ec-children-sterilization]');
            const fpSource = row.querySelector('[data-ec-fp-source]');

            if (!menstruation) {
                return;
            }

            const menstruationValue = menstruation.value;
            const showPregnancyLam = menstruationValue === 'No';
            const showWantBaby = menstruationValue === 'Yes';
            const showUsingFp = showWantBaby && wantBaby?.value === 'No';
            const showReasonNotFp = showUsingFp && usingFp?.value === 'No';
            const showMethodCategory = showUsingFp && usingFp?.value === 'Yes';
            const showTemporaryMethod = showMethodCategory && methodCategory?.value === 'Temporary';
            const showSterilization = showMethodCategory && methodCategory?.value === 'Permanent';

            toggleFieldCell(pregnancyLam, showPregnancyLam, 'Shown when menstruation is No');
            toggleFieldCell(wantBaby, showWantBaby, 'Shown when menstruation is Yes');
            toggleFieldCell(usingFp, showUsingFp, 'Shown when family does not want baby soon');
            toggleFieldCell(reasonNotFp, showReasonNotFp, 'Shown when FP is not used');
            toggleFieldCell(methodCategory, showMethodCategory, 'Shown when FP is used');
            toggleFieldCell(temporaryMethod, showTemporaryMethod, 'Shown for temporary method');
            toggleFieldCell(sterilization, showSterilization, 'Shown for permanent method');
            toggleFieldCell(childrenSterilization, showSterilization, 'Shown for permanent method');
            toggleFieldCell(fpSource, showMethodCategory, 'Shown when FP is used');
        });
    };

    const bindRow = (row) => {
        row.querySelector('[data-remove-row]')?.addEventListener('click', () => {
            row.remove();
            updateHouseholdMemberVisibility();
            updateEligibleCoupleVisibility();
        });

        row.querySelectorAll('[data-member-age], [data-relation-to-mother]').forEach((input) => {
            input.addEventListener('input', updateHouseholdMemberVisibility);
            input.addEventListener('change', updateHouseholdMemberVisibility);
        });

        row.querySelectorAll('[data-ec-menstruation], [data-ec-pregnancy-lam], [data-ec-want-baby], [data-ec-using-fp], [data-ec-method-category]').forEach((input) => {
            input.addEventListener('input', updateEligibleCoupleVisibility);
            input.addEventListener('change', updateEligibleCoupleVisibility);
        });

        row.querySelectorAll('input[placeholder]').forEach((input) => {
            input.dataset.defaultPlaceholder = input.getAttribute('placeholder') || '';
        });

        updateHouseholdMemberVisibility();
        updateEligibleCoupleVisibility();
    };

    const addRow = () => {
        const index = tbody.children.length;
        const html = template.innerHTML.replaceAll('__INDEX__', String(index));
        const wrapper = document.createElement('tbody');
        wrapper.innerHTML = html.trim();
        const row = wrapper.firstElementChild;
        tbody.appendChild(row);
        bindRow(row);
    };

    addButton.addEventListener('click', addRow);
    addRow();
});

const familyTypeControl = document.querySelector('[name="family_type"]');
if (familyTypeControl) {
    familyTypeControl.addEventListener('change', () => {
        document.querySelectorAll('[data-member-age]').forEach((input) => input.dispatchEvent(new Event('change')));
    });
}

const moduleCards = Array.from(document.querySelectorAll('[data-module-card]'));
const progressChip = document.querySelector('[data-progress-chip]');
const isPhoneLayout = () => window.matchMedia('(max-width: 768px)').matches;

const setModuleState = (card, open) => {
    const body = card.querySelector('[data-module-body]');
    const toggle = card.querySelector('[data-module-toggle]');
    if (!body || !toggle) {
        return;
    }

    card.classList.toggle('is-open', open);
    body.hidden = !open;
    toggle.textContent = open ? 'Collapse' : 'Open';
};

const updateModuleProgress = () => {
    if (!progressChip) {
        return;
    }

    const openCount = moduleCards.filter((card) => card.classList.contains('is-open')).length;
    progressChip.textContent = `${openCount} / ${moduleCards.length} modules opened`;
};

moduleCards.forEach((card) => {
    const body = card.querySelector('[data-module-body]');
    const toggle = card.querySelector('[data-module-toggle]');
    const shouldStartOpen = card.classList.contains('is-open');

    if (body && toggle) {
        setModuleState(card, shouldStartOpen);
        toggle.addEventListener('click', () => {
            const nextState = !card.classList.contains('is-open');
            if (nextState && isPhoneLayout()) {
                moduleCards.forEach((otherCard) => {
                    if (otherCard !== card) {
                        setModuleState(otherCard, false);
                    }
                });
            }
            setModuleState(card, nextState);
            if (nextState && isPhoneLayout()) {
                card.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
            updateModuleProgress();
        });
    }
});

document.querySelector('[data-expand-all]')?.addEventListener('click', () => {
    moduleCards.forEach((card) => setModuleState(card, true));
    updateModuleProgress();
});

document.querySelector('[data-collapse-all]')?.addEventListener('click', () => {
    moduleCards.forEach((card) => setModuleState(card, false));
    updateModuleProgress();
});

updateModuleProgress();

const formSections = document.querySelectorAll('[data-form-section]');
if (formSections.length > 0) {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                formSections.forEach((section) => section.classList.remove('is-active'));
                entry.target.classList.add('is-active');
            }
        });
    }, { threshold: 0.2 });

    formSections.forEach((section) => observer.observe(section));
}

document.querySelectorAll('.section-nav a').forEach((link) => {
    link.addEventListener('click', (event) => {
        const target = document.querySelector(link.getAttribute('href'));
        if (!target) {
            return;
        }

        event.preventDefault();
        if (target.matches('[data-module-card]') && !target.classList.contains('is-open')) {
            if (isPhoneLayout()) {
                moduleCards.forEach((otherCard) => {
                    if (otherCard !== target) {
                        setModuleState(otherCard, false);
                    }
                });
            }
            setModuleState(target, true);
            updateModuleProgress();
        }
        target.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
});

window.addEventListener('resize', () => {
    if (!isPhoneLayout()) {
        return;
    }

    const openCards = moduleCards.filter((card) => card.classList.contains('is-open'));
    if (openCards.length > 1) {
        openCards.slice(1).forEach((card) => setModuleState(card, false));
        updateModuleProgress();
    }
});

const fillLocationButton = document.querySelector('[data-fill-location]');
const locationStatus = document.querySelector('[data-location-status]');
const latitudeInput = document.querySelector('[name="geo_latitude"]');
const longitudeInput = document.querySelector('[name="geo_longitude"]');

const setLocationStatus = (message, tone = 'info') => {
    if (!locationStatus) {
        return;
    }
    locationStatus.textContent = message;
    locationStatus.dataset.tone = tone;
};

const getCurrentPosition = (options) => new Promise((resolve, reject) => {
    navigator.geolocation.getCurrentPosition(resolve, reject, options);
});

if (fillLocationButton) {
    fillLocationButton.addEventListener('click', async () => {
        if (!navigator.geolocation) {
            setLocationStatus('Geolocation is not supported on this phone or browser. Enter the coordinates manually.', 'error');
            return;
        }

        if (!window.isSecureContext) {
            setLocationStatus('Location access needs HTTPS. Open the portal on the secure site URL and try again.', 'error');
            return;
        }

        if (navigator.permissions?.query) {
            try {
                const permission = await navigator.permissions.query({ name: 'geolocation' });
                if (permission.state === 'denied') {
                    setLocationStatus('Location permission is blocked for this site. Allow location in browser settings and try again.', 'error');
                    return;
                }
            } catch (error) {
                // Continue even if the permissions API is unavailable.
            }
        }

        fillLocationButton.disabled = true;
        fillLocationButton.textContent = 'Getting location...';
        setLocationStatus('Trying GPS location...', 'info');

        try {
            let position;
            try {
                position = await getCurrentPosition({
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
            } catch (highAccuracyError) {
                setLocationStatus('High-accuracy GPS is slow. Trying standard device location...', 'info');
                position = await getCurrentPosition({
                    enableHighAccuracy: false,
                    timeout: 12000,
                    maximumAge: 60000,
                });
            }

            if (latitudeInput) {
                latitudeInput.value = position.coords.latitude.toFixed(6);
                latitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
            if (longitudeInput) {
                longitudeInput.value = position.coords.longitude.toFixed(6);
                longitudeInput.dispatchEvent(new Event('input', { bubbles: true }));
            }

            const accuracy = Math.round(position.coords.accuracy || 0);
            setLocationStatus(`Location captured successfully${accuracy ? ` (accuracy about ${accuracy} m)` : ''}.`, 'success');
        } catch (error) {
            let message = 'Unable to fetch current location. Please allow GPS permission or enter coordinates manually.';
            if (error && typeof error === 'object' && 'code' in error) {
                if (error.code === 1) {
                    message = 'Location permission was denied. Allow location for this site and try again.';
                } else if (error.code === 2) {
                    message = 'Location is unavailable right now. Move outdoors or check that device location is on.';
                } else if (error.code === 3) {
                    message = 'Location request timed out. Retry after the phone gets a better GPS signal.';
                }
            }
            setLocationStatus(message, 'error');
        } finally {
            fillLocationButton.disabled = false;
            fillLocationButton.textContent = 'Use current location';
        }
    });
}
