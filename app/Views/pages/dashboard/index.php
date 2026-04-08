<div class="row mb-5">
    <div class="col-12">
        
        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center mb-4 gap-3">
            <h1 class="h3 fw-bold text-primary mb-0"><?= translate('nav.dashboard', 'Panoul meu') ?></h1>
            
            <?php if (empty($alerts)): ?>
                <a href="/" class="btn btn-primary btn-sm fw-bold shadow-sm"><?= translate('dashboard.btn.new_alert', '+ Setare Alertă Nouă') ?></a>
            <?php else: ?>
                <button type="button" class="btn btn-primary btn-sm fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#newAlertModal">
                    <?= translate('dashboard.btn.new_alert', '+ Setare Alertă Nouă') ?>
                </button>
            <?php endif; ?>
        </div>

        <p class="text-muted mb-4"><?= translate('dashboard.greeting', 'Bine ai venit,') ?> <strong><?= htmlspecialchars($_SESSION['user_email']) ?></strong>!</p>

        <div class="card shadow-sm border-0 mb-4 bg-white border-start border-primary border-4">
            <div class="card-body">
                <h5 class="fw-bold text-primary"><?= translate('dashboard.ai.card_title', '✨ Înrolare BETA: Rezumate AI') ?></h5>
                <p class="small text-muted mb-3"><?= translate('dashboard.ai.card_desc', 'Înscrie-te pe lista de așteptare pentru a primi un rezumat explicativ generat de Inteligența Artificială pentru dosarele tale. Te vom notifica pe email imediat ce contul tău este aprobat și funcționalitatea devine activă.') ?></p>
                <form action="/dashboard/toggle-ai" method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input cursor-pointer" type="checkbox" id="aiOptIn" name="ai_opt_in" value="1" <?= $aiOptIn ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="form-check-label fw-bold" for="aiOptIn"><?= translate('dashboard.ai.toggle', 'Doresc să mă înscriu pe lista de așteptare AI') ?></label>
                    </div>
                </form>
            </div>
        </div>
        
        <div class="card shadow-sm border-0 mb-4 bg-white border-start border-secondary border-4">
            <div class="card-body">
                <h5 class="fw-bold text-secondary"><?= translate('dashboard.privacy.title', '🛡️ Date și Confidențialitate') ?></h5>
                <p class="small text-muted mb-3"><?= translate('dashboard.privacy.desc', 'Descarcă o copie a datelor tale sau șterge log-urile tehnice ale notificărilor primite.') ?></p>
                
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="/dashboard/export-data" class="btn btn-sm btn-outline-secondary fw-bold">
                        <?= translate('dashboard.btn.export', '⬇️ Descarcă Datele (JSON)') ?>
                    </a>

                    <form action="/dashboard/clear-history" method="POST" class="m-0" onsubmit="return confirm('<?= translate('dashboard.confirm.clear_history', 'Atenție! Istoricul va fi șters, iar toate alertele tale vor fi puse pe PAUZĂ pentru a nu re-primii dosarele vechi. Ești sigur?') ?>');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger fw-bold">
                            <?= translate('dashboard.btn.clear_history', '🗑️ Șterge Istoric Notificări') ?>
                        </button>
                    </form>

                    <form action="/dashboard/delete-account" method="POST" class="m-0 ms-auto" onsubmit="return confirm('<?= translate('dashboard.confirm.delete_account', '⚠️ ATENȚIE: Această acțiune va șterge definitiv și irevocabil contul tău, toate alertele setate și istoricul notificărilor. Nu vom mai reține nicio dată despre tine. Ești absolut sigur că vrei să continui?') ?>');">
                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                        <button type="submit" class="btn btn-sm btn-danger fw-bold text-white shadow-sm">
                            <?= translate('dashboard.btn.delete_account', '🚨 Șterge Contul Complet') ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
        
        <?php if (empty($alerts)): ?>
            <div class="text-center py-5 bg-white shadow-sm rounded">
                <span class="fs-1 d-block mb-3">📭</span>
                <p class="text-muted mb-0"><?= translate('dashboard.no_alerts', 'Nu ai setat nicio alertă momentan.') ?></p>
            </div>
        <?php else: ?>
            <div class="row g-4">
                <?php foreach ($alerts as $alert): ?>
                    
                    <?php 
                        $statusColor = 'info';
                        if ($alert['status'] === 'active') $statusColor = 'success';
                        if ($alert['status'] === 'paused') $statusColor = 'warning';

                        $savedInstitutions = json_decode($alert['target_institutions'] ?? '[]', true) ?: [];
                        $savedObjects = json_decode($alert['target_objects'] ?? '[]', true) ?: [];
                    ?>

                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="card h-100 shadow-sm border-0 border-start border-<?= $statusColor ?> border-4">
                            <div class="card-body d-flex flex-column">
                                
                                <div class="d-flex justify-content-between align-items-start mb-2 gap-2">
                                    <h5 class="card-title fw-bold text-dark mb-0 lh-sm">
                                        <?= htmlspecialchars($alert['nume_familie'] . ' ' . $alert['prenume']) ?>
                                    </h5>
                                    <div>
                                        <?php if ($alert['status'] === 'active'): ?>
                                            <span class="badge bg-success"><?= translate('dashboard.status.active', 'Activă') ?></span>
                                        <?php elseif ($alert['status'] === 'paused'): ?>
                                            <span class="badge bg-warning text-dark"><?= translate('dashboard.status.paused', 'Pe pauză') ?></span>
                                        <?php else: ?>
                                            <span class="badge bg-info"><?= translate('dashboard.status.pending', 'În așteptare') ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mb-4 mt-3">
                                    <div class="small text-muted mb-2">
                                        <i class="opacity-75">🔍</i> 
                                        <strong><?= translate('dashboard.label.objects', 'Obiecte:') ?></strong> 
                                        <?= empty($savedObjects) ? '<span class="badge bg-secondary">' . translate('dashboard.any_object', 'Orice obiect') . '</span>' : count($savedObjects) . ' ' . translate('dashboard.label.selected', 'selectate') ?>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="opacity-75">⚖️</i> 
                                        <strong><?= translate('dashboard.label.courts', 'Instanțe:') ?></strong> 
                                        <?= empty($savedInstitutions) ? '<span class="badge bg-secondary">' . translate('dashboard.all_courts', 'Toate instanțele') . '</span>' : count($savedInstitutions) . ' ' . translate('dashboard.label.selected', 'selectate') ?>
                                    </div>
                                </div>
                                
                                <div class="d-flex justify-content-end gap-2 mt-auto pt-3 border-top">
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editModal<?= $alert['id'] ?>">
                                        ✏️ <?= translate('dashboard.btn.edit', 'Editează') ?>
                                    </button>

                                    <?php if ($alert['status'] !== 'pending_verification'): ?>
                                        <form action="/dashboard/alert/toggle-status" method="POST" class="m-0">
                                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                            <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                            <?php if ($alert['status'] === 'active'): ?>
                                                <input type="hidden" name="status" value="paused">
                                                <button type="submit" class="btn btn-sm btn-outline-warning" title="<?= translate('dashboard.btn.pause_title', 'Pune pe pauză') ?>">⏸</button>
                                            <?php else: ?>
                                                <input type="hidden" name="status" value="active">
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="<?= translate('dashboard.btn.resume_title', 'Reia alerta') ?>">▶️</button>
                                            <?php endif; ?>
                                        </form>
                                    <?php endif; ?>

                                    <form action="/dashboard/alert/delete" method="POST" class="m-0" onsubmit="return confirm('<?= translate('dashboard.confirm.delete', 'Ești sigur că vrei să ștergi această alertă?') ?>');">
                                        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                        <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="<?= translate('dashboard.btn.delete_title', 'Șterge definitiv') ?>">🗑</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="editModal<?= $alert['id'] ?>" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                            <div class="modal-content">
                                <form action="/dashboard/alert/edit" method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold"><?= translate('dashboard.edit.title', 'Editează Alerta') ?></h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body pt-4">
                                        <input type="hidden" name="alert_id" value="<?= $alert['id'] ?>">
                                        
                                        <div class="mb-4">
                                            <label class="form-label small text-muted"><?= translate('dashboard.edit.name_label', 'Nume monitorizat (nu poate fi modificat)') ?></label>
                                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($alert['nume_familie'] . ' ' . $alert['prenume']) ?>" disabled>
                                        </div>

                                        <div class="mb-4 smart-select-wrapper" data-alert-id="<?= $alert['id'] ?>" data-type="obj" data-selected='<?= htmlspecialchars($alert['target_objects'] ?: "[]") ?>'>
                                            <label class="form-label d-flex justify-content-between align-items-center w-100">
                                                <?= translate('home.form.obiect.label', 'Obiecte Dosar') ?>
                                                <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm badge-display" onclick="openGlobalModal('<?= $alert['id'] ?>', 'obj')" title="<?= translate('home.form.selection_title', 'Vezi selecția') ?>"><?= translate('home.form.any_selected', 'Orice') ?></span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control search-input" placeholder="<?= translate('home.form.obiect.placeholder', 'Caută și bifează obiecte...') ?>" autocomplete="off">
                                                <div class="list-group position-absolute w-100 shadow dropdown-list" style="display:none; z-index: 1060; max-height: 200px; overflow-y:auto; overflow-x:hidden;"></div>
                                            </div>
                                            <input type="hidden" name="target_objects" class="hidden-input" value="[]">
                                        </div>

                                        <div class="mb-2 smart-select-wrapper" data-alert-id="<?= $alert['id'] ?>" data-type="inst" data-selected='<?= htmlspecialchars($alert['target_institutions'] ?: "[]") ?>'>
                                            <label class="form-label d-flex justify-content-between align-items-center w-100">
                                                <?= translate('home.form.institution.label', 'Instanțe') ?>
                                                <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm badge-display" onclick="openGlobalModal('<?= $alert['id'] ?>', 'inst')" title="<?= translate('home.form.selection_title', 'Vezi selecția') ?>"><?= translate('home.form.all_selected', 'Toate') ?></span>
                                            </label>
                                            <div class="position-relative">
                                                <input type="text" class="form-control search-input" placeholder="<?= translate('home.form.institution.default', 'Caută și bifează instanțe...') ?>" autocomplete="off">
                                                <div class="list-group position-absolute w-100 shadow dropdown-list" style="display:none; z-index: 1060; max-height: 200px; overflow-y:auto; overflow-x:hidden;"></div>
                                            </div>
                                            <input type="hidden" name="target_institutions" class="hidden-input" value="[]">
                                        </div>

                                    </div>
                                    <div class="modal-footer border-0 pt-0">
                                        <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= translate('dashboard.edit.cancel', 'Anulează') ?></button>
                                        <button type="submit" class="btn btn-primary"><?= translate('dashboard.edit.save', 'Salvează Modificările') ?></button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                <?php endforeach; ?>
            </div>
            
            <div class="modal fade" id="newAlertModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content">
                        <form action="/dashboard/alert/create" method="POST">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'] ?? '') ?>">
                            <div class="modal-header border-0 pb-0">
                                <h5 class="modal-title fw-bold"><?= translate('dashboard.modal.new_alert_title', 'Adaugă Alertă Nouă') ?></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div class="modal-body pt-4">
                                <div class="mb-4">
                                    <label class="form-label small text-muted"><?= translate('dashboard.edit.name_label', 'Nume monitorizat (nu poate fi modificat)') ?></label>
                                    <input type="text" class="form-control bg-light" value="<?= htmlspecialchars(($alerts[0]['nume_familie'] ?? '') . ' ' . ($alerts[0]['prenume'] ?? '')) ?>" disabled>
                                </div>

                                <div class="mb-4 smart-select-wrapper" data-alert-id="new" data-type="obj" data-selected="[]">
                                    <label class="form-label d-flex justify-content-between align-items-center w-100">
                                        <?= translate('home.form.obiect.label', 'Obiecte Dosar') ?>
                                        <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm badge-display" onclick="openGlobalModal('new', 'obj')" title="<?= translate('home.form.selection_title', 'Vezi selecția') ?>"><?= translate('home.form.any_selected', 'Orice') ?></span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control search-input" placeholder="<?= translate('home.form.obiect.placeholder', 'Caută și bifează obiecte...') ?>" autocomplete="off">
                                        <div class="list-group position-absolute w-100 shadow dropdown-list" style="display:none; z-index: 1060; max-height: 200px; overflow-y:auto; overflow-x:hidden;"></div>
                                    </div>
                                    <input type="hidden" name="target_objects" class="hidden-input" value="[]">
                                </div>

                                <div class="mb-2 smart-select-wrapper" data-alert-id="new" data-type="inst" data-selected="[]">
                                    <label class="form-label d-flex justify-content-between align-items-center w-100">
                                        <?= translate('home.form.institution.label', 'Instanțe') ?>
                                        <span class="badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm badge-display" onclick="openGlobalModal('new', 'inst')" title="<?= translate('home.form.selection_title', 'Vezi selecția') ?>"><?= translate('home.form.all_selected', 'Toate') ?></span>
                                    </label>
                                    <div class="position-relative">
                                        <input type="text" class="form-control search-input" placeholder="<?= translate('home.form.institution.default', 'Caută și bifează instanțe...') ?>" autocomplete="off">
                                        <div class="list-group position-absolute w-100 shadow dropdown-list" style="display:none; z-index: 1060; max-height: 200px; overflow-y:auto; overflow-x:hidden;"></div>
                                    </div>
                                    <input type="hidden" name="target_institutions" class="hidden-input" value="[]">
                                </div>
                            </div>
                            <div class="modal-footer border-0 pt-0">
                                <button type="button" class="btn btn-light" data-bs-dismiss="modal"><?= translate('dashboard.edit.cancel', 'Anulează') ?></button>
                                <button type="submit" class="btn btn-primary"><?= translate('dashboard.edit.save', 'Salvează Modificările') ?></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        <?php endif; ?>
        
    </div>
