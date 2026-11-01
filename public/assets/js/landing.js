/**
 * Frontend Interactive Controller for Republik Nusantara Portal (`public/assets/js/landing.js`).
 * Manages Portal Login, Logout, Citizen Registration, and Role-Restricted Executive Actions
 * (Menu Presiden RI & Menu Wakil Presiden RI) with custom UI validation.
 */

(function () {
    'use strict';

    /** @type {{ form: HTMLFormElement, endpoint: string, prefix: 'pres'|'vp' }|null} Pending executive form awaiting confirmation */
    let pendingExecutiveAction = null;

    /**
     * Escapes unsafe HTML characters before injecting dynamic strings into DOM templates.
     *
     * @param {string} rawText - Unsanitized string value.
     * @returns {string} HTML-safe escaped string.
     */
    function escapeHtml(rawText) {
        const div = document.createElement('div');
        div.textContent = String(rawText ?? '');
        return div.innerHTML;
    }

    /**
     * Displays a custom non-blocking toast notification with vector SVG iconography.
     *
     * @param {string} message - Notification body text.
     * @param {'success'|'error'|'info'} [tone='info'] - Visual severity tone.
     * @returns {void}
     */
    function showToast(message, tone = 'info') {
        const container = document.getElementById('toastContainer');
        if (!container) {
            return;
        }

        const toneStyles = {
            success: 'bg-slate-900 border-slate-800 text-white',
            error: 'bg-crimson-700 border-crimson-800 text-white',
            info: 'bg-slate-900 border-slate-800 text-white',
        };

        const iconSvg = tone === 'error'
            ? '<svg class="w-4 h-4 text-white shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>'
            : '<svg class="w-4 h-4 text-emerald-400 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path><polyline points="22 4 12 14.01 9 11.01"></polyline></svg>';

        const toastEl = document.createElement('div');
        toastEl.className = `pointer-events-auto flex items-start gap-2.5 px-4 py-3 rounded-lg border shadow-lg transition-all duration-200 ${toneStyles[tone] || toneStyles.info}`;
        toastEl.innerHTML = `
            ${iconSvg}
            <div class="flex-1 text-xs font-medium leading-relaxed">${escapeHtml(message)}</div>
        `;

        container.appendChild(toastEl);

        setTimeout(() => {
            toastEl.classList.add('opacity-0', 'translate-y-1');
            setTimeout(() => toastEl.remove(), 200);
        }, 4200);
    }

    /**
     * Clears validation errors on the registration form.
     *
     * @returns {void}
     */
    function clearFormValidationErrors() {
        const globalErr = document.getElementById('citizenshipGlobalError');
        if (globalErr) {
            globalErr.classList.add('hidden');
        }

        const fields = [
            'character_name',
            'discord_handle',
            'account_password',
            'career_track',
            'domicile',
            'character_backstory',
            'oath_accepted',
        ];
        fields.forEach((field) => {
            const errEl = document.getElementById(`err-${field}`);
            const inputEl = document.getElementById(field);
            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }
            if (inputEl && inputEl.type !== 'checkbox') {
                inputEl.classList.remove('border-crimson-600');
                inputEl.classList.add('border-slate-300');
            }
        });
    }

    /**
     * Displays field-specific validation error messages beneath invalid registration inputs.
     *
     * @param {Object<string, string>} errors - Field key to error description map.
     * @param {string} [bannerMessage] - Optional top-level summary error message.
     * @returns {void}
     */
    function displayFormValidationErrors(errors, bannerMessage) {
        const globalErr = document.getElementById('citizenshipGlobalError');
        const globalErrText = document.getElementById('citizenshipGlobalErrorText');

        if (globalErr) {
            globalErr.classList.remove('hidden');
            if (globalErrText && bannerMessage) {
                globalErrText.textContent = bannerMessage;
            }
        }

        Object.keys(errors).forEach((field) => {
            const errEl = document.getElementById(`err-${field}`);
            const inputEl = document.getElementById(field);
            if (errEl) {
                errEl.textContent = errors[field];
                errEl.classList.remove('hidden');
            }
            if (inputEl && inputEl.type !== 'checkbox') {
                inputEl.classList.remove('border-slate-300');
                inputEl.classList.add('border-crimson-600');
            }
        });
    }

    /**
     * Performs custom client-side validation on the registration form.
     *
     * @param {FormData} formData - Extracted form data payload.
     * @returns {Object<string, string>} Map of validation errors.
     */
    function validateCitizenshipClientSide(formData) {
        const errors = {};
        const charName = String(formData.get('character_name') || '').trim();
        const username = String(formData.get('discord_handle') || '').trim();
        const password = String(formData.get('account_password') || '');
        const track = String(formData.get('career_track') || '').trim();
        const domicile = String(formData.get('domicile') || '').trim();
        const backstory = String(formData.get('character_backstory') || '').trim();
        const oath = formData.get('oath_accepted') === '1';

        if (!charName) {
            errors.character_name = 'Nama lengkap karakter wajib diisi.';
        } else if (charName.length < 5 || charName.length > 60) {
            errors.character_name = 'Nama karakter harus terdiri dari 5 hingga 60 karakter.';
        } else if (!/^[a-zA-Z\s.,'-]+$/.test(charName)) {
            errors.character_name = 'Nama karakter hanya boleh berisi huruf alfabet, titik, koma, atau tanda hubung.';
        }

        if (!username) {
            errors.discord_handle = 'Username akun wajib diisi.';
        } else if (username.length < 3 || username.length > 32) {
            errors.discord_handle = 'Username harus terdiri dari 3 hingga 32 karakter.';
        } else if (!/^[a-zA-Z0-9_.-]+$/.test(username)) {
            errors.discord_handle = 'Username hanya boleh berisi huruf, angka, titik, atau garis bawah.';
        }

        if (!password) {
            errors.account_password = 'Kata sandi akun wajib dibuat.';
        } else if (password.length < 6) {
            errors.account_password = 'Kata sandi minimal terdiri dari 6 karakter.';
        }

        if (!track) {
            errors.career_track = 'Pilih salah satu jalur pengabdian awal.';
        }

        if (!domicile) {
            errors.domicile = 'Pilih provinsi domisili daerah pemilihan.';
        }

        if (!backstory) {
            errors.character_backstory = 'Ringkasan latar belakang karakter wajib diisi.';
        } else if (backstory.length < 25) {
            errors.character_backstory = `Latar belakang minimal 25 karakter (saat ini ${backstory.length} karakter).`;
        } else if (backstory.length > 500) {
            errors.character_backstory = 'Latar belakang maksimal 500 karakter.';
        }

        if (!oath) {
            errors.oath_accepted = 'Anda wajib menyetujui Sumpah Setia Konstitusi.';
        }

        return errors;
    }

    /**
     * Submits the validated citizenship form payload.
     *
     * @param {HTMLFormElement} formElement - The registration form DOM element.
     * @returns {Promise<void>}
     */
    async function submitValidatedCitizenshipForm(formElement) {
        const submitBtn = document.getElementById('citizenshipSubmitBtn');
        const originalText = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Menerbitkan...</span>';
        }

        try {
            const formData = new FormData(formElement);
            const response = await fetch('?action=register-citizen', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const result = await response.json();

            if (response.status === 422 || result.status === 'validation_error') {
                displayFormValidationErrors(result.errors || {}, result.message);
                showToast(result.message || 'Pendaftaran belum lengkap.', 'error');
                return;
            }

            if (result.status === 'success' && result.citizen) {
                const citizen = result.citizen;
                const cardNik = document.getElementById('cardNikRp');
                const cardName = document.getElementById('cardCharName');
                const cardDomicile = document.getElementById('cardDomicile');
                const cardRole = document.getElementById('cardRbacRole');
                const successPanel = document.getElementById('citizenshipSuccessPanel');

                if (cardNik) cardNik.textContent = `NIK: ${citizen.nik_rp}`;
                if (cardName) cardName.textContent = citizen.character_name;
                if (cardDomicile) cardDomicile.textContent = citizen.domicile;
                if (cardRole) cardRole.textContent = citizen.initial_rbac_role;

                formElement.classList.add('hidden');
                if (successPanel) {
                    successPanel.classList.remove('hidden');
                }

                showToast(result.message, 'success');
            }
        } catch (error) {
            showToast('Tidak dapat memproses pendaftaran saat ini.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalText;
            }
        }
    }

    /**
     * Clears validation errors on the Login form.
     *
     * @returns {void}
     */
    function clearLoginErrors() {
        const globalErr = document.getElementById('loginGlobalError');
        if (globalErr) {
            globalErr.classList.add('hidden');
        }

        ['login_identifier', 'login_password'].forEach((field) => {
            const errEl = document.getElementById(`err-${field}`);
            const inputEl = document.getElementById(field);
            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }
            if (inputEl) {
                inputEl.classList.remove('border-crimson-600');
                inputEl.classList.add('border-slate-300');
            }
        });
    }

    /**
     * Displays validation errors on the Login form.
     *
     * @param {Object<string, string>} errors - Field error map.
     * @param {string} [bannerMessage] - Optional banner message.
     * @returns {void}
     */
    function displayLoginErrors(errors, bannerMessage) {
        const globalErr = document.getElementById('loginGlobalError');
        const globalErrText = document.getElementById('loginGlobalErrorText');

        if (globalErr) {
            globalErr.classList.remove('hidden');
            if (globalErrText && bannerMessage) {
                globalErrText.textContent = bannerMessage;
            }
        }

        Object.keys(errors).forEach((field) => {
            const errEl = document.getElementById(`err-${field}`);
            const inputEl = document.getElementById(field);
            if (errEl) {
                errEl.textContent = errors[field];
                errEl.classList.remove('hidden');
            }
            if (inputEl) {
                inputEl.classList.remove('border-slate-300');
                inputEl.classList.add('border-crimson-600');
            }
        });
    }

    /**
     * Submits the Login form with custom validation and reloads the portal on success
     * so role-exclusive menus (Menu Presiden / Menu Wakil Presiden) are rendered.
     *
     * @param {HTMLFormElement} loginForm - Login form DOM element.
     * @returns {Promise<void>}
     */
    async function handleLoginSubmit(loginForm) {
        clearLoginErrors();

        const formData = new FormData(loginForm);
        const identifier = String(formData.get('login_identifier') || '').trim();
        const password = String(formData.get('login_password') || '');
        const clientErrors = {};

        if (!identifier) {
            clientErrors.login_identifier = 'Username atau Nomor Induk Kependudukan (NIK) wajib diisi.';
        }
        if (!password) {
            clientErrors.login_password = 'Kata sandi wajib diisi.';
        }

        if (Object.keys(clientErrors).length > 0) {
            displayLoginErrors(clientErrors, 'Harap lengkapi identitas dan kata sandi Anda.');
            return;
        }

        const submitBtn = document.getElementById('loginSubmitBtn');
        const originalText = submitBtn ? submitBtn.textContent : 'Masuk';
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.textContent = 'Memverifikasi...';
        }

        try {
            const response = await fetch('?action=login', {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const result = await response.json();

            if (response.status === 422 || result.status === 'validation_error') {
                displayLoginErrors(result.errors || {}, result.message);
                showToast(result.message || 'Gagal masuk ke portal.', 'error');
                return;
            }

            if (result.status === 'success' && result.user) {
                showToast(result.message, 'success');
                setTimeout(() => {
                    window.location.href = result.redirect_url || '?page=beranda';
                }, 500);
            }
        } catch (error) {
            showToast('Terjadi kendala saat memverifikasi identitas.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.textContent = originalText;
            }
        }
    }

    /**
     * Logs out the currently authenticated user session.
     *
     * @returns {Promise<void>}
     */
    async function handleLogout() {
        try {
            const response = await fetch('?action=logout', {
                method: 'POST',
                headers: { 'Accept': 'application/json' },
            });
            const result = await response.json();
            showToast(result.message || 'Sesi telah diakhiri.', 'info');
            setTimeout(() => {
                window.location.href = '?page=beranda';
            }, 400);
        } catch (error) {
            window.location.href = '?page=beranda';
        }
    }

    /**
     * Clears validation errors on the Presidential or Vice-Presidential form.
     *
     * @param {'pres'|'vp'} prefix - Form field prefix.
     * @returns {void}
     */
    function clearExecutiveActErrors(prefix) {
        const globalErr = document.getElementById(`${prefix}ActGlobalError`);
        if (globalErr) {
            globalErr.textContent = '';
            globalErr.classList.add('hidden');
        }

        ['document_type', 'document_number', 'permission_id', 'title', 'summary_content'].forEach((field) => {
            const errEl = document.getElementById(`err-${prefix}-${field}`);
            const inputEl = document.getElementById(`${prefix}_${field}`);
            if (errEl) {
                errEl.textContent = '';
                errEl.classList.add('hidden');
            }
            if (inputEl) {
                inputEl.classList.remove('border-crimson-600');
                inputEl.classList.add('border-slate-300');
            }
        });
    }

    /**
     * Displays validation errors on the Presidential or Vice-Presidential form.
     *
     * @param {'pres'|'vp'} prefix - Form field prefix.
     * @param {Object<string, string>} errors - Field error map.
     * @param {string} [bannerMessage] - Summary banner error.
     * @returns {void}
     */
    function displayExecutiveActErrors(prefix, errors, bannerMessage) {
        const globalErr = document.getElementById(`${prefix}ActGlobalError`);
        if (globalErr && bannerMessage) {
            globalErr.textContent = bannerMessage;
            globalErr.classList.remove('hidden');
        }

        Object.keys(errors).forEach((field) => {
            const errEl = document.getElementById(`err-${prefix}-${field}`);
            const inputEl = document.getElementById(`${prefix}_${field}`);
            if (errEl) {
                errEl.textContent = errors[field];
                errEl.classList.remove('hidden');
            }
            if (inputEl) {
                inputEl.classList.remove('border-slate-300');
                inputEl.classList.add('border-crimson-600');
            }
        });
    }

    /**
     * Validates Presidential or Vice-Presidential decree inputs on the client side.
     *
     * @param {FormData} formData - Extracted form data.
     * @returns {Object<string, string>} Validation error dictionary.
     */
    function validateExecutiveActClientSide(formData) {
        const errors = {};
        const docType = String(formData.get('document_type') || '').trim();
        const docNumber = String(formData.get('document_number') || '').trim();
        const permId = String(formData.get('permission_id') || '').trim();
        const title = String(formData.get('title') || '').trim();
        const summary = String(formData.get('summary_content') || '').trim();

        if (!docType) {
            errors.document_type = 'Pilih jenis instrumen keputusan.';
        }
        if (!docNumber || docNumber.length < 4 || docNumber.length > 60) {
            errors.document_number = 'Nomor registrasi naskah wajib diisi (4–60 karakter).';
        }
        if (!permId) {
            errors.permission_id = 'Pilih dasar kewenangan konstitusional.';
        }
        if (!title || title.length < 10 || title.length > 200) {
            errors.title = 'Judul keputusan wajib diisi (minimal 10 karakter).';
        }
        if (!summary || summary.length < 20 || summary.length > 25000) {
            errors.summary_content = 'Materi pasal keputusan wajib diisi (minimal 20 karakter).';
        }

        return errors;
    }

    /**
     * Synchronizes the Presidential regulation form inputs with the live document sheet preview,
     * manages dynamic Menimbang consideration items, dynamic Mengingat legal basis items,
     * dynamic Pasal (article) builder, cursive signature text, and coordinates PDF export & print actions.
     *
     * @returns {void}
     */
    function initPresidentDocumentPreview() {
        const docTypeSelect = document.getElementById('pres_document_type');
        const docNumberInput = document.getElementById('pres_document_number');
        const permSelect = document.getElementById('pres_permission_id');
        const titleInput = document.getElementById('pres_title');
        const summaryInput = document.getElementById('pres_summary_content');
        const sigTextInput = document.getElementById('pres_signature_text');

        const previewType = document.getElementById('docPreviewType');
        const previewNumber = document.getElementById('docPreviewNumber');
        const previewTitle = document.getElementById('docPreviewTitle');
        const previewMenimbangList = document.getElementById('docPreviewMenimbangList');
        const previewMengingatList = document.getElementById('docPreviewMengingatList');
        const previewMenetapkan = document.getElementById('docPreviewMenetapkan');
        const previewPasalContainer = document.getElementById('docPreviewPasalContainer');
        const previewSigScript = document.getElementById('docPreviewSignatureScript');
        const formSigTextPreview = document.getElementById('formSigTextPreview');

        const menimbangContainer = document.getElementById('menimbangInputsContainer');
        const addMenimbangBtn = document.getElementById('addMenimbangItemBtn');

        const mengingatContainer = document.getElementById('mengingatInputsContainer');
        const addMengingatBtn = document.getElementById('addMengingatItemBtn');

        const pasalContainer = document.getElementById('pasalInputsContainer');
        const addPasalBtn = document.getElementById('addPasalBtn');

        const downloadPdfBtn = document.getElementById('downloadPdfBtn');
        const printDocBtn = document.getElementById('printDocBtn');

        if (!docTypeSelect || !docNumberInput || !titleInput || !summaryInput) {
            return;
        }

        const typeLabels = {
            'PERPRES': 'PERATURAN PRESIDEN REPUBLIK INDONESIA',
            'KEPPRES': 'KEPUTUSAN PRESIDEN REPUBLIK INDONESIA',
            'PP': 'PERATURAN PEMERINTAH REPUBLIK INDONESIA',
            'PERPPU': 'PERATURAN PEMERINTAH PENGGANTI UNDANG-UNDANG',
            'NOTA_KEUANGAN': 'NOTA KEUANGAN & RANCANGAN APBN',
        };

        const typeShortLabels = {
            'PERPRES': 'PERATURAN PRESIDEN',
            'KEPPRES': 'KEPUTUSAN PRESIDEN',
            'PP': 'PERATURAN PEMERINTAH',
            'PERPPU': 'PERATURAN PEMERINTAH PENGGANTI UNDANG-UNDANG',
            'NOTA_KEUANGAN': 'NOTA KEUANGAN NEGARA',
        };

        const alphabet = 'abcdefghijklmnopqrstuvwxyz'.split('');

        /** @type {string[]} Dynamic list of consideration points for Menimbang */
        let menimbangPoints = [
            'bahwa untuk mewujudkan kemandirian ekonomi, kedaulatan industri strategis, serta perlindungan kepentingan umum nasional, diperlukan percepatan tata kelola pembangunan yang terpadu dan berkelanjutan;',
            'bahwa penyelenggaraan kebijakan strategis memerlukan koordinasi lintas kementerian dan integrasi perizinan terpadu;',
        ];

        /** @type {string[]} Dynamic list of legal basis points for Mengingat */
        let mengingatPoints = [
            'Pasal 4 ayat (1) Undang-Undang Dasar Negara Republik Indonesia Tahun 1945;',
            'Undang-Undang Nomor 12 Tahun 2011 tentang Pembentukan Peraturan Perundang-undangan sebagaimana telah diubah terakhir dengan Undang-Undang Nomor 13 Tahun 2022;',
        ];

        /** @type {Array<{content: string}>} Dynamic list of articles for the decree */
        let pasalList = [
            {
                content: 'Menetapkan langkah-langkah terpadu dan strategis percepatan hilirisasi industri nasional, fasilitasi perizinan terpadu, serta pemberian insentif fiskal bagi pengembangan fasilitas pengolahan bahan mentah dalam negeri.',
            },
            {
                content: 'Peraturan ini mulai berlaku pada tanggal diundangkan dan dicatat dalam Lembaran Berita Negara Resmi Discord Indonesia Simulator.',
            },
        ];

        /**
         * Renders the interactive input fields for each Menimbang item with letter badges (a, b, c...)
         * and custom delete buttons.
         *
         * @returns {void}
         */
        function renderMenimbangInputs() {
            if (!menimbangContainer) {
                return;
            }

            menimbangContainer.innerHTML = '';

            menimbangPoints.forEach((pointText, index) => {
                const letter = alphabet[index] || `(${index + 1})`;
                const row = document.createElement('div');
                row.className = 'flex items-start gap-2 group';

                const badge = document.createElement('span');
                badge.className = 'shrink-0 w-7 h-7 mt-0.5 rounded-md bg-white border border-slate-300 text-slate-700 font-mono text-xs font-bold flex items-center justify-center shadow-2xs';
                badge.textContent = `${letter}.`;

                const textarea = document.createElement('textarea');
                textarea.rows = 2;
                textarea.value = pointText;
                textarea.placeholder = `Uraikan pertimbangan konsideran huruf ${letter}...`;
                textarea.className = 'flex-1 px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors leading-relaxed';
                textarea.addEventListener('input', (event) => {
                    menimbangPoints[index] = event.target.value;
                    updatePreview();
                });

                row.appendChild(badge);
                row.appendChild(textarea);

                if (menimbangPoints.length > 1) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.title = `Hapus butir ${letter}`;
                    deleteBtn.className = 'shrink-0 p-1.5 mt-1 rounded text-slate-400 hover:text-crimson-600 hover:bg-slate-200/60 transition-colors cursor-pointer';
                    deleteBtn.innerHTML = `
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    `;
                    deleteBtn.addEventListener('click', () => {
                        menimbangPoints.splice(index, 1);
                        renderMenimbangInputs();
                        updatePreview();
                    });
                    row.appendChild(deleteBtn);
                }

                menimbangContainer.appendChild(row);
            });
        }

        /**
         * Renders the interactive input fields for each Mengingat legal basis item with numeric badges (1, 2, 3...)
         * and custom delete buttons.
         *
         * @returns {void}
         */
        function renderMengingatInputs() {
            if (!mengingatContainer) {
                return;
            }

            mengingatContainer.innerHTML = '';

            mengingatPoints.forEach((pointText, index) => {
                const num = index + 1;
                const row = document.createElement('div');
                row.className = 'flex items-start gap-2 group';

                const badge = document.createElement('span');
                badge.className = 'shrink-0 w-7 h-7 mt-0.5 rounded-md bg-white border border-slate-300 text-slate-700 font-mono text-xs font-bold flex items-center justify-center shadow-2xs';
                badge.textContent = `${num}.`;

                const textarea = document.createElement('textarea');
                textarea.rows = 2;
                textarea.value = pointText;
                textarea.placeholder = `Uraikan rujukan dasar hukum nomor ${num}...`;
                textarea.className = 'flex-1 px-3 py-1.5 rounded-lg bg-white border border-slate-300 text-xs text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 transition-colors leading-relaxed';
                textarea.addEventListener('input', (event) => {
                    mengingatPoints[index] = event.target.value;
                    updatePreview();
                });

                row.appendChild(badge);
                row.appendChild(textarea);

                if (mengingatPoints.length > 1) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.title = `Hapus dasar hukum nomor ${num}`;
                    deleteBtn.className = 'shrink-0 p-1.5 mt-1 rounded text-slate-400 hover:text-crimson-600 hover:bg-slate-200/60 transition-colors cursor-pointer';
                    deleteBtn.innerHTML = `
                        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <line x1="18" y1="6" x2="6" y2="18"></line>
                            <line x1="6" y1="6" x2="18" y2="18"></line>
                        </svg>
                    `;
                    deleteBtn.addEventListener('click', () => {
                        mengingatPoints.splice(index, 1);
                        renderMengingatInputs();
                        updatePreview();
                    });
                    row.appendChild(deleteBtn);
                }

                mengingatContainer.appendChild(row);
            });
        }

        /**
         * Renders the interactive input fields for each Pasal with dynamic numbering (Pasal 1, Pasal 2, ...)
         * and custom delete buttons.
         *
         * @returns {void}
         */
        function renderPasalInputs() {
            if (!pasalContainer) {
                return;
            }

            pasalContainer.innerHTML = '';

            pasalList.forEach((pasal, index) => {
                const pasalNum = index + 1;
                const card = document.createElement('div');
                card.className = 'p-3.5 sm:p-4 rounded-xl bg-white border border-slate-200 space-y-2.5 shadow-2xs transition-all';

                const header = document.createElement('div');
                header.className = 'flex items-center justify-between';

                const badge = document.createElement('span');
                badge.className = 'inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded bg-slate-100 text-slate-800 text-xs font-bold font-mono border border-slate-200';
                badge.textContent = `Pasal ${pasalNum}`;
                header.appendChild(badge);

                if (pasalList.length > 1) {
                    const deleteBtn = document.createElement('button');
                    deleteBtn.type = 'button';
                    deleteBtn.title = `Hapus Pasal ${pasalNum}`;
                    deleteBtn.className = 'inline-flex items-center gap-1 px-2 py-1 rounded text-xs font-medium text-slate-500 hover:text-crimson-600 hover:bg-crimson-50 transition-colors cursor-pointer';
                    deleteBtn.innerHTML = `
                        <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path d="M3 6h18"></path>
                            <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                            <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                        </svg>
                        <span>Hapus Pasal</span>
                    `;
                    deleteBtn.addEventListener('click', () => {
                        pasalList.splice(index, 1);
                        renderPasalInputs();
                        updatePreview();
                    });
                    header.appendChild(deleteBtn);
                }

                const textarea = document.createElement('textarea');
                textarea.rows = 3;
                textarea.value = pasal.content;
                textarea.placeholder = `Ketik rumusan norma, substansi perintah, atau ketentuan Pasal ${pasalNum}...`;
                textarea.className = 'w-full px-3 py-2 rounded-lg bg-slate-50 border border-slate-300 text-xs sm:text-sm text-slate-900 placeholder-slate-400 focus:outline-none focus:border-slate-900 focus:bg-white transition-colors leading-relaxed';
                textarea.addEventListener('input', (event) => {
                    pasalList[index].content = event.target.value;
                    updatePreview();
                });

                card.appendChild(header);
                card.appendChild(textarea);
                pasalContainer.appendChild(card);
            });
        }

        if (addMenimbangBtn) {
            addMenimbangBtn.addEventListener('click', () => {
                if (menimbangPoints.length >= 15) {
                    showToast('Maksimal 15 butir konsideran menimbang.', 'info');
                    return;
                }
                menimbangPoints.push('');
                renderMenimbangInputs();
                updatePreview();

                const inputs = menimbangContainer.querySelectorAll('textarea');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            });
        }

        if (addMengingatBtn) {
            addMengingatBtn.addEventListener('click', () => {
                if (mengingatPoints.length >= 15) {
                    showToast('Maksimal 15 butir dasar hukum mengingat.', 'info');
                    return;
                }
                mengingatPoints.push('');
                renderMengingatInputs();
                updatePreview();

                const inputs = mengingatContainer.querySelectorAll('textarea');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            });
        }

        if (addPasalBtn) {
            addPasalBtn.addEventListener('click', () => {
                if (pasalList.length >= 30) {
                    showToast('Maksimal 30 pasal dalam satu regulasi.', 'info');
                    return;
                }
                pasalList.push({ content: '' });
                renderPasalInputs();
                updatePreview();

                const inputs = pasalContainer.querySelectorAll('textarea');
                if (inputs.length > 0) {
                    inputs[inputs.length - 1].focus();
                }
            });
        }

        /**
         * Updates the Menimbang block in the official document preview.
         * Appends the authentic Indonesian legislative concluding point (e.g. huruf a dan huruf b, perlu menetapkan...).
         *
         * @param {string} shortType - Abbreviated regulation type.
         * @param {string} title - Regulation subject title.
         * @returns {void}
         */
        function updateMenimbangPreview(shortType, title) {
            if (!previewMenimbangList) {
                return;
            }

            previewMenimbangList.innerHTML = '';

            menimbangPoints.forEach((point, index) => {
                const letter = alphabet[index] || `(${index + 1})`;
                const cleanText = point.trim() || '...';
                const itemEl = document.createElement('div');
                itemEl.className = 'flex items-start gap-2';
                itemEl.innerHTML = `
                    <span class="shrink-0 font-medium">${letter}.</span>
                    <span class="leading-relaxed">${escapeHtml(cleanText)}</span>
                `;
                previewMenimbangList.appendChild(itemEl);
            });

            const count = menimbangPoints.length;
            let refPhrase = '';
            if (count === 1) {
                refPhrase = 'huruf a';
            } else if (count === 2) {
                refPhrase = 'huruf a dan huruf b';
            } else if (count === 3) {
                refPhrase = 'huruf a, huruf b, dan huruf c';
            } else {
                const lastLetter = alphabet[count - 1] || `${count}`;
                refPhrase = `huruf a sampai dengan huruf ${lastLetter}`;
            }

            const concludingLetter = alphabet[count] || `(${count + 1})`;
            const concludingEl = document.createElement('div');
            concludingEl.className = 'flex items-start gap-2';
            concludingEl.innerHTML = `
                <span class="shrink-0 font-medium">${concludingLetter}.</span>
                <span class="leading-relaxed">bahwa berdasarkan pertimbangan sebagaimana dimaksud dalam ${refPhrase}, perlu menetapkan <span class="font-semibold">${escapeHtml(shortType)} tentang ${escapeHtml(title)};</span></span>
            `;
            previewMenimbangList.appendChild(concludingEl);
        }

        /**
         * Updates the Mengingat block in the official document preview with numbered legal references.
         *
         * @returns {void}
         */
        function updateMengingatPreview() {
            if (!previewMengingatList) {
                return;
            }

            previewMengingatList.innerHTML = '';

            mengingatPoints.forEach((point, index) => {
                const cleanText = point.trim() || '...';
                const itemEl = document.createElement('div');
                itemEl.className = 'flex items-start gap-2';
                itemEl.innerHTML = `
                    <span class="shrink-0 font-medium">${index + 1}.</span>
                    <span class="leading-relaxed">${escapeHtml(cleanText)}</span>
                `;
                previewMengingatList.appendChild(itemEl);
            });
        }

        /**
         * Updates the dynamic Pasal container in the official document preview.
         *
         * @returns {void}
         */
        function updatePasalPreview() {
            if (!previewPasalContainer) {
                return;
            }

            previewPasalContainer.innerHTML = '';

            pasalList.forEach((pasal, index) => {
                const cleanText = pasal.content.trim() || '...';
                const pasalEl = document.createElement('div');
                pasalEl.className = 'mt-3.5 space-y-1.5';
                pasalEl.innerHTML = `
                    <p class="font-bold text-center">Pasal ${index + 1}</p>
                    <p class="text-justify indent-8 leading-relaxed whitespace-pre-line">${escapeHtml(cleanText)}</p>
                `;
                previewPasalContainer.appendChild(pasalEl);
            });

            // Compile all pasal into hidden summaryInput for backend persistence
            const compiledSummary = pasalList
                .map((p, idx) => `Pasal ${idx + 1}\n${p.content.trim()}`)
                .join('\n\n');
            if (summaryInput) {
                summaryInput.value = compiledSummary;
            }
        }

        /**
         * Synchronizes all form inputs with the live document preview.
         *
         * @returns {void}
         */
        function updatePreview() {
            const selectedType = docTypeSelect.value || 'PERPRES';
            const docNumber = docNumberInput.value.trim() || 'PERPRES-18/2026';
            const title = titleInput.value.trim() || 'Percepatan Pembangunan Kawasan Industri Hilirisasi Nasional';

            const fullType = typeLabels[selectedType] || 'PERATURAN PRESIDEN REPUBLIK INDONESIA';
            const shortType = typeShortLabels[selectedType] || 'PERATURAN PRESIDEN';

            if (previewType) {
                previewType.textContent = fullType;
            }
            if (previewNumber) {
                previewNumber.textContent = docNumber.toUpperCase().startsWith('NOMOR') ? docNumber.toUpperCase() : `NOMOR ${docNumber.toUpperCase()}`;
            }
            if (previewTitle) {
                previewTitle.textContent = title.toUpperCase();
            }

            updateMenimbangPreview(shortType, title);
            updateMengingatPreview();

            if (previewMenetapkan) {
                previewMenetapkan.textContent = `${shortType} TENTANG ${title.toUpperCase()}.`;
            }

            updatePasalPreview();

            if (sigTextInput) {
                const sigText = sigTextInput.value.trim() || 'Pratama Wijaya';
                if (previewSigScript) {
                    previewSigScript.textContent = sigText;
                }
                if (formSigTextPreview) {
                    formSigTextPreview.textContent = sigText;
                }
            }
        }

        // Initialize dynamic controls
        renderMenimbangInputs();
        renderMengingatInputs();
        renderPasalInputs();

        // Listen for standard form input changes
        docTypeSelect.addEventListener('change', updatePreview);
        docNumberInput.addEventListener('input', updatePreview);
        if (permSelect) {
            permSelect.addEventListener('change', updatePreview);
        }
        titleInput.addEventListener('input', updatePreview);
        if (sigTextInput) {
            sigTextInput.addEventListener('input', updatePreview);
        }

        updatePreview();

        // ==========================================
        // PDF Export & Print Document Actions
        // ==========================================
        if (downloadPdfBtn) {
            downloadPdfBtn.addEventListener('click', () => {
                const sheet = document.getElementById('officialDocumentSheet');
                if (!sheet) {
                    return;
                }

                if (typeof html2pdf === 'undefined') {
                    showToast('Modul PDF sedang dimuat. Mengalihkan ke opsi cetak...', 'info');
                    window.print();
                    return;
                }

                const docNumber = docNumberInput.value.trim() || 'Naskah_Kepresidenan';
                const safeFileName = docNumber.replace(/[^a-zA-Z0-9_-]/g, '_') + '.pdf';
                const originalHtml = downloadPdfBtn.innerHTML;

                downloadPdfBtn.disabled = true;
                downloadPdfBtn.innerHTML = `
                    <svg class="w-3.5 h-3.5 animate-spin" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                        <circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle>
                        <path d="M12 2a10 10 0 0 1 10 10" stroke-opacity="1"></path>
                    </svg>
                    <span>Membuat PDF...</span>
                `;

                const opt = {
                    margin: [12, 12, 12, 12],
                    filename: safeFileName,
                    image: { type: 'jpeg', quality: 0.98 },
                    html2canvas: { scale: 2, useCORS: true, letterRendering: true, logging: false },
                    jsPDF: { unit: 'mm', format: 'a4', orientation: 'portrait' },
                    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] },
                };

                html2pdf()
                    .set(opt)
                    .from(sheet)
                    .save()
                    .then(() => {
                        showToast(`Naskah ${docNumber} berhasil diunduh sebagai PDF resmi (1 lembar cetak).`, 'success');
                    })
                    .catch((err) => {
                        console.error('PDF Export Error:', err);
                        showToast('Gagal memproses PDF otomatis. Gunakan opsi Cetak/Simpan PDF browser.', 'error');
                        window.print();
                    })
                    .finally(() => {
                        downloadPdfBtn.disabled = false;
                        downloadPdfBtn.innerHTML = originalHtml;
                    });
            });
        }

        if (printDocBtn) {
            printDocBtn.addEventListener('click', () => {
                window.print();
            });
        }
    }

    /**
     * Submits the confirmed Presidential or Vice-Presidential act to the backend controller.
     *
     * @returns {Promise<void>}
     */
    async function submitConfirmedExecutiveAction() {
        if (!pendingExecutiveAction) {
            return;
        }

        const { form, endpoint, prefix } = pendingExecutiveAction;
        const submitBtn = document.getElementById(`${prefix}ActSubmitBtn`);
        const originalHtml = submitBtn ? submitBtn.innerHTML : '';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span>Mengesahkan Naskah...</span>';
        }

        try {
            const formData = new FormData(form);
            const response = await fetch(endpoint, {
                method: 'POST',
                body: formData,
                headers: { 'Accept': 'application/json' },
            });

            const result = await response.json();

            if (response.status === 422 || response.status === 403 || result.status === 'validation_error') {
                displayExecutiveActErrors(prefix, result.errors || {}, result.message);
                showToast(result.message || 'Naskah belum memenuhi syarat.', 'error');
                return;
            }

            if (result.status === 'success') {
                showToast(result.message, 'success');
                form.reset();
                setTimeout(() => {
                    window.location.reload();
                }, 600);
            }
        } catch (error) {
            showToast('Gagal menerbitkan naskah keputusan negara.', 'error');
        } finally {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalHtml;
            }
            pendingExecutiveAction = null;
        }
    }

    /**
     * Initializes all event listeners once the DOM is ready.
     *
     * @returns {void}
     */
    function initLandingApp() {
        const citizenshipModal = document.getElementById('citizenshipModal');
        const loginModal = document.getElementById('loginModal');
        const execConfirmModal = document.getElementById('executiveActConfirmModal');

        document.addEventListener('click', (event) => {
            // Open Registration Modal
            const openRegBtn = event.target.closest('[data-open-citizenship-modal]');
            if (openRegBtn && citizenshipModal) {
                citizenshipModal.classList.remove('hidden');
                citizenshipModal.classList.add('flex');
                return;
            }

            // Close Registration Modal
            const closeRegBtn = event.target.closest('[data-close-citizenship-modal]');
            if (closeRegBtn && citizenshipModal) {
                citizenshipModal.classList.add('hidden');
                citizenshipModal.classList.remove('flex');
                return;
            }

            // Open Login Modal
            const openLoginBtn = event.target.closest('[data-open-login-modal]');
            if (openLoginBtn && loginModal) {
                loginModal.classList.remove('hidden');
                loginModal.classList.add('flex');
                return;
            }

            // Close Login Modal
            const closeLoginBtn = event.target.closest('[data-close-login-modal]');
            if (closeLoginBtn && loginModal) {
                loginModal.classList.add('hidden');
                loginModal.classList.remove('flex');
                return;
            }

            // Switch from Login to Register
            const switchToRegBtn = event.target.closest('[data-switch-to-register]');
            if (switchToRegBtn && loginModal && citizenshipModal) {
                loginModal.classList.add('hidden');
                loginModal.classList.remove('flex');
                citizenshipModal.classList.remove('hidden');
                citizenshipModal.classList.add('flex');
                return;
            }

            // Switch from Register to Login
            const switchToLoginBtn = event.target.closest('[data-switch-to-login]');
            if (switchToLoginBtn && loginModal && citizenshipModal) {
                citizenshipModal.classList.add('hidden');
                citizenshipModal.classList.remove('flex');
                loginModal.classList.remove('hidden');
                loginModal.classList.add('flex');
                return;
            }
        });

        const logoutBtn = document.getElementById('logoutBtn');
        if (logoutBtn) {
            logoutBtn.addEventListener('click', () => {
                handleLogout();
            });
        }

        const mobileToggleBtn = document.getElementById('mobileMenuToggleBtn');
        const mobileDrawer = document.getElementById('mobileNavDrawer');
        if (mobileToggleBtn && mobileDrawer) {
            mobileToggleBtn.addEventListener('click', () => {
                const isExpanded = mobileToggleBtn.getAttribute('aria-expanded') === 'true';
                mobileToggleBtn.setAttribute('aria-expanded', String(!isExpanded));
                mobileDrawer.classList.toggle('hidden');
            });
        }

        const backstoryInput = document.getElementById('character_backstory');
        const backstoryCounter = document.getElementById('backstoryCharCounter');
        if (backstoryInput && backstoryCounter) {
            backstoryInput.addEventListener('input', () => {
                const len = backstoryInput.value.length;
                backstoryCounter.textContent = `${len} / 500`;
                backstoryCounter.className = len > 500
                    ? 'text-xs font-mono text-crimson-600'
                    : 'text-xs font-mono text-slate-400';
            });
        }

        const citizenshipForm = document.getElementById('citizenshipForm');
        const oathConfirmModal = document.getElementById('oathConfirmationModal');
        const cancelOathBtn = document.getElementById('cancelOathConfirmBtn');
        const proceedOathBtn = document.getElementById('proceedOathConfirmBtn');
        const resetFormBtn = document.getElementById('resetCitizenshipFormBtn');

        if (citizenshipForm) {
            citizenshipForm.addEventListener('submit', (event) => {
                event.preventDefault();
                clearFormValidationErrors();

                const formData = new FormData(citizenshipForm);
                const clientErrors = validateCitizenshipClientSide(formData);

                if (Object.keys(clientErrors).length > 0) {
                    displayFormValidationErrors(
                        clientErrors,
                        'Terdapat kolom pendaftaran yang belum lengkap. Silakan periksa keterangan di bawah kolom.'
                    );
                    showToast('Mohon lengkapi data pendaftaran sesuai ketentuan.', 'error');
                    return;
                }

                const charNamePreview = document.getElementById('confirmCharNamePreview');
                if (charNamePreview) {
                    charNamePreview.textContent = String(formData.get('character_name') || '').trim();
                }
                if (oathConfirmModal) {
                    oathConfirmModal.classList.remove('hidden');
                    oathConfirmModal.classList.add('flex');
                }
            });
        }

        if (cancelOathBtn && oathConfirmModal) {
            cancelOathBtn.addEventListener('click', () => {
                oathConfirmModal.classList.add('hidden');
                oathConfirmModal.classList.remove('flex');
            });
        }

        if (proceedOathBtn && oathConfirmModal && citizenshipForm) {
            proceedOathBtn.addEventListener('click', () => {
                oathConfirmModal.classList.add('hidden');
                oathConfirmModal.classList.remove('flex');
                submitValidatedCitizenshipForm(citizenshipForm);
            });
        }

        if (resetFormBtn && citizenshipForm) {
            resetFormBtn.addEventListener('click', () => {
                citizenshipForm.reset();
                clearFormValidationErrors();
                const successPanel = document.getElementById('citizenshipSuccessPanel');
                if (successPanel) {
                    successPanel.classList.add('hidden');
                }
                citizenshipForm.classList.remove('hidden');
                if (backstoryCounter) {
                    backstoryCounter.textContent = '0 / 500';
                }
            });
        }

        // Login Form Handler
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', (event) => {
                event.preventDefault();
                handleLoginSubmit(loginForm);
            });
        }

        // Presidential Act Form Handler (Menu Presiden RI)
        initPresidentDocumentPreview();

        const presForm = document.getElementById('presidentialActForm');
        if (presForm) {
            presForm.addEventListener('submit', (event) => {
                event.preventDefault();
                clearExecutiveActErrors('pres');

                const formData = new FormData(presForm);
                const errors = validateExecutiveActClientSide(formData);
                if (Object.keys(errors).length > 0) {
                    displayExecutiveActErrors('pres', errors, 'Harap lengkapi seluruh kolom naskah Keputusan/Peraturan Presiden.');
                    showToast('Mohon lengkapi data naskah Presiden sesuai ketentuan.', 'error');
                    return;
                }

                pendingExecutiveAction = {
                    form: presForm,
                    endpoint: '?action=issue-presidential-act',
                    prefix: 'pres',
                };

                const docPreview = document.getElementById('execConfirmDocPreview');
                if (docPreview) {
                    docPreview.textContent = `${formData.get('document_number')} — ${formData.get('title')}`;
                }
                if (execConfirmModal) {
                    execConfirmModal.classList.remove('hidden');
                    execConfirmModal.classList.add('flex');
                }
            });
        }

        // Vice-Presidential Act Form Handler (Menu Wakil Presiden RI)
        const vpForm = document.getElementById('vicePresidentialActForm');
        if (vpForm) {
            vpForm.addEventListener('submit', (event) => {
                event.preventDefault();
                clearExecutiveActErrors('vp');

                const formData = new FormData(vpForm);
                const errors = validateExecutiveActClientSide(formData);
                if (Object.keys(errors).length > 0) {
                    displayExecutiveActErrors('vp', errors, 'Harap lengkapi seluruh kolom Instruksi/Risalah Wakil Presiden.');
                    showToast('Mohon lengkapi data naskah Wakil Presiden sesuai ketentuan.', 'error');
                    return;
                }

                pendingExecutiveAction = {
                    form: vpForm,
                    endpoint: '?action=issue-vice-presidential-act',
                    prefix: 'vp',
                };

                const docPreview = document.getElementById('execConfirmDocPreview');
                if (docPreview) {
                    docPreview.textContent = `${formData.get('document_number')} — ${formData.get('title')}`;
                }
                if (execConfirmModal) {
                    execConfirmModal.classList.remove('hidden');
                    execConfirmModal.classList.add('flex');
                }
            });
        }

        const cancelExecBtn = document.getElementById('cancelExecActConfirmBtn');
        const proceedExecBtn = document.getElementById('proceedExecActConfirmBtn');

        if (cancelExecBtn && execConfirmModal) {
            cancelExecBtn.addEventListener('click', () => {
                execConfirmModal.classList.add('hidden');
                execConfirmModal.classList.remove('flex');
                pendingExecutiveAction = null;
            });
        }

        if (proceedExecBtn && execConfirmModal) {
            proceedExecBtn.addEventListener('click', () => {
                execConfirmModal.classList.add('hidden');
                execConfirmModal.classList.remove('flex');
                submitConfirmedExecutiveAction();
            });
        }

        // Initialize Presidential Cabinet Management
        initCabinetManagement();
    }

    /**
     * Initializes the Cabinet Management interface:
     * - Update Cabinet Name & Vision modal & submission
     * - Ministerial appointment with eligibility validation & confirmation modal
     * - Ministerial dismissal with confirmation modal & optional reasoning
     *
     * @returns {void}
     */
    function initCabinetManagement() {
        // 1. Edit Cabinet Name Modal
        const editCabinetModal = document.getElementById('updateCabinetModal');
        const openEditCabinetBtn = document.getElementById('openEditCabinetNameBtn');
        const closeEditCabinetBtn = document.getElementById('closeEditCabinetModalBtn');
        const cancelEditCabinetBtn = document.getElementById('cancelEditCabinetBtn');
        const editCabinetForm = document.getElementById('editCabinetForm');
        const cabinetFormError = document.getElementById('cabinetFormError');

        if (openEditCabinetBtn && editCabinetModal) {
            openEditCabinetBtn.addEventListener('click', () => {
                if (cabinetFormError) {
                    cabinetFormError.classList.add('hidden');
                    cabinetFormError.textContent = '';
                }
                editCabinetModal.classList.remove('hidden');
                editCabinetModal.classList.add('flex');
            });
        }

        function closeEditCabinet() {
            if (editCabinetModal) {
                editCabinetModal.classList.add('hidden');
                editCabinetModal.classList.remove('flex');
            }
        }

        if (closeEditCabinetBtn) {
            closeEditCabinetBtn.addEventListener('click', closeEditCabinet);
        }
        if (cancelEditCabinetBtn) {
            cancelEditCabinetBtn.addEventListener('click', closeEditCabinet);
        }

        if (editCabinetForm) {
            editCabinetForm.addEventListener('submit', async (event) => {
                event.preventDefault();
                const nameInput = document.getElementById('cabinet_name_input');
                const visionInput = document.getElementById('cabinet_vision_input');
                const saveBtn = document.getElementById('saveCabinetBtn');

                const nameVal = nameInput ? nameInput.value.trim() : '';
                const visionVal = visionInput ? visionInput.value.trim() : '';

                if (nameVal.length < 4 || nameVal.length > 120) {
                    if (cabinetFormError) {
                        cabinetFormError.textContent = 'Nama kabinet harus terdiri dari 4 hingga 120 karakter.';
                        cabinetFormError.classList.remove('hidden');
                    }
                    showToast('Nama kabinet harus 4–120 karakter.', 'error');
                    return;
                }

                if (visionVal.length < 10 || visionVal.length > 255) {
                    if (cabinetFormError) {
                        cabinetFormError.textContent = 'Visi kabinet harus terdiri dari 10 hingga 255 karakter.';
                        cabinetFormError.classList.remove('hidden');
                    }
                    showToast('Visi kabinet harus 10–255 karakter.', 'error');
                    return;
                }

                const originalBtnText = saveBtn ? saveBtn.innerHTML : '';
                if (saveBtn) {
                    saveBtn.disabled = true;
                    saveBtn.textContent = 'Menyimpan...';
                }

                try {
                    const formData = new FormData();
                    formData.append('cabinet_name', nameVal);
                    formData.append('cabinet_vision', visionVal);

                    const response = await fetch('?action=update-cabinet-name', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const result = await response.json();
                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'Gagal memperbarui setelan kabinet.');
                    }

                    const displayName = document.getElementById('displayCabinetName');
                    const displayVision = document.getElementById('displayCabinetVision');
                    if (displayName) displayName.textContent = nameVal;
                    if (displayVision) displayVision.textContent = visionVal;

                    closeEditCabinet();
                    showToast('Nama dan visi kabinet berhasil diperbarui.', 'success');
                } catch (err) {
                    if (cabinetFormError) {
                        cabinetFormError.textContent = err.message || 'Terjadi kesalahan sistem.';
                        cabinetFormError.classList.remove('hidden');
                    }
                    showToast(err.message || 'Gagal menyimpan kabinet.', 'error');
                } finally {
                    if (saveBtn) {
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalBtnText;
                    }
                }
            });
        }

        // 2. Appoint Minister Confirmation Modal & Execution
        const appointModal = document.getElementById('appointMinisterConfirmModal');
        const cancelAppointBtn = document.getElementById('cancelAppointBtn');
        const proceedAppointBtn = document.getElementById('proceedAppointBtn');
        const candidateDisplay = document.getElementById('appointCandidateNameDisplay');
        const ministryDisplay = document.getElementById('appointMinistryNameDisplay');

        let pendingAppoint = null;

        document.querySelectorAll('.appointMinisterForm').forEach((form) => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                const ministryId = form.getAttribute('data-ministry-id');
                const ministryName = form.getAttribute('data-ministry-name');
                const ministerTitle = form.getAttribute('data-minister-title');
                const select = form.querySelector('select[name="candidate_user_id"]');

                if (!select || !select.value) {
                    showToast('Pilih salah satu warga negara yang memenuhi syarat.', 'error');
                    return;
                }

                const candidateUserId = select.value;
                const candidateText = select.options[select.selectedIndex].text.split('•')[0].trim();

                pendingAppoint = {
                    ministryId,
                    candidateUserId,
                    candidateName: candidateText,
                    ministerTitle,
                };

                if (candidateDisplay) candidateDisplay.textContent = candidateText;
                if (ministryDisplay) ministryDisplay.textContent = ministerTitle || ministryName;

                if (appointModal) {
                    appointModal.classList.remove('hidden');
                    appointModal.classList.add('flex');
                }
            });
        });

        if (cancelAppointBtn && appointModal) {
            cancelAppointBtn.addEventListener('click', () => {
                appointModal.classList.add('hidden');
                appointModal.classList.remove('flex');
                pendingAppoint = null;
            });
        }

        if (proceedAppointBtn && appointModal) {
            proceedAppointBtn.addEventListener('click', async () => {
                if (!pendingAppoint) return;

                const origHtml = proceedAppointBtn.innerHTML;
                proceedAppointBtn.disabled = true;
                proceedAppointBtn.textContent = 'Memproses Pelantikan...';

                try {
                    const formData = new FormData();
                    formData.append('ministry_id', pendingAppoint.ministryId);
                    formData.append('candidate_user_id', pendingAppoint.candidateUserId);

                    const response = await fetch('?action=appoint-minister', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const result = await response.json();
                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'Gagal mengangkat menteri.');
                    }

                    appointModal.classList.add('hidden');
                    appointModal.classList.remove('flex');
                    showToast(result.message || 'Menteri berhasil diangkat dan dilantik!', 'success');

                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (err) {
                    showToast(err.message || 'Gagal memproses pelantikan menteri.', 'error');
                    proceedAppointBtn.disabled = false;
                    proceedAppointBtn.innerHTML = origHtml;
                }
            });
        }

        // 3. Dismiss Minister Confirmation Modal & Execution
        const dismissModal = document.getElementById('dismissMinisterConfirmModal');
        const cancelDismissBtn = document.getElementById('cancelDismissBtn');
        const proceedDismissBtn = document.getElementById('proceedDismissBtn');
        const dismissIncumbentDisplay = document.getElementById('dismissIncumbentNameDisplay');
        const dismissMinistryDisplay = document.getElementById('dismissMinistryTitleDisplay');
        const dismissReasonInput = document.getElementById('dismiss_reason_input');

        let pendingDismiss = null;

        document.querySelectorAll('.openDismissMinisterBtn').forEach((btn) => {
            btn.addEventListener('click', () => {
                const ministryId = btn.getAttribute('data-ministry-id');
                const ministerTitle = btn.getAttribute('data-minister-title');
                const incumbentName = btn.getAttribute('data-incumbent-name');

                pendingDismiss = {
                    ministryId,
                    incumbentName,
                    ministerTitle,
                };

                if (dismissIncumbentDisplay) dismissIncumbentDisplay.textContent = incumbentName;
                if (dismissMinistryDisplay) dismissMinistryDisplay.textContent = ministerTitle;
                if (dismissReasonInput) dismissReasonInput.value = '';

                if (dismissModal) {
                    dismissModal.classList.remove('hidden');
                    dismissModal.classList.add('flex');
                }
            });
        });

        if (cancelDismissBtn && dismissModal) {
            cancelDismissBtn.addEventListener('click', () => {
                dismissModal.classList.add('hidden');
                dismissModal.classList.remove('flex');
                pendingDismiss = null;
            });
        }

        if (proceedDismissBtn && dismissModal) {
            proceedDismissBtn.addEventListener('click', async () => {
                if (!pendingDismiss) return;

                const origHtml = proceedDismissBtn.innerHTML;
                proceedDismissBtn.disabled = true;
                proceedDismissBtn.textContent = 'Memproses Pemberhentian...';

                try {
                    const formData = new FormData();
                    formData.append('ministry_id', pendingDismiss.ministryId);
                    if (dismissReasonInput) {
                        formData.append('reason', dismissReasonInput.value.trim());
                    }

                    const response = await fetch('?action=dismiss-minister', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    const result = await response.json();
                    if (!response.ok || result.status !== 'success') {
                        throw new Error(result.message || 'Gagal memberhentikan menteri.');
                    }

                    dismissModal.classList.add('hidden');
                    dismissModal.classList.remove('flex');
                    showToast(result.message || 'Menteri berhasil diberhentikan dengan hormat.', 'success');

                    setTimeout(() => {
                        window.location.reload();
                    }, 800);
                } catch (err) {
                    showToast(err.message || 'Gagal memproses pemberhentian menteri.', 'error');
                    proceedDismissBtn.disabled = false;
                    proceedDismissBtn.innerHTML = origHtml;
                }
            });
        }
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initLandingApp);
    } else {
        initLandingApp();
    }
})();
