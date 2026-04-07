<div class="row justify-content-center mb-4">
    <div class="col-12 col-lg-10">

        <div class="card border-0 shadow-sm overflow-hidden">
            <div class="card-body p-4 p-md-5">
                <div class="row align-items-center g-4">
                    <div class="col-12 col-lg-8">


                        <h1 class="display-6 fw-bold mb-3">
                            <?= translate('home.landing.title') ?>
                        </h1>

                        <p class="lead text-muted mb-3">
                            <?= translate('home.landing.subtitle') ?>
                        </p>

                        <p class="mb-4 text-muted">
                            <?= translate('home.landing.note') ?>
                        </p>

                        <div class="d-grid d-sm-flex gap-2">
                            <a href="#create-account" class="btn btn-primary btn-lg px-4">
                                <?= translate('home.landing.cta_primary') ?>
                            </a>
                            <a href="#despre-serviciu" class="btn btn-outline-secondary btn-lg px-4">
                                <?= translate('home.landing.cta_secondary') ?>
                            </a>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="bg-light rounded-4 p-4 h-100 border">
                            <h2 class="h5 fw-bold mb-3"><?= translate('home.summary.title') ?></h2>
                            <ul class="list-unstyled small mb-0">
                                <li class="mb-2"><?= translate('home.summary.item1') ?></li>
                                <li class="mb-2"><?= translate('home.summary.item2') ?></li>
                                <li class="mb-2"><?= translate('home.summary.item3') ?></li>
                                <li class="mb-2"><?= translate('home.summary.item4') ?></li>
                                <li class="mb-0"><?= translate('home.summary.item5') ?></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning border-0 shadow-sm mt-3 mb-0" role="alert">
            <div class="small">
                <?= translate('home.warning.text') ?>
            </div>
        </div>

    </div>
</div>

