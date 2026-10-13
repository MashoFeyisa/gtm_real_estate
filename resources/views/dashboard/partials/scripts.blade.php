    <script>
        // Candidate Details Modal (Admin Dashboard)
        function viewCandidateDetails(app) {
            const modal = document.getElementById('admin-candidate-modal');
            if (!modal) return;

            document.getElementById('modal-c-applied-at').textContent = 'Applied ' + app.applied_at;
            document.getElementById('modal-c-name').textContent = app.name;
            document.getElementById('modal-c-job-title').textContent = 'Position: ' + app.job_title;

            const emailEl = document.getElementById('modal-c-email');
            emailEl.textContent = app.email;
            emailEl.href = 'mailto:' + app.email;

            const phoneEl = document.getElementById('modal-c-phone');
            phoneEl.textContent = app.phone;
            phoneEl.href = 'tel:' + app.phone;

            document.getElementById('modal-c-location').textContent = app.location || 'Not provided';
            document.getElementById('modal-c-start-date').textContent = app.start_date || 'Flexible';
            document.getElementById('modal-c-experience').textContent = app.experience || 'Not specified';
            document.getElementById('modal-c-education').textContent = app.education || 'Not specified';
            document.getElementById('modal-c-salary').textContent = app.expected_salary || 'Negotiable';

            const portWrap = document.getElementById('modal-c-portfolio-wrapper');
            const portEl = document.getElementById('modal-c-portfolio');
            if (app.portfolio) {
                portWrap.classList.remove('hidden');
                portEl.textContent = app.portfolio;
                portEl.href = app.portfolio;
            } else {
                portWrap.classList.add('hidden');
            }

            const resumeBadge = document.getElementById('modal-c-resume-badge');
            if (app.is_pdf) {
                resumeBadge.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e02424] text-white font-black text-xs';
                resumeBadge.textContent = 'PDF';
            } else {
                resumeBadge.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#2563eb] text-white font-black text-xs';
                resumeBadge.textContent = 'DOCX';
            }

            document.getElementById('modal-c-resume-name').textContent = app.resume_name || 'Resume document';
            document.getElementById('modal-c-resume-size').textContent = app.resume_size || '';
            document.getElementById('modal-c-resume-download').href = app.resume_download_url;

            document.getElementById('modal-c-cover-letter').textContent = app.cover_letter || 'No cover letter was included with this application.';

            const statusForm = document.getElementById('modal-c-status-form');
            statusForm.action = app.status_action;
            document.getElementById('modal-c-status-select').value = app.status;

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeCandidateDetailsModal() {
            const modal = document.getElementById('admin-candidate-modal');
            if (modal) {
                modal.classList.add('hidden');
                document.body.classList.remove('overflow-hidden');
            }
        }

        let currentApplicationStatusFilter = 'all';

        function filterApplicationsByStatus(status) {
            currentApplicationStatusFilter = status;

            document.querySelectorAll('.app-status-filter-btn').forEach(btn => {
                const isActive = btn.dataset.status === status;
                btn.className = isActive
                    ? 'app-status-filter-btn rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 transition font-bold text-xs'
                    : 'app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition font-bold text-xs';
            });

            filterJobApplicationsTable();
        }

        function filterJobApplicationsTable() {
            const query = (document.getElementById('dashboard-applications-search')?.value || '').trim().toLowerCase();
            const rows = document.querySelectorAll('.job-application-row');
            const noResultsRow = document.getElementById('dashboard-applications-no-results');
            let visibleCount = 0;

            rows.forEach(row => {
                const text = (row.dataset.search || row.textContent || '').toLowerCase();
                const rowStatus = row.dataset.status;
                const matchesQuery = !query || text.includes(query);
                const matchesStatus = (currentApplicationStatusFilter === 'all') || (rowStatus === currentApplicationStatusFilter);

                const visible = matchesQuery && matchesStatus;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });

            if (noResultsRow) {
                noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
            }
        }

        function filterApplicationsByJobTitle(title) {
            const input = document.getElementById('dashboard-applications-search');
            if (input) {
                input.value = title;
            }
            currentApplicationStatusFilter = 'all';
            document.querySelectorAll('.app-status-filter-btn').forEach(btn => {
                const isActive = btn.dataset.status === 'all';
                btn.className = isActive
                    ? 'app-status-filter-btn rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 transition font-bold text-xs'
                    : 'app-status-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition font-bold text-xs';
            });
            filterJobApplicationsTable();
        }

        const THEME_PRESETS_DATA = @json(\App\Models\SiteSetting::themePresets());

        function applyThemePreset(presetKey) {
            const preset = THEME_PRESETS_DATA[presetKey];
            if (!preset) return;

            const presetInput = document.getElementById('input_theme_preset');
            if (presetInput) presetInput.value = presetKey;

            document.querySelectorAll('.theme-preset-card').forEach(card => {
                const isMatch = card.dataset.presetKey === presetKey;
                card.classList.toggle('border-[#1d3c34]', isMatch);
                card.classList.toggle('bg-[#edf2ee]/40', isMatch);
                card.classList.toggle('shadow-xs', isMatch);
                card.classList.toggle('ring-2', isMatch);
                card.classList.toggle('ring-[#1d3c34]/20', isMatch);
                card.classList.toggle('border-[#e7ddca]', !isMatch);
                card.classList.toggle('bg-[#fdfaf5]', !isMatch);

                const indicator = card.querySelector('.preset-active-indicator');
                if (indicator) indicator.classList.toggle('hidden', !isMatch);
            });

            const colorMap = {
                'theme_primary_color': preset.colors.primary,
                'theme_primary_hover': preset.colors.primary_hover,
                'theme_primary_dark': preset.colors.primary_dark,
                'theme_primary_light': preset.colors.primary_light,
                'theme_secondary_color': preset.colors.secondary,
                'theme_secondary_light': preset.colors.secondary_light,
                'theme_accent_color': preset.colors.accent,
                'theme_accent_dark': preset.colors.accent_dark,
                'theme_accent_light': preset.colors.accent_light,
                'theme_site_bg': preset.colors.site_bg,
                'theme_card_bg': preset.colors.card_bg,
                'theme_text_dark': preset.colors.text_dark,
                'theme_text_muted': preset.colors.text_muted,
            };

            for (const [key, val] of Object.entries(colorMap)) {
                const picker = document.getElementById('picker_' + key);
                const input = document.getElementById('input_' + key);
                if (picker) picker.value = val;
                if (input) input.value = val.toUpperCase();
            }

            updateLiveThemePreview();
        }

        function updateColorFromPicker(key, hexVal) {
            const input = document.getElementById('input_' + key);
            if (input) input.value = hexVal.toUpperCase();
            markCustomPreset();
            updateLiveThemePreview();
        }

        function updateColorFromText(key, textVal) {
            if (/^#[0-9A-Fa-f]{6}$/.test(textVal)) {
                const picker = document.getElementById('picker_' + key);
                if (picker) picker.value = textVal;
                markCustomPreset();
                updateLiveThemePreview();
            }
        }

        function markCustomPreset() {
            const presetInput = document.getElementById('input_theme_preset');
            if (presetInput) presetInput.value = 'custom';
            document.querySelectorAll('.preset-active-indicator').forEach(ind => ind.classList.add('hidden'));
            document.querySelectorAll('.theme-preset-card').forEach(card => {
                card.classList.remove('border-[#1d3c34]', 'bg-[#edf2ee]/40', 'shadow-xs', 'ring-2', 'ring-[#1d3c34]/20');
                card.classList.add('border-[#e7ddca]', 'bg-[#fdfaf5]');
            });
        }

        function resetToDefaultTheme() {
            applyThemePreset('emerald');
        }

        function hexToRgbStr(hex) {
            if (!hex) return '29, 60, 52';
            hex = hex.replace('#', '');
            if (hex.length === 3) hex = hex[0] + hex[0] + hex[1] + hex[1] + hex[2] + hex[2];
            if (hex.length !== 6) return '29, 60, 52';
            const num = parseInt(hex, 16);
            return `${(num >> 16) & 255}, ${(num >> 8) & 255}, ${num & 255}`;
        }

        function updateLiveThemePreview() {
            const primary = document.getElementById('input_theme_primary_color')?.value || '#1d3c34';
            const primaryHover = document.getElementById('input_theme_primary_hover')?.value || '#254d43';
            const primaryDark = document.getElementById('input_theme_primary_dark')?.value || '#102b25';
            const primaryLight = document.getElementById('input_theme_primary_light')?.value || '#dfeee4';
            const secondary = document.getElementById('input_theme_secondary_color')?.value || '#d9cab3';
            const secondaryLight = document.getElementById('input_theme_secondary_light')?.value || '#e7ddca';
            const accent = document.getElementById('input_theme_accent_color')?.value || '#c5a059';
            const accentDark = document.getElementById('input_theme_accent_dark')?.value || '#9b6c17';
            const accentLight = document.getElementById('input_theme_accent_light')?.value || '#f9ecd0';
            const siteBg = document.getElementById('input_theme_site_bg')?.value || '#f6f1e8';
            const cardBg = document.getElementById('input_theme_card_bg')?.value || '#fdfaf5';
            const textDark = document.getElementById('input_theme_text_dark')?.value || '#1d3c34';
            const textMuted = document.getElementById('input_theme_text_muted')?.value || '#587165';

            const root = document.documentElement;
            root.style.setProperty('--theme-primary', primary);
            root.style.setProperty('--theme-primary-hover', primaryHover);
            root.style.setProperty('--theme-primary-dark', primaryDark);
            root.style.setProperty('--theme-primary-light', primaryLight);
            root.style.setProperty('--theme-secondary', secondary);
            root.style.setProperty('--theme-secondary-light', secondaryLight);
            root.style.setProperty('--theme-accent', accent);
            root.style.setProperty('--theme-accent-dark', accentDark);
            root.style.setProperty('--theme-accent-light', accentLight);
            root.style.setProperty('--theme-site-bg', siteBg);
            root.style.setProperty('--theme-card-bg', cardBg);
            root.style.setProperty('--theme-text-dark', textDark);
            root.style.setProperty('--theme-text-muted', textMuted);

            root.style.setProperty('--theme-primary-rgb', hexToRgbStr(primary));
            root.style.setProperty('--theme-primary-dark-rgb', hexToRgbStr(primaryDark));
            root.style.setProperty('--theme-primary-light-rgb', hexToRgbStr(primaryLight));
            root.style.setProperty('--theme-secondary-rgb', hexToRgbStr(secondary));
            root.style.setProperty('--theme-accent-rgb', hexToRgbStr(accent));
            root.style.setProperty('--theme-accent-light-rgb', hexToRgbStr(accentLight));

            const previewBox = document.getElementById('theme-live-preview-box');
            if (previewBox) {
                previewBox.style.backgroundColor = siteBg;
                previewBox.style.borderColor = secondary;
            }

            const prevHeader = document.getElementById('prev-header');
            if (prevHeader) prevHeader.style.backgroundColor = primaryDark;

            const prevLogo = document.getElementById('prev-logo-icon');
            if (prevLogo) {
                prevLogo.style.backgroundColor = primary;
                prevLogo.style.borderColor = secondary;
            }

            const prevNavActive = document.getElementById('prev-nav-active');
            if (prevNavActive) prevNavActive.style.backgroundColor = primary;

            const prevHeaderCta = document.getElementById('prev-header-cta');
            if (prevHeaderCta) prevHeaderCta.style.backgroundColor = primary;

            const prevHeroCard = document.getElementById('prev-hero-card');
            if (prevHeroCard) {
                prevHeroCard.style.backgroundColor = cardBg;
                prevHeroCard.style.borderColor = secondary;
            }

            const prevHeroBadge = document.getElementById('prev-hero-badge');
            if (prevHeroBadge) {
                prevHeroBadge.style.backgroundColor = primaryLight;
                prevHeroBadge.style.color = primary;
            }

            const prevHeroTitle = document.getElementById('prev-hero-title');
            if (prevHeroTitle) prevHeroTitle.style.color = textDark;

            const prevHeroDesc = document.getElementById('prev-hero-desc');
            if (prevHeroDesc) prevHeroDesc.style.color = textMuted;

            const prevBtnPrimary = document.getElementById('prev-btn-primary');
            if (prevBtnPrimary) prevBtnPrimary.style.backgroundColor = primary;

            const prevBtnOutline = document.getElementById('prev-btn-outline');
            if (prevBtnOutline) {
                prevBtnOutline.style.borderColor = secondary;
                prevBtnOutline.style.color = textDark;
                prevBtnOutline.style.backgroundColor = cardBg;
            }

            const prevPropCard = document.getElementById('prev-prop-card');
            if (prevPropCard) {
                prevPropCard.style.backgroundColor = cardBg;
                prevPropCard.style.borderColor = secondary;
            }

            const prevPropGrad = document.getElementById('prev-prop-grad');
            if (prevPropGrad) prevPropGrad.style.background = `linear-gradient(135deg, ${primaryDark} 0%, ${primary} 100%)`;

            const prevPropBadgeFeatured = document.getElementById('prev-prop-badge-featured');
            if (prevPropBadgeFeatured) prevPropBadgeFeatured.style.backgroundColor = accent;

            const prevPropBadgeStatus = document.getElementById('prev-prop-badge-status');
            if (prevPropBadgeStatus) {
                prevPropBadgeStatus.style.backgroundColor = primaryDark;
                prevPropBadgeStatus.style.borderColor = secondary;
            }

            const prevPropPrice = document.getElementById('prev-prop-price');
            if (prevPropPrice) prevPropPrice.style.color = primary;

            const prevPropTitle = document.getElementById('prev-prop-title');
            if (prevPropTitle) prevPropTitle.style.color = textDark;

            const prevPropDesc = document.getElementById('prev-prop-desc');
            if (prevPropDesc) prevPropDesc.style.color = textMuted;

            const prevPropFoot = document.getElementById('prev-prop-foot');
            if (prevPropFoot) prevPropFoot.style.borderColor = secondaryLight;

            const prevPropVerified = document.getElementById('prev-prop-verified');
            if (prevPropVerified) prevPropVerified.style.backgroundColor = accentLight;

            const prevPropVerifiedText = document.getElementById('prev-prop-verified-text');
            if (prevPropVerifiedText) prevPropVerifiedText.style.color = accentDark;

            const prevPropBtn = document.getElementById('prev-prop-btn');
            if (prevPropBtn) prevPropBtn.style.backgroundColor = primary;
        }

        function generateStrongPassword(targetSelector) {
            const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
            const lower = 'abcdefghjkmnpqrstuvwxyz';
            const numbers = '23456789';
            const symbols = '!@#$%&*?+';

            let pwd = '';
            pwd += upper[Math.floor(Math.random() * upper.length)];
            pwd += lower[Math.floor(Math.random() * lower.length)];
            pwd += numbers[Math.floor(Math.random() * numbers.length)];
            pwd += symbols[Math.floor(Math.random() * symbols.length)];

            const all = upper + lower + numbers + symbols;
            for (let i = 4; i < 16; i++) {
                pwd += all[Math.floor(Math.random() * all.length)];
            }
            pwd = pwd.split('').sort(() => 0.5 - Math.random()).join('');

            const input = typeof targetSelector === 'string' ? document.querySelector(targetSelector) : targetSelector;
            if (input) {
                input.value = pwd;
                input.type = 'text';
                navigator.clipboard?.writeText(pwd).catch(() => {});
                alert('Generated Strong Password:\n\n' + pwd + '\n\n(Copied to clipboard and filled in the input field)');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Initialize Admin Overview Charts
            const adminChartData = @json($adminCharts ?? null);
            if (adminChartData && typeof ApexCharts !== 'undefined') {
                // 1. Monthly Trends
                const monthlyEl = document.querySelector("#admin-overview-monthly-chart");
                if (monthlyEl && adminChartData.monthly_trends) {
                    new ApexCharts(monthlyEl, {
                        chart: { type: 'area', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                        series: [
                            { name: 'Sales', type: 'line', data: adminChartData.monthly_trends.sales || [] },
                            { name: 'Rentals', type: 'line', data: adminChartData.monthly_trends.rentals || [] },
                            { name: 'Revenue (ETB)', type: 'column', data: adminChartData.monthly_trends.revenue || [] }
                        ],
                        colors: ['#1d3c34', '#3b82f6', '#d4af37'],
                        stroke: { curve: 'smooth', width: [2.5, 2.5, 0] },
                        fill: { type: ['gradient', 'gradient', 'solid'], gradient: { opacityFrom: 0.3, opacityTo: 0.05 } },
                        xaxis: { categories: adminChartData.monthly_trends.categories || [], labels: { style: { fontSize: '10px' } } },
                        yaxis: [
                            { title: { text: 'Deals', style: { fontSize: '10px' } }, min: 0 },
                            { show: false, min: 0 },
                            { opposite: true, title: { text: 'ETB', style: { fontSize: '10px' } }, labels: { formatter: (v) => v >= 1000000 ? (v/1000000).toFixed(1)+'M' : (v >= 1000 ? (v/1000).toFixed(0)+'K' : v) }, min: 0 }
                        ],
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
                        grid: { borderColor: '#f1f5f9' }
                    }).render();
                }

                // 2. Property Status Donut
                const statusEl = document.querySelector("#admin-overview-status-chart");
                if (statusEl && adminChartData.property_status) {
                    new ApexCharts(statusEl, {
                        chart: { type: 'donut', height: 280, fontFamily: 'inherit' },
                        series: adminChartData.property_status.series || [],
                        labels: adminChartData.property_status.labels || [],
                        colors: ['#1d3c34', '#3b82f6', '#d4af37'],
                        plotOptions: {
                            pie: {
                                donut: {
                                    size: '65%',
                                    labels: {
                                        show: true,
                                        total: { show: true, label: 'Properties', formatter: () => adminChartData.property_status.totals || 0 }
                                    }
                                }
                            }
                        },
                        legend: { position: 'bottom', fontSize: '11px' }
                    }).render();
                }

                // 3. Agent Performance Bar
                const agentEl = document.querySelector("#admin-overview-agent-chart");
                if (agentEl && adminChartData.agent_performance) {
                    new ApexCharts(agentEl, {
                        chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                        series: [
                            { name: 'Sold', data: adminChartData.agent_performance.sold || [] },
                            { name: 'Rented', data: adminChartData.agent_performance.rented || [] }
                        ],
                        colors: ['#1d3c34', '#d4af37'],
                        plotOptions: { bar: { columnWidth: '50%', borderRadius: 4 } },
                        xaxis: { categories: adminChartData.agent_performance.agents || [], labels: { style: { fontSize: '10px' } } },
                        yaxis: { min: 0, forceNiceScale: true },
                        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' },
                        grid: { borderColor: '#f1f5f9' }
                    }).render();
                }

                // 4. Locations Horizontal Bar
                const locationEl = document.querySelector("#admin-overview-location-chart");
                if (locationEl && adminChartData.locations) {
                    new ApexCharts(locationEl, {
                        chart: { type: 'bar', height: 280, toolbar: { show: false }, fontFamily: 'inherit' },
                        plotOptions: { bar: { horizontal: true, barHeight: '50%', borderRadius: 4 } },
                        series: [{ name: 'Properties', data: adminChartData.locations.counts || [] }],
                        colors: ['#d4af37'],
                        xaxis: { categories: adminChartData.locations.cities || [], labels: { style: { fontSize: '10px' } } },
                        grid: { borderColor: '#f1f5f9' }
                    }).render();
                }
            }

            // Initialize Settings Dropside Navigation
            const settingsTabButtons = document.querySelectorAll('.settings-tab-btn');
            const settingsTabPanels = document.querySelectorAll('.settings-tab-panel');
            const settingsDropsideSelect = document.getElementById('settings-dropside-select');

            function activateSettingsTab(targetId) {
                settingsTabPanels.forEach(panel => {
                    const isMatch = panel.id === targetId;
                    panel.classList.toggle('hidden', !isMatch);
                });

                settingsTabButtons.forEach(btn => {
                    const isMatch = btn.dataset.settingsTarget === targetId;
                    btn.classList.toggle('bg-[#1d3c34]', isMatch);
                    btn.classList.toggle('text-white', isMatch);
                    btn.classList.toggle('shadow-sm', isMatch);
                    btn.classList.toggle('text-slate-700', !isMatch);
                    btn.classList.toggle('hover:bg-[#f0e5d8]', !isMatch);

                    const badge = btn.querySelector('.tab-badge');
                    if (badge) {
                        badge.classList.toggle('bg-white/20', isMatch);
                        badge.classList.toggle('text-white', isMatch);
                        badge.classList.toggle('bg-[#ebdcc8]', !isMatch);
                        badge.classList.toggle('text-[#1d3c34]', !isMatch);
                    }
                });

                if (settingsDropsideSelect && settingsDropsideSelect.value !== targetId) {
                    settingsDropsideSelect.value = targetId;
                }
            }

            settingsTabButtons.forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.dataset.settingsTarget;
                    activateSettingsTab(targetId);
                });
            });

            if (settingsDropsideSelect) {
                settingsDropsideSelect.addEventListener('change', function() {
                    activateSettingsTab(this.value);
                });
            }

            document.querySelectorAll('[data-settings-jump]').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const targetId = this.dataset.settingsJump;
                    activateSettingsTab(targetId);
                });
            });

            const navLinks = document.querySelectorAll('.nav-section-link, aside#admin-sidebar a[href^="#"]');
            const panels = document.querySelectorAll('.section-panel');

            function activateSection(sectionName, updateHash = true) {
                if (!sectionName) return;

                if (sectionName === 'message' || sectionName === 'messages') {
                    sectionName = 'inquiries';
                } else if (sectionName === 'developments' || sectionName === 'development') {
                    sectionName = 'properties';
                } else if (sectionName === 'report' || sectionName === 'reports') {
                    sectionName = 'reports';
                }

                panels.forEach(panel => {
                    const visible = panel.dataset.section === sectionName || panel.id === sectionName;
                    panel.classList.toggle('hidden', !visible);
                });

                navLinks.forEach(link => {
                    const target = (link.dataset.targetSection || link.getAttribute('href') || '').replace(/^#/, '');
                    const active = target === sectionName;
                    link.classList.toggle('bg-white/10', active);
                    link.classList.toggle('text-white', active);
                    link.classList.toggle('text-[#dfeee4]', !active);
                    link.classList.toggle('font-semibold', true);
                });

                if (updateHash && window.location.hash !== '#' + sectionName) {
                    history.replaceState(null, '', '#' + sectionName);
                }

                try {
                    sessionStorage.setItem('dashboard_active_section', sectionName);
                    sessionStorage.setItem('dashboard_active_section_' + window.location.pathname, sectionName);
                } catch (e) {}
            }

            // Make sidebar and in-page clicks switch sections with JS (preventDefault, no browser anchor jump)
            document.addEventListener('click', function (event) {
                const link = event.target.closest('.nav-section-link, [data-target-section], aside#admin-sidebar a[href^="#"]');
                if (!link) {
                    return;
                }

                const target = (link.dataset.targetSection || link.getAttribute('href') || '').replace(/^#/, '');
                if (!target || target.startsWith('http') || target.startsWith('/')) {
                    return;
                }

                const targetPanel = document.querySelector('[data-section="' + target + '"], #' + target + '.section-panel');
                if (targetPanel) {
                    event.preventDefault();
                    activateSection(target, true);
                }
            });

            window.activateSection = activateSection;
            window.showDashboardSection = activateSection;

            const params = new URLSearchParams(window.location.search);
            const paramSection = params.get('section');
            const editSection = params.has('edit_post') ? 'blog-posts'
                : params.has('edit_property') ? 'properties'
                : params.has('edit_user') || params.has('edit_agent') ? 'users'
                : null;
            let initialHash = window.location.hash.replace('#', '');
            if (initialHash === 'message' || initialHash === 'messages') {
                initialHash = 'inquiries';
            } else if (initialHash === 'developments' || initialHash === 'development') {
                initialHash = 'properties';
            } else if (initialHash === 'report' || initialHash === 'reports') {
                initialHash = 'reports';
            }

            let savedSessionSection = null;
            try {
                savedSessionSection = sessionStorage.getItem('dashboard_active_section_' + window.location.pathname) || sessionStorage.getItem('dashboard_active_section');
            } catch (e) {}

            let initialSection = initialHash || savedSessionSection || paramSection || editSection || 'overview';

            if (! document.querySelector('[data-section="' + initialSection + '"], #' + initialSection + '.section-panel')) {
                // Unknown/stale section — fall back to a section that exists.
                initialSection = editSection || 'overview';
            }

            if (document.querySelector('[data-section="' + initialSection + '"], #' + initialSection + '.section-panel')) {
                activateSection(initialSection, !!initialHash);
            }

            window.addEventListener('hashchange', function () {
                const newHash = window.location.hash.replace('#', '');
                if (newHash && document.querySelector('[data-section="' + newHash + '"], #' + newHash + '.section-panel')) {
                    activateSection(newHash, false);
                }
            });

            // Show the merged Agent Profile fields only for the Agent role (or existing agent accounts).
            const agentRoleSelect = document.querySelector('#users-create-panel select[name="role"]');
            const agentProfileFields = document.getElementById('agent-profile-fields');
            if (agentRoleSelect && agentProfileFields) {
                const syncAgentProfileFields = function () {
                    const keepOpen = agentProfileFields.dataset.hasAgentProfile === '1';
                    agentProfileFields.classList.toggle('hidden', ! (agentRoleSelect.value === 'agent' || keepOpen));
                };
                agentRoleSelect.addEventListener('change', syncAgentProfileFields);
                syncAgentProfileFields();
            }

            // Real-time avatar image preview for admin profile edit
            const adminProfilePhotoInput = document.getElementById('admin-profile-photo-input');
            const adminProfileAvatarPreview = document.getElementById('admin-profile-avatar-preview');
            if (adminProfilePhotoInput && adminProfileAvatarPreview) {
                adminProfilePhotoInput.addEventListener('change', function () {
                    const file = this.files && this.files[0];
                    if (file && file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            if (adminProfileAvatarPreview.tagName.toLowerCase() === 'img') {
                                adminProfileAvatarPreview.src = e.target.result;
                            } else {
                                const newImg = document.createElement('img');
                                newImg.id = 'admin-profile-avatar-preview';
                                newImg.src = e.target.result;
                                newImg.alt = 'Profile Photo Preview';
                                newImg.className = 'h-24 w-24 rounded-full object-cover ring-4 ring-[#1d3c34]/20 shadow-md';
                                adminProfileAvatarPreview.parentNode.replaceChild(newImg, adminProfileAvatarPreview);
                            }
                        };
                        reader.readAsDataURL(file);
                    }
                });
            }
            // User role filter & search for Unified Users Table (Finance Officer, Receptionist, Agent, Admin)
            let currentUserRoleFilter = 'all';
            window.filterUsersByRole = function (role) {
                currentUserRoleFilter = role;
                document.querySelectorAll('.user-role-filter-btn').forEach(btn => {
                    const match = btn.dataset.role === role;
                    btn.className = match
                        ? 'user-role-filter-btn rounded-lg bg-[#1d3c34] text-white px-3 py-1.5 transition font-bold text-xs'
                        : 'user-role-filter-btn rounded-lg bg-white border border-[#d9cab3] text-slate-700 px-3 py-1.5 hover:bg-[#fbf9f5] transition font-bold text-xs';
                });
                filterUsersTable();
            };

            function filterUsersTable() {
                const searchInput = document.getElementById('dashboard-users-search');
                const clearBtn = document.getElementById('dashboard-users-search-clear');
                const query = (searchInput?.value || '').trim().toLowerCase();
                const countBadge = document.getElementById('dashboard-users-count');
                const noResults = document.getElementById('dashboard-users-no-results');
                const rows = document.querySelectorAll('.dashboard-user-row');
                let visibleCount = 0;

                if (clearBtn) clearBtn.classList.toggle('hidden', !query);

                rows.forEach(row => {
                    const searchData = (row.dataset.search || row.textContent || '').toLowerCase();
                    const rowRole = row.dataset.role || '';
                    const matchesRole = (currentUserRoleFilter === 'all') || (rowRole === currentUserRoleFilter);
                    const matchesQuery = !query || searchData.includes(query);
                    const show = matchesRole && matchesQuery;
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (noResults) {
                    noResults.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
                }
                if (countBadge) {
                    countBadge.textContent = query || currentUserRoleFilter !== 'all'
                        ? visibleCount + ' of ' + rows.length + ' matching'
                        : rows.length + ' users';
                }
            }

            const usersSearchInput = document.getElementById('dashboard-users-search');
            const usersClearBtn = document.getElementById('dashboard-users-search-clear');
            if (usersSearchInput) {
                usersSearchInput.addEventListener('input', filterUsersTable);
            }
            if (usersClearBtn) {
                usersClearBtn.addEventListener('click', function () {
                    usersSearchInput.value = '';
                    filterUsersTable();
                    usersSearchInput.focus();
                });
            }

            // Catalog filtering: All, Properties (100% complete), Developments (<100% in progress)
            let currentCatalogFilter = 'all';
            window.filterCatalogType = function (filterType) {
                currentCatalogFilter = filterType;
                document.querySelectorAll('.catalog-filter-btn').forEach(btn => {
                    const match = btn.dataset.catalogFilter === filterType;
                    btn.classList.toggle('bg-[#1d3c34]', match);
                    btn.classList.toggle('text-white', match);
                    btn.classList.toggle('shadow-xs', match);
                    btn.classList.toggle('border-[#d9cab3]', !match);
                    btn.classList.toggle('bg-white', !match);
                    btn.classList.toggle('text-[#1d3c34]', !match);
                });
                filterCatalogTable();
            };

            function filterCatalogTable() {
                const searchInput = document.getElementById('dashboard-catalog-search');
                const clearBtn = document.getElementById('dashboard-catalog-search-clear');
                const query = (searchInput?.value || '').trim().toLowerCase();
                const countBadge = document.getElementById('dashboard-catalog-count');
                const noResults = document.getElementById('dashboard-catalog-no-results');
                const rows = document.querySelectorAll('.dashboard-catalog-row');
                let visibleCount = 0;

                if (clearBtn) clearBtn.classList.toggle('hidden', !query);

                rows.forEach(row => {
                    const searchData = (row.dataset.search || row.textContent || '').toLowerCase();
                    const rowType = row.dataset.catalogType || 'property';
                    const matchesType = (currentCatalogFilter === 'all') || (rowType === currentCatalogFilter);
                    const matchesQuery = !query || searchData.includes(query);
                    const show = matchesType && matchesQuery;
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (noResults) {
                    noResults.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
                }
                if (countBadge) {
                    countBadge.textContent = query || currentCatalogFilter !== 'all' 
                        ? visibleCount + ' of ' + rows.length + ' matching' 
                        : rows.length + ' items';
                }
            }

            const catalogSearchInput = document.getElementById('dashboard-catalog-search');
            const catalogClearBtn = document.getElementById('dashboard-catalog-search-clear');
            if (catalogSearchInput) {
                catalogSearchInput.addEventListener('input', filterCatalogTable);
            }
            if (catalogClearBtn) {
                catalogClearBtn.addEventListener('click', function () {
                    catalogSearchInput.value = '';
                    filterCatalogTable();
                    catalogSearchInput.focus();
                });
            }

            // Orders filtering: All, Rent, Sold
            let currentOrderFilter = 'all';
            window.filterOrderType = function (filterType) {
                currentOrderFilter = filterType;
                document.querySelectorAll('.order-filter-btn').forEach(btn => {
                    const match = btn.dataset.orderFilter === filterType;
                    btn.classList.toggle('bg-[#1d3c34]', match);
                    btn.classList.toggle('text-white', match);
                    btn.classList.toggle('shadow-xs', match);
                    btn.classList.toggle('border-[#d9cab3]', !match);
                    btn.classList.toggle('bg-white', !match);
                    btn.classList.toggle('text-[#1d3c34]', !match);
                });
                filterOrdersTable();
            };

            function filterOrdersTable() {
                const searchInput = document.getElementById('dashboard-orders-search');
                const clearBtn = document.getElementById('dashboard-orders-search-clear');
                const query = (searchInput?.value || '').trim().toLowerCase();
                const countBadge = document.getElementById('dashboard-orders-count');
                const noResults = document.getElementById('dashboard-orders-no-results');
                const rows = document.querySelectorAll('.dashboard-order-row');
                let visibleCount = 0;

                if (clearBtn) clearBtn.classList.toggle('hidden', !query);

                rows.forEach(row => {
                    const searchData = (row.dataset.search || row.textContent || '').toLowerCase();
                    const rowType = row.dataset.orderType || 'sale';
                    const matchesType = (currentOrderFilter === 'all') || (rowType === currentOrderFilter);
                    const matchesQuery = !query || searchData.includes(query);
                    const show = matchesType && matchesQuery;
                    row.style.display = show ? '' : 'none';
                    if (show) visibleCount++;
                });

                if (noResults) {
                    noResults.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
                }
                if (countBadge) {
                    countBadge.textContent = query || currentOrderFilter !== 'all' 
                        ? visibleCount + ' of ' + rows.length + ' matching' 
                        : rows.length + ' orders';
                }
            }

            const ordersSearchInput = document.getElementById('dashboard-orders-search');
            const ordersClearBtn = document.getElementById('dashboard-orders-search-clear');
            if (ordersSearchInput) {
                ordersSearchInput.addEventListener('input', filterOrdersTable);
            }
            if (ordersClearBtn) {
                ordersClearBtn.addEventListener('click', function () {
                    ordersSearchInput.value = '';
                    filterOrdersTable();
                    ordersSearchInput.focus();
                });
            }

            // "+ Add" buttons toggle their create/edit form panel.
            document.querySelectorAll('.toggle-create-form').forEach(function (button) {
                button.addEventListener('click', function () {
                    const panel = document.getElementById(button.dataset.target);
                    if (! panel) {
                        return;
                    }

                    const willShow = panel.classList.toggle('hidden') === false;
                    button.textContent = willShow ? (button.dataset.labelClose || 'Close Form') : (button.dataset.labelCreate || '+ Add');
                });
            });

            // Agreement modal logic
            const agreementModal = document.getElementById('admin-agreement-modal');
            const agreementForm = document.getElementById('admin-agreement-form');
            const modalTitle = document.getElementById('modal-property-title');
            const modalPrice = document.getElementById('modal-deal-price');
            const modalLeaseContainer = document.getElementById('modal-lease-months-container');

            document.querySelectorAll('.open-agreement-modal').forEach(button => {
                button.addEventListener('click', function () {
                    if (! agreementModal || ! agreementForm) return;
                    const actionUrl = this.dataset.actionUrl;
                    const title = this.dataset.propertyTitle;
                    const price = this.dataset.propertyPrice;
                    const type = this.dataset.propertyType;

                    agreementForm.action = actionUrl;
                    modalTitle.textContent = (type === 'rent' ? 'Rent: ' : 'Sell: ') + title;
                    modalPrice.value = price || '';
                    if (modalLeaseContainer) {
                        modalLeaseContainer.classList.toggle('hidden', type !== 'rent');
                    }
                    agreementModal.classList.remove('hidden');
                });
            });

            const closeAgreementModal = () => {
                if (agreementModal) agreementModal.classList.add('hidden');
            };

            document.getElementById('close-agreement-modal')?.addEventListener('click', closeAgreementModal);
            document.getElementById('cancel-agreement-modal')?.addEventListener('click', closeAgreementModal);
            agreementModal?.addEventListener('click', function (e) {
                if (e.target === agreementModal) closeAgreementModal();
            });

            // Top-right notification icon: preview the latest client messages, then jump to the inquiries section.
            const messagesIcon = document.getElementById('dashboard-messages-icon');
            const messagesDropdown = document.getElementById('dashboard-messages-dropdown');
            if (messagesIcon && messagesDropdown) {
                const closeMessagesDropdown = function () {
                    messagesDropdown.classList.add('hidden');
                    messagesIcon.setAttribute('aria-expanded', 'false');
                };

                messagesIcon.addEventListener('click', function (event) {
                    event.preventDefault();
                    const isOpen = messagesDropdown.classList.toggle('hidden') === false;
                    messagesIcon.setAttribute('aria-expanded', String(isOpen));
                });

                document.addEventListener('click', function (event) {
                    if (!messagesIcon.contains(event.target) && !messagesDropdown.contains(event.target)) {
                        closeMessagesDropdown();
                    }
                });

                document.querySelectorAll('.dashboard-messages-nav-item').forEach(function (item) {
                    item.addEventListener('click', function (event) {
                        const navLink = document.querySelector('.nav-section-link[data-target-section="inquiries"]');
                        if (navLink) {
                            event.preventDefault();
                            closeMessagesDropdown();
                            navLink.click();
                        }
                    });
                });
            }

            // Client Inquiry / Message modal logic
            const inquiryModal = document.getElementById('admin-inquiry-modal');
            const modalInquirySubject = document.getElementById('modal-inquiry-subject');
            const modalInquiryName = document.getElementById('modal-inquiry-name');
            const modalInquiryEmail = document.getElementById('modal-inquiry-email');
            const modalInquiryPhone = document.getElementById('modal-inquiry-phone');
            const modalInquiryAgent = document.getElementById('modal-inquiry-agent');
            const modalInquiryReceived = document.getElementById('modal-inquiry-received');
            const modalInquiryMessage = document.getElementById('modal-inquiry-message');
            const modalInquiryReply = document.getElementById('modal-inquiry-reply');

            document.querySelectorAll('.view-inquiry-btn').forEach(function (button) {
                button.addEventListener('click', function () {
                    if (! inquiryModal) return;

                    const id = this.dataset.id;
                    const isRead = this.dataset.read === '1';
                    const readUrl = this.dataset.readUrl;
                    const name = this.dataset.name || 'Anonymous';
                    const email = this.dataset.email || '';
                    const phone = this.dataset.phone || '';
                    const agent = this.dataset.agent || 'Admin / General';
                    const subject = this.dataset.subject || 'No Subject';
                    const message = this.dataset.message || '';
                    const received = this.dataset.received || '';

                    modalInquirySubject.textContent = subject;
                    modalInquiryName.textContent = name;
                    modalInquiryEmail.textContent = email || 'N/A';
                    modalInquiryEmail.href = email ? 'mailto:' + email : '#';
                    modalInquiryPhone.textContent = phone || 'None provided';
                    modalInquiryPhone.href = phone ? 'tel:' + phone.replace(/[^0-9+]/g, '') : '#';
                    modalInquiryAgent.textContent = agent;
                    modalInquiryReceived.textContent = received;
                    modalInquiryMessage.textContent = message;
                    if (modalInquiryReply) {
                        modalInquiryReply.href = email ? 'mailto:' + email + '?subject=' + encodeURIComponent('Re: ' + subject) : '#';
                        modalInquiryReply.classList.toggle('hidden', ! email);
                    }

                    inquiryModal.classList.remove('hidden');
                    inquiryModal.classList.add('flex');

                    // If message is new (unread), mark it as read and reduce the unread count!
                    if (! isRead && readUrl) {
                        document.querySelectorAll('.view-inquiry-btn[data-id="' + id + '"]').forEach(function (b) {
                            b.dataset.read = '1';
                        });

                        const row = document.querySelector('[data-inquiry-row="' + id + '"]');
                        if (row) {
                            row.classList.remove('bg-[#fffdf9]');
                            const newBadge = row.querySelector('.inquiry-badge-new');
                            if (newBadge) {
                                newBadge.remove();
                            }
                        }

                        // Reduce sidebar badge count
                        const sidebarBadge = document.getElementById('sidebar-inquiries-badge');
                        if (sidebarBadge) {
                            const count = parseInt(sidebarBadge.textContent, 10) || 0;
                            const newCount = Math.max(0, count - 1);
                            if (newCount > 0) {
                                sidebarBadge.textContent = newCount + ' new';
                            } else {
                                sidebarBadge.remove();
                            }
                        }

                        // Reduce section header unread pill
                        const headerPill = document.getElementById('inquiries-unread-pill');
                        const headerCountEl = document.getElementById('inquiries-unread-count');
                        if (headerPill && headerCountEl) {
                            const count = parseInt(headerCountEl.textContent, 10) || 0;
                            const newCount = Math.max(0, count - 1);
                            if (newCount > 0) {
                                headerCountEl.textContent = newCount;
                            } else {
                                headerPill.classList.add('hidden');
                            }
                        }

                        // Reduce top-right messages icon badge
                        const iconBadge = document.getElementById('dashboard-messages-icon-badge');
                        if (iconBadge) {
                            const iconCount = Math.max(0, (parseInt(iconBadge.textContent, 10) || 0) - 1);
                            if (iconCount > 0) {
                                iconBadge.textContent = iconCount;
                            } else {
                                iconBadge.remove();
                            }
                        }

                        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
                        fetch(readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'Accept': 'application/json',
                                'Content-Type': 'application/json'
                            }
                        }).then(function (res) {
                            return res.json();
                        }).then(function (data) {
                            if (data && typeof data.unread_count === 'number') {
                                const currentBadge = document.getElementById('sidebar-inquiries-badge');
                                if (data.unread_count > 0 && currentBadge) {
                                    currentBadge.textContent = data.unread_count + ' new';
                                } else if (data.unread_count === 0 && currentBadge) {
                                    currentBadge.remove();
                                }
                                if (headerPill && headerCountEl) {
                                    if (data.unread_count > 0) {
                                        headerCountEl.textContent = data.unread_count;
                                        headerPill.classList.remove('hidden');
                                    } else {
                                        headerPill.classList.add('hidden');
                                    }
                                }
                                const iconBadge = document.getElementById('dashboard-messages-icon-badge');
                                if (iconBadge) {
                                    if (data.unread_count > 0) {
                                        iconBadge.textContent = data.unread_count;
                                    } else {
                                        iconBadge.remove();
                                    }
                                }
                            }
                        }).catch(function () {});
                    }
                });
            });

            const closeInquiryModal = () => {
                if (inquiryModal) {
                    inquiryModal.classList.add('hidden');
                    inquiryModal.classList.remove('flex');
                }
            };

            document.getElementById('close-inquiry-modal')?.addEventListener('click', closeInquiryModal);
            document.getElementById('cancel-inquiry-modal')?.addEventListener('click', closeInquiryModal);
            inquiryModal?.addEventListener('click', function (e) {
                if (e.target === inquiryModal) closeInquiryModal();
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape' && inquiryModal && ! inquiryModal.classList.contains('hidden')) {
                    closeInquiryModal();
                }
            });

            // Dedicated search for each dashboard page/section
            function setupTableSearch(inputId, clearBtnId, rowSelector, noResultsId, countBadgeId, singularLabel, pluralLabel) {
                const input = document.getElementById(inputId);
                const clearBtn = document.getElementById(clearBtnId);
                const noResultsRow = document.getElementById(noResultsId);
                const countBadge = document.getElementById(countBadgeId);
                if (!input) return;

                function filterRows() {
                    const query = (input.value || '').trim().toLowerCase();
                    if (clearBtn) {
                        clearBtn.classList.toggle('hidden', !query);
                    }
                    const rows = document.querySelectorAll(rowSelector);
                    let visibleCount = 0;
                    rows.forEach(function (row) {
                        const text = (row.dataset.search || row.textContent || '').toLowerCase();
                        const match = !query || text.includes(query);
                        row.style.display = match ? '' : 'none';
                        if (match) visibleCount++;
                    });

                    if (noResultsRow) {
                        noResultsRow.classList.toggle('hidden', visibleCount > 0 || rows.length === 0);
                    }
                    if (countBadge) {
                        if (query) {
                            countBadge.textContent = visibleCount + ' of ' + rows.length + ' matching';
                        } else {
                            countBadge.textContent = rows.length + ' ' + (rows.length === 1 ? singularLabel : pluralLabel);
                        }
                    }
                }

                input.addEventListener('input', filterRows);
                if (clearBtn) {
                    clearBtn.addEventListener('click', function () {
                        input.value = '';
                        filterRows();
                        input.focus();
                    });
                }
            }

            // Multi-Photo Accumulator & Dropzone Uploader
            function setupMultiPhotoUploader(inputId, dropzoneId, previewId, counterPillId) {
                const input = document.getElementById(inputId);
                const dropzone = document.getElementById(dropzoneId);
                const preview = document.getElementById(previewId);
                const counterPill = counterPillId ? document.getElementById(counterPillId) : null;
                if (!input || !preview) return;

                let dt = new DataTransfer();

                function renderPreviews() {
                    preview.innerHTML = "";
                    const files = Array.from(dt.files);

                    if (counterPill) {
                        if (files.length > 0) {
                            counterPill.textContent = `${files.length} photo${files.length > 1 ? "s" : ""} added`;
                            counterPill.classList.remove("hidden");
                        } else {
                            counterPill.classList.add("hidden");
                        }
                    }

                    if (files.length === 0) {
                        preview.classList.add("hidden");
                        preview.classList.remove("grid");
                        return;
                    }

                    preview.classList.remove("hidden");
                    preview.classList.add("grid");

                    files.forEach((file, idx) => {
                        const card = document.createElement("div");
                        card.className = "group relative rounded-xl overflow-hidden border border-[#d9cab3] bg-white aspect-square shadow-2xs transition-all hover:shadow-md";

                        const isCover = idx === 0;
                        const sizeMb = (file.size / (1024 * 1024)).toFixed(1);

                        const img = document.createElement("img");
                        img.className = "h-full w-full object-cover";
                        img.src = URL.createObjectURL(file);

                        const badge = document.createElement("span");
                        badge.className = isCover
                            ? "absolute top-1 left-1 rounded bg-[#102b25]/90 px-1.5 py-0.5 text-[9px] font-bold text-[#e7d8b7] uppercase tracking-wider shadow-xs"
                            : "absolute top-1 left-1 rounded bg-black/70 px-1.5 py-0.5 text-[9px] font-bold text-white";
                        badge.textContent = isCover ? "Cover" : `#${idx + 1}`;

                        const sizeBadge = document.createElement("span");
                        sizeBadge.className = "absolute bottom-1 left-1 rounded bg-black/60 px-1 py-0.5 text-[8px] font-medium text-white/90 backdrop-blur-xs";
                        sizeBadge.textContent = `${sizeMb} MB`;

                        const removeBtn = document.createElement("button");
                        removeBtn.type = "button";
                        removeBtn.title = "Remove this photo from upload batch";
                        removeBtn.className = "absolute top-1 right-1 flex h-6 w-6 items-center justify-center rounded-full bg-red-600/90 text-white hover:bg-red-700 transition shadow-xs cursor-pointer";
                        removeBtn.innerHTML = '<svg class="h-3.5 w-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>';
                        removeBtn.addEventListener("click", (e) => {
                            e.stopPropagation();
                            e.preventDefault();
                            const newDt = new DataTransfer();
                            Array.from(dt.files).forEach((f, i) => {
                                if (i !== idx) newDt.items.add(f);
                            });
                            dt = newDt;
                            input.files = dt.files;
                            renderPreviews();
                        });

                        card.appendChild(img);
                        card.appendChild(badge);
                        card.appendChild(sizeBadge);
                        card.appendChild(removeBtn);
                        preview.appendChild(card);
                    });
                }

                input.addEventListener("change", function () {
                    Array.from(this.files || []).forEach(file => {
                        if (file.type.startsWith("image/")) {
                            dt.items.add(file);
                        }
                    });
                    this.files = dt.files;
                    renderPreviews();
                });

                if (dropzone) {
                    dropzone.addEventListener("click", (e) => {
                        if (e.target !== input) {
                            input.click();
                        }
                    });

                    ["dragenter", "dragover"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.add("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    ["dragleave", "drop"].forEach(name => {
                        dropzone.addEventListener(name, (e) => {
                            e.preventDefault();
                            e.stopPropagation();
                            dropzone.classList.remove("border-[#1d3c34]", "bg-[#dfeee4]/40");
                        });
                    });

                    dropzone.addEventListener("drop", (e) => {
                        if (e.dataTransfer && e.dataTransfer.files) {
                            Array.from(e.dataTransfer.files).forEach(file => {
                                if (file.type.startsWith("image/")) {
                                    dt.items.add(file);
                                }
                            });
                            input.files = dt.files;
                            renderPreviews();
                        }
                    });
                }
            }

            setupMultiPhotoUploader("property-image-input", "admin-property-dropzone", "new-property-images-preview", "property-image-counter-pill");
            window.deleteAdminPropertyImage = function (propertyId, imageId) {
                if (!confirm('Remove this photo from the property?')) return;
                const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;

                fetch(`/dashboard/properties/${propertyId}/images/${imageId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.ok) {
                        document.getElementById(`admin-prop-img-${imageId}`)?.remove();
                    } else {
                        alert('Could not remove image.');
                    }
                })
                .catch(() => alert('Network error while removing photo.'));
            };

            window.setAdminCoverImage = function (propertyId, imageId, imageUrl) {
                const hiddenId = document.getElementById('admin-cover-image-id');
                if (hiddenId) hiddenId.value = imageId;

                const hiddenPreset = document.getElementById('admin-cover-preset');
                if (hiddenPreset) hiddenPreset.value = '';

                const activeCoverImg = document.getElementById('admin-active-cover-img');
                if (activeCoverImg && imageUrl) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('admin-cover-status-text');
                if (statusText) statusText.textContent = 'Active cover photo updated';

                updateAdminThumbnailsCoverState(imageId);

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/dashboard/properties/${propertyId}/images/${imageId}/cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showToastNotification('Cover photo updated successfully!');
                    }).catch(err => console.log('Cover updated locally', err));
                }
            };

            window.updateAdminThumbnailsCoverState = function (activeImageId) {
                const container = document.getElementById('admin-existing-images-container');
                if (!container) return;

                const cards = container.querySelectorAll('[id^="admin-prop-img-"]');
                cards.forEach(card => {
                    const id = card.id.replace('admin-prop-img-', '');
                    const controlDiv = card.querySelector('.admin-cover-control');
                    if (!controlDiv) return;

                    const propertyId = card.getAttribute('data-property-id') || '';

                    if (id == activeImageId) {
                        card.classList.remove('border-[#d9cab3]');
                        card.classList.add('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        controlDiv.innerHTML = '<span class="admin-cover-badge rounded-md bg-[#d4af37] px-1.5 py-0.5 text-[9px] font-black text-[#102b25] uppercase tracking-wider shadow-xs flex items-center gap-1">★ Cover</span>';
                    } else {
                        card.classList.remove('border-[#d4af37]', 'ring-2', 'ring-[#d4af37]/30');
                        card.classList.add('border-[#d9cab3]');
                        const img = card.querySelector('img');
                        const src = img ? img.src : '';
                        controlDiv.innerHTML = `<button type="button" onclick="setAdminCoverImage(${propertyId}, ${id}, '${src}')" title="Set as primary cover photo" class="admin-make-cover-btn rounded-md bg-black/75 hover:bg-[#d4af37] hover:text-[#102b25] text-white px-1.5 py-0.5 text-[9px] font-bold uppercase tracking-wider transition backdrop-blur-xs cursor-pointer shadow-xs">★ Set Cover</button>`;
                    }
                });
            };

            window.selectAdminPresetBackground = function (presetId, imageUrl, propertyId) {
                const hiddenPreset = document.getElementById('admin-cover-preset');
                if (hiddenPreset) hiddenPreset.value = presetId;

                const hiddenId = document.getElementById('admin-cover-image-id');
                if (hiddenId) hiddenId.value = '';

                const activeCoverImg = document.getElementById('admin-active-cover-img');
                if (activeCoverImg) activeCoverImg.src = imageUrl;

                const statusText = document.getElementById('admin-cover-status-text');
                if (statusText) statusText.textContent = `Selected luxury preset: ${presetId}`;

                if (propertyId) {
                    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || document.querySelector('input[name="_token"]')?.value;
                    fetch(`/dashboard/properties/${propertyId}/preset-cover`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ preset: presetId })
                    }).then(res => res.json()).then(data => {
                        if (data.ok) showToastNotification('Luxury background preset set as cover!');
                    }).catch(err => console.log('Preset chosen', err));
                } else {
                    showToastNotification('Luxury background preset selected!');
                }
            };

            window.previewAdminCoverImage = function (input) {
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const activeCoverImg = document.getElementById('admin-active-cover-img');
                        if (activeCoverImg) activeCoverImg.src = e.target.result;
                        const statusText = document.getElementById('admin-cover-status-text');
                        if (statusText) statusText.textContent = 'New cover photo file selected (will save with form)';
                        showToastNotification('New cover photo selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            window.previewJobTopImage = function (event) {
                const input = event.target;
                if (input.files && input.files[0]) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        const preview = document.getElementById('job-active-top-image');
                        const placeholder = document.getElementById('job-top-image-placeholder');
                        const badge = document.getElementById('job-image-badge');
                        const presetInput = document.getElementById('job-image-preset');
                        const removeChk = document.getElementById('job-remove-image-chk');

                        if (presetInput) presetInput.value = '';
                        if (removeChk) removeChk.checked = false;

                        if (preview) {
                            preview.src = e.target.result;
                            preview.classList.remove('hidden', 'opacity-30');
                        }
                        if (placeholder) placeholder.classList.add('hidden');
                        if (badge) {
                            badge.textContent = '★ New Upload';
                            badge.classList.remove('hidden');
                        }
                        showToastNotification('Job top image file selected!');
                    };
                    reader.readAsDataURL(input.files[0]);
                }
            };

            window.selectJobPresetBackground = function (presetId, imageUrl) {
                const presetInput = document.getElementById('job-image-preset');
                const fileInput = document.getElementById('job-image-input');
                const preview = document.getElementById('job-active-top-image');
                const placeholder = document.getElementById('job-top-image-placeholder');
                const badge = document.getElementById('job-image-badge');
                const removeChk = document.getElementById('job-remove-image-chk');

                if (presetInput) presetInput.value = presetId;
                if (fileInput) fileInput.value = '';
                if (removeChk) removeChk.checked = false;

                if (preview) {
                    preview.src = imageUrl;
                    preview.classList.remove('hidden', 'opacity-30');
                }
                if (placeholder) placeholder.classList.add('hidden');
                if (badge) {
                    badge.textContent = '★ Preset: ' + presetId;
                    badge.classList.remove('hidden');
                }
                showToastNotification('Luxury preset top image selected!');
            };

            window.toggleJobRemoveImage = function (checkbox) {
                const preview = document.getElementById('job-active-top-image');
                const badge = document.getElementById('job-image-badge');
                if (checkbox.checked) {
                    if (preview) preview.classList.add('opacity-30');
                    if (badge) badge.classList.add('hidden');
                    showToastNotification('Top image will be removed on save');
                } else {
                    if (preview) preview.classList.remove('opacity-30');
                    if (badge) badge.classList.remove('hidden');
                }
            };

            function showToastNotification(message) {
                let toast = document.getElementById('global-admin-toast');
                if (!toast) {
                    toast = document.createElement('div');
                    toast.id = 'global-admin-toast';
                    toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2 rounded-xl bg-[#102b25] text-[#e7d8b7] px-4 py-3 shadow-2xl border border-[#d4af37]/40 text-sm font-semibold transition-all duration-300 opacity-0 translate-y-2 pointer-events-none';
                    document.body.appendChild(toast);
                }
                toast.innerHTML = `<span class="text-[#d4af37]">★</span> <span>${message}</span>`;
                toast.classList.remove('opacity-0', 'translate-y-2');
                toast.classList.add('opacity-100', 'translate-y-0');
                setTimeout(() => {
                    toast.classList.remove('opacity-100', 'translate-y-0');
                    toast.classList.add('opacity-0', 'translate-y-2');
                }, 2800);
            }
        });
    </script>