</div>

<div class="modal fade" id="globalSelectionModal" tabindex="-1" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-primary">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="globalModalTitle"><?= translate('dashboard.modal.selections', 'Selecții') ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-3">
                <ul class="list-group list-group-flush" id="globalModalList">
                    </ul>
            </div>
            <div class="modal-footer border-0 pt-0 justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-danger" id="globalModalClearAll"><?= translate('home.modal.clear_all', '🗑️ Șterge Tot') ?></button>
                <button type="button" class="btn btn-primary" data-bs-dismiss="modal"><?= translate('home.modal.done', 'Gata') ?></button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    const dbData = {
        'inst': <?= json_encode($institutions ?? []) ?>,
        'obj': <?= json_encode($caseObjects ?? []) ?>
    };

    const i18n = {
        allSelected: '<?= translate('home.form.all_selected', 'Toate') ?>',
        anySelected: '<?= translate('home.form.any_selected', 'Orice') ?>',
        instTitle: '<?= translate('home.modal.inst_title', 'Instanțe Selectate') ?>',
        objTitle: '<?= translate('home.modal.obj_title', 'Obiecte Selectate') ?>',
        noSelection: '<?= translate('home.modal.no_selection', 'Nicio excepție setată.<br><small>Căutăm în toate.</small>') ?>',
        noResults: '<?= translate('home.dropdown.no_results', 'Nu am găsit rezultate.') ?>',
        removeTitle: '<?= translate('home.modal.remove', 'Șterge') ?>'
    };

    let appState = {};
    let currentContext = { alertId: null, type: null };
    const selectionModal = new bootstrap.Modal(document.getElementById('globalSelectionModal'));

    const wrappers = document.querySelectorAll('.smart-select-wrapper');

    wrappers.forEach(wrapper => {
        const alertId = wrapper.dataset.alertId;
        const type = wrapper.dataset.type;
        const initialCodes = JSON.parse(wrapper.dataset.selected || '[]');

        if (!appState[alertId]) appState[alertId] = { inst: [], obj: [] };

        appState[alertId][type] = initialCodes.map(code => {
            const found = dbData[type].find(item => item.api_code === code);
            return found ? { api_code: code, name: found.name } : null;
        }).filter(item => item !== null);

        const searchInput = wrapper.querySelector('.search-input');
        const dropdown = wrapper.querySelector('.dropdown-list');
        const badge = wrapper.querySelector('.badge-display');
        const hiddenInput = wrapper.querySelector('.hidden-input');

        function updateUI() {
            const count = appState[alertId][type].length;
            if (count === 0) {
                badge.textContent = type === 'inst' ? i18n.allSelected : i18n.anySelected;
                badge.className = 'badge bg-secondary rounded-pill cursor-pointer fs-6 shadow-sm badge-display';
            } else {
                badge.textContent = count;
                badge.className = 'badge bg-primary rounded-pill cursor-pointer fs-6 shadow-sm badge-display';
                badge.classList.remove('bg-primary');
                badge.classList.add('bg-success');
                setTimeout(() => { badge.classList.remove('bg-success'); badge.classList.add('bg-primary'); }, 300);
            }
            hiddenInput.value = JSON.stringify(appState[alertId][type].map(i => i.api_code));
        }

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
                    const isChecked = appState[alertId][type].some(sel => sel.api_code === match.api_code);

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
                            appState[alertId][type].push({ api_code: match.api_code, name: match.name });
                        } else {
                            appState[alertId][type] = appState[alertId][type].filter(sel => sel.api_code !== match.api_code);
                        }
                        updateUI();
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

        wrapper.addEventListener('refreshUI', () => {
            updateUI();
            if(dropdown.style.display === 'block') {
                renderList(searchInput.value);
            }
        });

        updateUI();
    });

    document.addEventListener('click', (e) => {
        if (!e.target.closest('.smart-select-wrapper')) {
            document.querySelectorAll('.dropdown-list').forEach(d => d.style.display = 'none');
        }
    });

    window.openGlobalModal = function(alertId, type) {
        currentContext = { alertId: alertId.toString(), type: type };
        document.getElementById('globalModalTitle').textContent = type === 'inst' ? i18n.instTitle : i18n.objTitle;
        renderGlobalModalList();
        selectionModal.show();
    }

    window.removeSelectionGlobal = function(api_code) {
        const { alertId, type } = currentContext;
        appState[alertId][type] = appState[alertId][type].filter(item => item.api_code !== api_code);
        
        const wrapper = document.querySelector(`.smart-select-wrapper[data-alert-id="${alertId}"][data-type="${type}"]`);
        if (wrapper) wrapper.dispatchEvent(new Event('refreshUI'));
        
        renderGlobalModalList();
    }

    function renderGlobalModalList() {
        const { alertId, type } = currentContext;
        const list = document.getElementById('globalModalList');
        list.innerHTML = '';
        
        if (!appState[alertId] || appState[alertId][type].length === 0) {
            list.innerHTML = `<li class="list-group-item text-muted text-center border-0">${i18n.noSelection}</li>`;
            return;
        }

        appState[alertId][type].forEach(item => {
            list.innerHTML += `
                <li class="list-group-item d-flex justify-content-between align-items-start px-0" style="white-space: normal; word-break: break-word;">
                    <span class="me-3 mt-1">${item.name}</span>
                    <button type="button" class="btn btn-sm btn-outline-danger border-0 flex-shrink-0" onclick="removeSelectionGlobal('${item.api_code}')" title="${i18n.removeTitle}">❌</button>
                </li>
            `;
        });
    }

    document.getElementById('globalModalClearAll').addEventListener('click', () => {
        const { alertId, type } = currentContext;
        if (!alertId || !type) return;
        
        appState[alertId][type] = [];
        
        const wrapper = document.querySelector(`.smart-select-wrapper[data-alert-id="${alertId}"][data-type="${type}"]`);
        if (wrapper) wrapper.dispatchEvent(new Event('refreshUI'));
        
        renderGlobalModalList();
    });
});
</script>

<style>
.cursor-pointer { cursor: pointer; }
.badge { transition: background-color 0.3s ease; padding: 0.5em 0.8em; }
</style>