<div class="row justify-content-center g-4 mb-4" id="despre-serviciu">
    <div class="col-12 col-lg-10">
        <div class="row g-4">

            <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-3"><?= translate('home.what.title') ?></h2>
                        <p class="text-muted mb-0">
                            <?= translate('home.what.text') ?>
                        </p>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-3"><?= translate('home.what_not.title') ?></h2>
                        <ul class="text-muted mb-0 ps-3">
                            <li><?= translate('home.what_not.item1') ?></li>
                            <li><?= translate('home.what_not.item2') ?></li>
                            <li><?= translate('home.what_not.item3') ?></li>
                            <li><?= translate('home.what_not.item4') ?></li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-3"><?= translate('home.how_it_works.title') ?></h2>

                        <div class="row g-3">
                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="rounded-4 border bg-light p-3 h-100">
                                    <div class="fw-bold mb-2"><?= translate('home.how_it_works.step1.title') ?></div>
                                    <div class="small text-muted">
                                        <?= translate('home.how_it_works.step1.text') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="rounded-4 border bg-light p-3 h-100">
                                    <div class="fw-bold mb-2"><?= translate('home.how_it_works.step2.title') ?></div>
                                    <div class="small text-muted">
                                        <?= translate('home.how_it_works.step2.text') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="rounded-4 border bg-light p-3 h-100">
                                    <div class="fw-bold mb-2"><?= translate('home.how_it_works.step3.title') ?></div>
                                    <div class="small text-muted">
                                        <?= translate('home.how_it_works.step3.text') ?>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-md-6 col-lg-3">
                                <div class="rounded-4 border bg-light p-3 h-100">
                                    <div class="fw-bold mb-2"><?= translate('home.how_it_works.step4.title') ?></div>
                                    <div class="small text-muted">
                                        <?= translate('home.how_it_works.step4.text') ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-4 small text-muted">
                            <?= translate('home.how_it_works.footer') ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body p-4">
                        <h2 class="h4 fw-bold mb-3"><?= translate('home.limitations.title') ?></h2>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <ul class="text-muted mb-0 ps-3">
                                    <li><?= translate('home.limitations.item1') ?></li>
                                    <li><?= translate('home.limitations.item2') ?></li>
                                    <li><?= translate('home.limitations.item3') ?></li>
                                </ul>
                            </div>
                            <div class="col-12 col-md-6">
                                <ul class="text-muted mb-0 ps-3">
                                    <li><?= translate('home.limitations.item4') ?></li>
                                    <li><?= translate('home.limitations.item5') ?></li>
                                    <li><?= translate('home.limitations.item6') ?></li>
                                </ul>
                            </div>
                        </div>

                        <div class="mt-3 small text-muted">
                            <?= translate('home.limitations.footer') ?>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="row justify-content-center" id="create-account">
    <div class="col-12 col-lg-10">
        <div class="card shadow-sm border-0 mb-5">
            <div class="card-body p-4 p-md-5">

                <div class="text-center mb-4">
                    <h2 class="h3 fw-bold mb-2"><?= translate('home.signup.title') ?></h2>
                    <p class="text-muted mb-0">
                        <?= translate('home.signup.subtitle') ?>
                    </p>
                </div>

                <form action="/api/subscribe" method="POST">
                    
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">

                    <div class="row g-3">
                        <div class="col-12 col-md-6">
                            <label for="country_id" class="form-label"><?= translate('home.form.country.label') ?></label>
                            <select class="form-select" id="country_id" name="country_id" required>
                                <?php foreach ($countries as $country): ?>
                                    <option value="<?= $country['id'] ?>" <?= $country['is_active'] ? '' : 'disabled' ?> <?= $country['code'] === 'RO' ? 'selected' : '' ?>>
                                        <?= $country['emoji'] ?> <?= translate($country['name_key']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="email" class="form-label"><?= translate('home.form.email.label') ?></label>
                            <input type="email" class="form-control" id="email" name="email" required placeholder="<?= translate('home.form.email.placeholder') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="nume" class="form-label"><?= translate('home.form.last_name.label') ?></label>
                            <input type="text" class="form-control" id="nume" name="nume" required placeholder="<?= translate('home.form.last_name.placeholder') ?>">
                        </div>

                        <div class="col-12 col-md-6">
                            <label for="prenume" class="form-label"><?= translate('home.form.first_name.label') ?></label>
                            <input type="text" class="form-control" id="prenume" name="prenume" required placeholder="<?= translate('home.form.first_name.placeholder') ?>">
                        </div>
                    </div>

                    <div class="accordion my-4" id="accordionAdvanced">
                        <div class="accordion-item border-0 bg-light">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed bg-light shadow-none text-primary fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOptions">
                                    ⚙️ <?= translate('home.form.advanced_options') ?>
                                </button>
                            </h2>
                            <div id="collapseOptions" class="accordion-collapse collapse" data-bs-parent="#accordionAdvanced">
                                <div class="accordion-body border-top">

                                    <div class="mb-4">
                                        <label class="form-label d-flex justify-content-between align-items-center w-100">
                                            <?= translate('home.form.obiect.label') ?>
                                            <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm" id="badge_obj" onclick="openModal('obj')" title="<?= translate('home.form.selection_title') ?>">
                                                <?= translate('home.form.any_selected') ?>
                                            </span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control" id="search_obj" placeholder="<?= translate('home.form.obiect.placeholder') ?>" autocomplete="off">
                                            <div id="dropdown_obj" class="list-group position-absolute w-100 shadow" style="display:none; z-index: 1000; max-height: 250px; overflow-y:auto; overflow-x:hidden;"></div>
                                        </div>
                                        <input type="hidden" name="target_objects" id="hidden_obj" value="[]">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label d-flex justify-content-between align-items-center w-100">
                                            <?= translate('home.form.institution.label') ?>
                                            <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm" id="badge_inst" onclick="openModal('inst')" title="<?= translate('home.form.selection_title') ?>">
                                                <?= translate('home.form.all_selected') ?>
                                            </span>
                                        </label>
                                        <div class="position-relative">
                                            <input type="text" class="form-control" id="search_inst" placeholder="<?= translate('home.form.institution.default') ?>" autocomplete="off">
                                            <div id="dropdown_inst" class="list-group position-absolute w-100 shadow" style="display:none; z-index: 1000; max-height: 250px; overflow-y:auto; overflow-x:hidden;"></div>
                                        </div>
                                        <input type="hidden" name="target_institutions" id="hidden_inst" value="[]">
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-4 border bg-light p-3 p-md-4 mb-4">
                        <div class="fw-bold mb-3"><?= translate('home.confirmations.title') ?></div>

                        <div class="form-check mb-3">
                            <input type="checkbox" class="form-check-input" id="self_monitoring_only" name="self_monitoring_only" value="1" required>
                            <label class="form-check-label" for="self_monitoring_only">
                                <?= translate('home.confirmations.self_only') ?>
                            </label>
                        </div>

                        <div class="form-check mb-0">
                            <input type="checkbox" class="form-check-input" id="legal_consent" name="legal_consent" required>
                            <label class="form-check-label small text-muted" for="legal_consent">
                                <?= translate('home.form.legal_consent') ?>
                            </label>
                        </div>
                    </div>

                    <div class="alert alert-secondary small mb-4" role="alert">
                        <?= translate('home.notice.informative_only') ?>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">
                        <?= translate('home.signup.submit') ?>
                    </button>

                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="selectionModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle"><?= translate('home.modal.title') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?= translate('home.modal.close') ?>"></button>
            </div>
            <div class="modal-body pt-3">
                <ul class="list-group list-group-flush" id="modalList"></ul>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-danger" id="modalClearAll">
                    <?= translate('home.modal.clear_all') ?>
                </button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">
                    <?= translate('home.modal.done') ?>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {

    const i18n = {
        allSelected: '<?= translate('home.form.all_selected') ?>',
        anySelected: '<?= translate('home.form.any_selected') ?>',
        instTitle: '<?= translate('home.modal.inst_title') ?>',
        objTitle: '<?= translate('home.modal.obj_title') ?>',
        noSelection: '<?= translate('home.modal.no_selection') ?>',
        noResults: '<?= translate('home.dropdown.no_results') ?>',
        removeTitle: '<?= translate('home.modal.remove') ?>'
    };

    const dbData = {
        'inst': <?= json_encode($institutions ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>,
        'obj': <?= json_encode($caseObjects ?? [], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?>
    };

    let selections = { 'inst': [], 'obj': [] };
    let currentModalType = '';
    const selectionModal = new bootstrap.Modal(document.getElementById('selectionModal'));

    function initSmartSelect(type) {
        const searchInput = document.getElementById(`search_${type}`);
        const dropdown = document.getElementById(`dropdown_${type}`);

        function renderList(filterText) {
            const val = filterText.toLowerCase().trim();
            dropdown.innerHTML = '';

            let matches = dbData[type];
            if (val.length > 0) {
                matches = matches.filter(item => item.name.toLowerCase().includes(val));
            }

            matches = matches.slice(0, 50);

            if (matches.length > 0) {
                matches.forEach(match => {
                    const isChecked = selections[type].some(sel => sel.api_code === match.api_code);

                    const label = document.createElement('label');
                    label.className = 'list-group-item list-group-item-action d-flex gap-2 align-items-start cursor-pointer';
                    label.style.whiteSpace = 'normal';
                    label.style.wordBreak = 'break-word';

                    const checkbox = document.createElement('input');
                    checkbox.className = 'form-check-input flex-shrink-0 mt-1';
                    checkbox.type = 'checkbox';
                    checkbox.value = match.api_code;
                    checkbox.checked = isChecked;

                    checkbox.onchange = (e) => {
                        if (e.target.checked) {
                            selections[type].push({ api_code: match.api_code, name: match.name });
                        } else {
                            selections[type] = selections[type].filter(sel => sel.api_code !== match.api_code);
                        }
                        updateUI(type);
                    };

                    const textSpan = document.createElement('span');
                    textSpan.textContent = match.name;
                    textSpan.className = 'w-100';

                    label.appendChild(checkbox);
                    label.appendChild(textSpan);
                    dropdown.appendChild(label);
                });
                dropdown.style.display = 'block';
            } else {
                dropdown.innerHTML = `<div class="list-group-item text-muted text-center border-0">${i18n.noResults}</div>`;
                dropdown.style.display = 'block';
            }
        }

        searchInput.addEventListener('focus', (e) => renderList(e.target.value));
        searchInput.addEventListener('input', (e) => renderList(e.target.value));

        document.addEventListener('click', (e) => {
            if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
            }
        });
    }

    window.removeSelection = function(type, api_code) {
        selections[type] = selections[type].filter(item => item.api_code !== api_code);
        updateUI(type);
        renderModalList(type);

        const searchInput = document.getElementById(`search_${type}`);
        searchInput.dispatchEvent(new Event('input'));
    };

    window.openModal = function(type) {
        currentModalType = type;
        document.getElementById('modalTitle').textContent = type === 'inst' ? i18n.instTitle : i18n.objTitle;
        renderModalList(type);
        selectionModal.show();
    };

    function renderModalList(type) {
        const list = document.getElementById('modalList');
        list.innerHTML = '';

        if (selections[type].length === 0) {
            list.innerHTML = `<li class="list-group-item text-muted text-center border-0">${i18n.noSelection}</li>`;
            return;
        }

        selections[type].forEach(item => {
            list.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-start px-0" style="white-space: normal; word-break: break-word;">
                    <span class="me-3 mt-1">${item.name}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 flex-shrink-0" onclick="removeSelection('${type}', '${item.api_code}')" title="${i18n.removeTitle}">❌</button>
                </li>
            `;
        });
    }

    document.getElementById('modalClearAll').addEventListener('click', () => {
        if (!currentModalType) return;
        selections[currentModalType] = [];
        updateUI(currentModalType);
        renderModalList(currentModalType);

        document.getElementById(`search_${currentModalType}`).dispatchEvent(new Event('input'));
    });

    function updateUI(type) {
        const count = selections[type].length;
        const badge = document.getElementById(`badge_${type}`);

        if (count === 0) {
            badge.textContent = type === 'inst' ? i18n.allSelected : i18n.anySelected;
            badge.className = 'badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm';
        } else {
            badge.textContent = count;
            badge.className = 'badge bg-primary rounded-pill cursor-pointer fs-6 shadow-sm';

            badge.classList.remove('bg-primary');
            badge.classList.add('bg-success');
            setTimeout(() => {
                badge.classList.remove('bg-success');
                badge.classList.add('bg-primary');
            }, 300);
        }

        const apiCodesOnly = selections[type].map(item => item.api_code);
        document.getElementById(`hidden_${type}`).value = JSON.stringify(apiCodesOnly);
    }

    initSmartSelect('inst');
    initSmartSelect('obj');

    updateUI('inst');
    updateUI('obj');
});
</script>

<style>
.cursor-pointer { cursor: pointer; }
.badge { transition: background-color 0.3s ease; padding: 0.5em 0.8em; }
</style>
