@php
    $availableJobs = \App\Models\BlogPost::query()
        ->where('type', 'job')
        ->where('status', 'published')
        ->activeNotExpired()
        ->orderBy('title')
        ->get(['id', 'title', 'category', 'job_location', 'job_type']);
@endphp

<!-- Job Application Modal Overlay -->
<div id="job-application-modal" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="job-application-modal-title" role="dialog" aria-modal="true">
    <!-- Backdrop with luxury dark tint and blur -->
    <div id="job-application-modal-backdrop" class="fixed inset-0 bg-[#0c1e19]/80 backdrop-blur-md transition-opacity duration-300 opacity-0"></div>

    <div class="flex min-h-screen items-center justify-center p-4 sm:p-6 text-center">
        <!-- Modal Card Container -->
        <div id="job-application-modal-content" class="relative w-full max-w-2xl transform overflow-hidden rounded-[2rem] border border-[#d9cab3] bg-white text-left shadow-2xl transition-all duration-300 scale-95 opacity-0 my-8">

            <!-- Luxury Header -->
            <div class="relative overflow-hidden p-6 sm:p-8 text-white" style="background: linear-gradient(135deg, #102b25 0%, #1c473c 60%, #0d241f 100%);">
                <!-- Geometric Gold Pattern Accents -->
                <div class="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-[#d4af37]/10 blur-2xl pointer-events-none"></div>
                <div class="absolute right-0 top-0 w-24 h-24 pointer-events-none" style="background: linear-gradient(225deg, #d4af37 0%, #d4af37 45%, transparent 45%); opacity: 0.85;"></div>

                <div class="relative z-10 flex items-start justify-between gap-4">
                    <div>
                        <div class="inline-flex items-center gap-2 rounded-full border border-[#d4af37]/40 bg-[#d4af37]/15 px-3 py-1 text-[11px] font-black uppercase tracking-[0.2em] text-[#e7d8b7]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#d4af37] animate-pulse"></span>
                            <span>Career Application</span>
                        </div>
                        <h2 id="job-application-modal-title" class="mt-2.5 text-2xl sm:text-3xl font-black text-white tracking-tight">
                            Join Our Elite Team
                        </h2>
                        <p class="mt-1 text-xs sm:text-sm text-[#c2d7cb]">
                            Shape the future of real estate excellence with GTP Real Estate.
                        </p>
                    </div>

                    <!-- Close Button -->
                    <button type="button" onclick="closeJobApplicationModal()" class="shrink-0 -mr-2 -mt-2 flex h-10 w-10 items-center justify-center rounded-full bg-white/10 text-white/80 hover:bg-[#d4af37] hover:text-[#102b25] transition-all duration-200 cursor-pointer" aria-label="Close modal">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Position Banner -->
                <div class="relative z-10 mt-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#d4af37]/30 bg-black/25 px-4 py-3 backdrop-blur-sm">
                    <div class="flex items-center gap-3">
                        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-[#d4af37]/20 text-[#d4af37] border border-[#d4af37]/30">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-[#a3c2b1]">Applying for Position</span>
                            <p id="modal-display-job-title" class="text-sm font-extrabold text-[#e7d8b7] leading-tight">General Application</p>
                        </div>
                    </div>
                    <button type="button" id="modal-change-job-btn" onclick="toggleJobSelector()" class="text-[11px] font-bold text-[#d4af37] hover:text-white underline transition">
                        Change Position
                    </button>
                </div>

                <!-- Hidden / Expandable Job Selector -->
                <div id="modal-job-selector-wrapper" class="relative z-10 mt-3 hidden rounded-xl border border-white/20 bg-black/40 p-3">
                    <label for="modal-job-select-dropdown" class="block text-[11px] font-bold uppercase tracking-wider text-[#c2d7cb] mb-1.5">Select Open Position:</label>
                    <select id="modal-job-select-dropdown" onchange="onJobDropdownChange(this)" class="w-full rounded-lg border border-[#d9cab3]/40 bg-[#102b25] px-3 py-2 text-xs font-semibold text-white focus:outline-none focus:ring-2 focus:ring-[#d4af37]">
                        <option value="" data-title="General Application / Open Talent Pool">General Application / Open Talent Pool</option>
                        @foreach ($availableJobs as $jobItem)
                            <option value="{{ $jobItem->id }}" data-title="{{ $jobItem->title }}" data-category="{{ $jobItem->category }}" data-location="{{ $jobItem->job_location }}">
                                {{ $jobItem->title }} ({{ $jobItem->category ?: 'General' }} • {{ $jobItem->job_location ?: 'Addis Ababa' }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Form Content -->
            <form id="job-application-form" action="{{ route('job-applications.store') }}" method="POST" enctype="multipart/form-data" class="p-6 sm:p-8 space-y-6 bg-[#fffdfa]" onsubmit="handleJobApplicationSubmit(event)">
                @csrf
                <input type="hidden" name="job_id" id="form-job-id" value="">
                <input type="hidden" name="job_title" id="form-job-title" value="General Application">

                <!-- Dynamic Alert Banner (Client Validation / Server Errors) -->
                <div id="job-form-alert" class="hidden rounded-xl p-4 text-xs font-semibold border"></div>

                <!-- STEP 1: Applicant Identity -->
                <div>
                    <div class="flex items-center gap-2 pb-2 border-b border-[#f0e8dc]">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#102b25] text-[10px] font-bold text-[#d4af37]">1</span>
                        <h3 class="text-xs font-black uppercase tracking-[0.18em] text-[#102b25]">Personal &amp; Contact Details</h3>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <!-- Full Name -->
                        <div>
                            <label for="apply_full_name" class="block text-xs font-bold text-slate-700 mb-1">
                                Full Name <span class="text-[#a24339]">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                </span>
                                <input type="text" id="apply_full_name" name="full_name" required placeholder="e.g. Abebe Kebede" class="w-full rounded-xl border border-[#d9cab3] bg-white py-2.5 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>

                        <!-- Email Address -->
                        <div>
                            <label for="apply_email" class="block text-xs font-bold text-slate-700 mb-1">
                                Email Address <span class="text-[#a24339]">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                                </span>
                                <input type="email" id="apply_email" name="email" required placeholder="e.g. candidate@example.com" class="w-full rounded-xl border border-[#d9cab3] bg-white py-2.5 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>

                        <!-- Phone Number -->
                        <div>
                            <label for="apply_phone" class="block text-xs font-bold text-slate-700 mb-1">
                                Phone Number <span class="text-[#a24339]">*</span>
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                </span>
                                <input type="tel" id="apply_phone" name="phone" required placeholder="e.g. +251 91 123 4567" class="w-full rounded-xl border border-[#d9cab3] bg-white py-2.5 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>

                        <!-- Location / City -->
                        <div>
                            <label for="apply_location" class="block text-xs font-bold text-slate-700 mb-1">
                                Current Location / City
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                </span>
                                <input type="text" id="apply_location" name="current_location" placeholder="e.g. Addis Ababa, Bole" class="w-full rounded-xl border border-[#d9cab3] bg-white py-2.5 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: Professional Profile & Qualifications -->
                <div>
                    <div class="flex items-center gap-2 pb-2 border-b border-[#f0e8dc]">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#102b25] text-[10px] font-bold text-[#d4af37]">2</span>
                        <h3 class="text-xs font-black uppercase tracking-[0.18em] text-[#102b25]">Professional Profile</h3>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <!-- Experience Level -->
                        <div>
                            <label for="apply_experience_years" class="block text-xs font-bold text-slate-700 mb-1">
                                Experience Level
                            </label>
                            <select id="apply_experience_years" name="experience_years" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs sm:text-sm font-medium text-slate-800 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                                <option value="Entry-level (0 - 1 year)">Entry-level (0 - 1 year)</option>
                                <option value="Junior (1 - 3 years)">Junior (1 - 3 years)</option>
                                <option value="Mid-level (3 - 5 years)" selected>Mid-level (3 - 5 years)</option>
                                <option value="Senior (5 - 8 years)">Senior (5 - 8 years)</option>
                                <option value="Lead / Executive (8+ years)">Lead / Executive (8+ years)</option>
                            </select>
                        </div>

                        <!-- Highest Education Level -->
                        <div>
                            <label for="apply_education_level" class="block text-xs font-bold text-slate-700 mb-1">
                                Highest Education
                            </label>
                            <select id="apply_education_level" name="education_level" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs sm:text-sm font-medium text-slate-800 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                                <option value="Bachelor's Degree" selected>Bachelor's Degree</option>
                                <option value="Master's Degree">Master's Degree</option>
                                <option value="Diploma / TVET Certificate">Diploma / TVET Certificate</option>
                                <option value="Doctorate / PhD">Doctorate / PhD</option>
                                <option value="High School / Preparatory">High School / Preparatory</option>
                                <option value="Other Certification">Other Certification</option>
                            </select>
                        </div>

                        <!-- LinkedIn / Portfolio Link -->
                        <div>
                            <label for="apply_portfolio_url" class="block text-xs font-bold text-slate-700 mb-1">
                                LinkedIn or Portfolio URL
                            </label>
                            <div class="relative">
                                <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                </span>
                                <input type="url" id="apply_portfolio_url" name="portfolio_url" placeholder="https://linkedin.com/in/..." class="w-full rounded-xl border border-[#d9cab3] bg-white py-2.5 pl-9 pr-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>

                        <!-- Expected Salary & Availability in subgrid -->
                        <div class="grid grid-cols-2 gap-2">
                            <div>
                                <label for="apply_expected_salary" class="block text-xs font-bold text-slate-700 mb-1">
                                    Expected Salary
                                </label>
                                <input type="text" id="apply_expected_salary" name="expected_salary" placeholder="e.g. Negotiable" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                            <div>
                                <label for="apply_start_date" class="block text-xs font-bold text-slate-700 mb-1">
                                    Earliest Start
                                </label>
                                <input type="text" id="apply_start_date" name="available_start_date" placeholder="e.g. Immediately" class="w-full rounded-xl border border-[#d9cab3] bg-white px-3 py-2.5 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- STEP 3: Resume / CV File Upload (PDF or DOCX) -->
                <div>
                    <div class="flex items-center justify-between pb-2 border-b border-[#f0e8dc]">
                        <div class="flex items-center gap-2">
                            <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#102b25] text-[10px] font-bold text-[#d4af37]">3</span>
                            <h3 class="text-xs font-black uppercase tracking-[0.18em] text-[#102b25]">Resume / CV Document</h3>
                            <span class="text-[#a24339] text-xs font-bold">*</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <span class="inline-flex items-center rounded-md bg-[#e02424]/10 px-2 py-0.5 text-[10px] font-black text-[#e02424] border border-[#e02424]/20">PDF</span>
                            <span class="inline-flex items-center rounded-md bg-[#2563eb]/10 px-2 py-0.5 text-[10px] font-black text-[#2563eb] border border-[#2563eb]/20">DOCX</span>
                        </div>
                    </div>

                    <!-- Dropzone -->
                    <div class="mt-4">
                        <input type="file" id="apply_resume_file" name="resume" accept=".pdf,.docx,.doc,application/pdf,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/msword" required class="hidden" onchange="handleResumeFileSelected(this)">

                        <!-- Unselected Dropzone State -->
                        <div id="resume-dropzone-box" onclick="document.getElementById('apply_resume_file').click()" class="group relative cursor-pointer rounded-2xl border-2 border-dashed border-[#d9cab3] hover:border-[#d4af37] bg-[#fbf9f5] hover:bg-[#f6f2ea] p-6 text-center transition-all duration-200">
                            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-white text-[#102b25] shadow-sm border border-[#e8ded0] group-hover:scale-110 group-hover:border-[#d4af37] transition-all">
                                <svg class="h-7 w-7 text-[#102b25]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.75">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                                </svg>
                            </div>
                            <p class="mt-3 text-xs sm:text-sm font-bold text-slate-800">
                                Drag &amp; drop your resume here, or <span class="text-[#d4af37] underline decoration-2">browse files</span>
                            </p>
                            <p class="mt-1 text-[11px] text-slate-500">
                                Supports <strong class="text-slate-700">PDF (.pdf)</strong> or <strong class="text-slate-700">Word (.docx, .doc)</strong> up to 10MB
                            </p>
                        </div>

                        <!-- Selected File Preview Card -->
                        <div id="resume-selected-preview" class="hidden rounded-2xl border border-[#d4af37]/40 bg-gradient-to-r from-[#fbf9f5] to-[#f4eee1] p-4 shadow-sm">
                            <div class="flex items-center justify-between gap-3">
                                <div class="flex items-center gap-3 min-w-0">
                                    <!-- Dynamic File Icon -->
                                    <div id="resume-icon-badge" class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e02424] text-white font-black text-xs shadow-sm">
                                        PDF
                                    </div>
                                    <div class="min-w-0">
                                        <p id="resume-filename-display" class="truncate text-xs sm:text-sm font-extrabold text-[#102b25]">
                                            resume.pdf
                                        </p>
                                        <div class="flex items-center gap-2 mt-0.5 text-[11px] text-slate-500">
                                            <span id="resume-filesize-display">1.2 MB</span>
                                            <span>•</span>
                                            <span class="inline-flex items-center gap-1 font-semibold text-emerald-700">
                                                <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                                Ready to upload
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <button type="button" onclick="clearSelectedResume()" class="shrink-0 flex items-center gap-1 rounded-lg border border-[#d9cab3] bg-white px-2.5 py-1.5 text-xs font-bold text-[#a24339] hover:bg-[#a24339] hover:text-white transition cursor-pointer" title="Remove and choose different file">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    <span>Change</span>
                                </button>
                            </div>
                        </div>

                        <!-- Client-side error warning -->
                        <p id="resume-error-msg" class="hidden mt-2 text-xs font-semibold text-[#a24339] flex items-center gap-1">
                            <svg class="h-4 w-4 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                            <span id="resume-error-text">Please upload a valid PDF or DOCX file (under 10MB).</span>
                        </p>
                    </div>
                </div>

                <!-- STEP 4: Cover Letter / Note to Hiring Team -->
                <div>
                    <div class="flex items-center gap-2 pb-2 border-b border-[#f0e8dc]">
                        <span class="flex h-5 w-5 items-center justify-center rounded-full bg-[#102b25] text-[10px] font-bold text-[#d4af37]">4</span>
                        <h3 class="text-xs font-black uppercase tracking-[0.18em] text-[#102b25]">Cover Letter / Professional Summary</h3>
                    </div>

                    <div class="mt-4">
                        <textarea id="apply_cover_letter" name="cover_letter" rows="4" placeholder="Introduce yourself, your key accomplishments, and why you believe you're a great fit for GTP Real Estate..." class="w-full rounded-xl border border-[#d9cab3] bg-white p-3 text-xs sm:text-sm font-medium text-slate-800 placeholder-slate-400 focus:border-[#102b25] focus:outline-none focus:ring-2 focus:ring-[#102b25]/20 leading-relaxed"></textarea>
                    </div>
                </div>

                <!-- STEP 5: Verification & Submit -->
                <div class="pt-2">
                    <label class="flex items-start gap-3 cursor-pointer select-none">
                        <input type="checkbox" id="apply_consent" required checked class="mt-0.5 h-4 w-4 rounded border-[#d9cab3] text-[#102b25] focus:ring-[#102b25]">
                        <span class="text-xs text-slate-600 leading-snug">
                            I verify that all personal and professional information provided in this application and attached CV is accurate and genuine.
                        </span>
                    </label>

                    <div class="mt-6 flex flex-col sm:flex-row items-center justify-end gap-3 pt-4 border-t border-[#f0e8dc]">
                        <button type="button" onclick="closeJobApplicationModal()" class="w-full sm:w-auto rounded-full border border-[#d9cab3] bg-white px-6 py-3 text-xs sm:text-sm font-bold text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                            Cancel
                        </button>

                        <button type="submit" id="job-apply-submit-btn" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 rounded-full px-8 py-3 text-xs sm:text-sm font-black uppercase tracking-wider text-[#102b25] shadow-lg transition-all duration-200 hover:scale-[1.02] active:scale-95 cursor-pointer disabled:opacity-50 disabled:pointer-events-none" style="background: linear-gradient(135deg, #d4af37 0%, #f3e5ab 50%, #c5a059 100%);">
                            <span id="job-apply-submit-text">Submit Application</span>
                            <svg id="job-apply-submit-icon" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3" />
                            </svg>
                            <!-- Spinner (hidden by default) -->
                            <svg id="job-apply-spinner" class="hidden h-4 w-4 animate-spin text-[#102b25]" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </form>

            <!-- Success State Overlay Screen -->
            <div id="job-application-success-view" class="hidden p-8 sm:p-12 text-center bg-[#fffdfa]">
                <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full border-2 border-[#d4af37] bg-[#102b25] text-[#d4af37] shadow-xl">
                    <svg class="h-10 w-10 animate-bounce" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                </div>
                <h3 class="mt-6 text-2xl sm:text-3xl font-black text-[#102b25]">
                    Application Submitted!
                </h3>
                <p id="success-candidate-feedback" class="mt-3 max-w-md mx-auto text-sm leading-relaxed text-slate-600">
                    Thank you. Your resume and application details have been safely received by GTP Real Estate HR.
                </p>
                <div class="mt-6 p-4 rounded-2xl border border-[#d9cab3] bg-[#f9f5ee] max-w-md mx-auto text-xs text-slate-600 space-y-1">
                    <p><strong class="text-[#102b25]">Position:</strong> <span id="success-position-title">Senior Property Consultant</span></p>
                    <p><strong class="text-[#102b25]">Status:</strong> <span class="font-bold text-amber-700">Under Review</span></p>
                </div>
                <div class="mt-8 flex justify-center">
                    <button type="button" onclick="closeJobApplicationModal()" class="rounded-full bg-[#102b25] px-8 py-3 text-sm font-bold text-[#e7d8b7] hover:bg-[#1a3d34] transition shadow-md cursor-pointer">
                        Done &amp; Close
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    /**
     * Open the Job Application Modal with pre-loaded position info.
     * @param {Object} [job] - Optional job details { id, title, category, location }
     */
    function openJobApplicationModal(job) {
        const modal = document.getElementById('job-application-modal');
        const backdrop = document.getElementById('job-application-modal-backdrop');
        const content = document.getElementById('job-application-modal-content');
        const formView = document.getElementById('job-application-form');
        const successView = document.getElementById('job-application-success-view');

        if (!modal) return;

        // Reset views
        formView.classList.remove('hidden');
        successView.classList.add('hidden');
        document.getElementById('job-form-alert').classList.add('hidden');

        // Populate job details
        const jobTitle = (job && job.title) ? job.title : 'General Application / Open Talent Pool';
        const jobId = (job && job.id) ? job.id : '';

        document.getElementById('modal-display-job-title').textContent = jobTitle;
        document.getElementById('form-job-title').value = jobTitle;
        document.getElementById('form-job-id').value = jobId;

        // Sync dropdown
        const dropdown = document.getElementById('modal-job-select-dropdown');
        if (dropdown) {
            dropdown.value = jobId;
        }

        // Show modal with animation
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');

        requestAnimationFrame(() => {
            backdrop.classList.remove('opacity-0');
            backdrop.classList.add('opacity-100');
            content.classList.remove('scale-95', 'opacity-0');
            content.classList.add('scale-100', 'opacity-100');
        });
    }

    /**
     * Close the Job Application Modal.
     */
    function closeJobApplicationModal() {
        const modal = document.getElementById('job-application-modal');
        const backdrop = document.getElementById('job-application-modal-backdrop');
        const content = document.getElementById('job-application-modal-content');

        if (!modal) return;

        backdrop.classList.remove('opacity-100');
        backdrop.classList.add('opacity-0');
        content.classList.remove('scale-100', 'opacity-100');
        content.classList.add('scale-95', 'opacity-0');

        setTimeout(() => {
            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }, 250);
    }

    /**
     * Toggle the dropdown job selector.
     */
    function toggleJobSelector() {
        const wrapper = document.getElementById('modal-job-selector-wrapper');
        if (wrapper) {
            wrapper.classList.toggle('hidden');
        }
    }

    /**
     * Handle job dropdown change.
     */
    function onJobDropdownChange(select) {
        const selectedOption = select.options[select.selectedIndex];
        const jobTitle = selectedOption.getAttribute('data-title') || selectedOption.text;
        const jobId = select.value;

        document.getElementById('modal-display-job-title').textContent = jobTitle;
        document.getElementById('form-job-title').value = jobTitle;
        document.getElementById('form-job-id').value = jobId;
    }

    /**
     * Handle Resume / CV file selection and display live preview.
     */
    function handleResumeFileSelected(input) {
        const file = input.files ? input.files[0] : null;
        const dropzone = document.getElementById('resume-dropzone-box');
        const preview = document.getElementById('resume-selected-preview');
        const errorBox = document.getElementById('resume-error-msg');
        const errorText = document.getElementById('resume-error-text');

        errorBox.classList.add('hidden');

        if (!file) {
            dropzone.classList.remove('hidden');
            preview.classList.add('hidden');
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        const validExtensions = ['pdf', 'docx', 'doc'];

        if (!validExtensions.includes(ext)) {
            errorText.textContent = 'Invalid file format. Please upload a PDF (.pdf) or Word document (.docx).';
            errorBox.classList.remove('hidden');
            input.value = '';
            dropzone.classList.remove('hidden');
            preview.classList.add('hidden');
            return;
        }

        // 10MB limit (10 * 1024 * 1024 bytes)
        if (file.size > 10485760) {
            errorText.textContent = 'File is too large (' + (file.size / (1024 * 1024)).toFixed(1) + 'MB). Maximum size is 10MB.';
            errorBox.classList.remove('hidden');
            input.value = '';
            dropzone.classList.remove('hidden');
            preview.classList.add('hidden');
            return;
        }

        // Display formatted size
        let sizeFormatted = (file.size >= 1048576)
            ? (file.size / 1048576).toFixed(1) + ' MB'
            : Math.round(file.size / 1024) + ' KB';

        document.getElementById('resume-filename-display').textContent = file.name;
        document.getElementById('resume-filesize-display').textContent = sizeFormatted;

        // Icon badge style based on file type
        const badge = document.getElementById('resume-icon-badge');
        if (ext === 'pdf') {
            badge.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#e02424] text-white font-black text-xs shadow-sm';
            badge.textContent = 'PDF';
        } else {
            badge.className = 'flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-[#2563eb] text-white font-black text-xs shadow-sm';
            badge.textContent = 'DOCX';
        }

        dropzone.classList.add('hidden');
        preview.classList.remove('hidden');
    }

    /**
     * Clear the selected resume and return to dropzone.
     */
    function clearSelectedResume() {
        const input = document.getElementById('apply_resume_file');
        input.value = '';
        document.getElementById('resume-dropzone-box').classList.remove('hidden');
        document.getElementById('resume-selected-preview').classList.add('hidden');
        document.getElementById('resume-error-msg').classList.add('hidden');
    }

    /**
     * Handle drag and drop on the dropzone.
     */
    document.addEventListener('DOMContentLoaded', () => {
        const dropzone = document.getElementById('resume-dropzone-box');
        const fileInput = document.getElementById('apply_resume_file');

        if (dropzone && fileInput) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.add('border-[#d4af37]', 'bg-[#f6f0e2]');
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, (e) => {
                    e.preventDefault();
                    e.stopPropagation();
                    dropzone.classList.remove('border-[#d4af37]', 'bg-[#f6f0e2]');
                }, false);
            });

            dropzone.addEventListener('drop', (e) => {
                const dt = e.dataTransfer;
                const files = dt.files;
                if (files && files.length > 0) {
                    fileInput.files = files;
                    handleResumeFileSelected(fileInput);
                }
            }, false);
        }

        // Close on backdrop click
        const backdrop = document.getElementById('job-application-modal-backdrop');
        if (backdrop) {
            backdrop.addEventListener('click', closeJobApplicationModal);
        }

        // Close on Esc key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                const modal = document.getElementById('job-application-modal');
                if (modal && !modal.classList.contains('hidden')) {
                    closeJobApplicationModal();
                }
            }
        });

        // Check URL parameters for ?apply=1 or ?job=ID
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('apply') || urlParams.has('job')) {
            const jobId = urlParams.get('job');
            if (jobId) {
                const dropdown = document.getElementById('modal-job-select-dropdown');
                let foundTitle = null;
                if (dropdown) {
                    const opt = dropdown.querySelector('option[value="' + jobId + '"]');
                    if (opt) {
                        foundTitle = opt.getAttribute('data-title');
                    }
                }
                openJobApplicationModal({ id: jobId, title: foundTitle || 'Selected Job Opening' });
            } else {
                openJobApplicationModal();
            }
        }
    });

    /**
     * Form submission with AJAX, file validation, and loading indicators.
     */
    async function handleJobApplicationSubmit(event) {
        event.preventDefault();

        const form = event.target;
        const fileInput = document.getElementById('apply_resume_file');
        const alertBox = document.getElementById('job-form-alert');
        const submitBtn = document.getElementById('job-apply-submit-btn');
        const submitText = document.getElementById('job-apply-submit-text');
        const submitIcon = document.getElementById('job-apply-submit-icon');
        const spinner = document.getElementById('job-apply-spinner');

        alertBox.classList.add('hidden');

        // Check if resume is attached
        if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
            const errorBox = document.getElementById('resume-error-msg');
            const errorText = document.getElementById('resume-error-text');
            errorText.textContent = 'Please insert your resume or CV document (PDF or DOCX).';
            errorBox.classList.remove('hidden');
            return;
        }

        // Start loading state
        submitBtn.disabled = true;
        submitText.textContent = 'Submitting Application...';
        submitIcon.classList.add('hidden');
        spinner.classList.remove('hidden');

        const formData = new FormData(form);

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                // Show Success View
                document.getElementById('job-application-form').classList.add('hidden');
                const successView = document.getElementById('job-application-success-view');
                document.getElementById('success-position-title').textContent = document.getElementById('form-job-title').value;
                document.getElementById('success-candidate-feedback').textContent = data.message || 'Your application has been received successfully.';
                successView.classList.remove('hidden');

                // Reset form inputs for subsequent opens
                form.reset();
                clearSelectedResume();
            } else {
                // Show Error Message
                let errorHtml = '<p class="font-bold text-[#a24339] mb-1">Please check the following:</p><ul class="list-disc pl-5 space-y-0.5 text-slate-700">';
                if (data.errors) {
                    Object.values(data.errors).forEach(errList => {
                        errList.forEach(err => {
                            errorHtml += '<li>' + err + '</li>';
                        });
                    });
                } else {
                    errorHtml += '<li>' + (data.message || 'An error occurred while submitting your application.') + '</li>';
                }
                errorHtml += '</ul>';

                alertBox.className = 'rounded-xl p-4 text-xs font-semibold border border-[#a24339]/30 bg-[#fdf2f2] text-slate-800';
                alertBox.innerHTML = errorHtml;
                alertBox.classList.remove('hidden');
            }
        } catch (err) {
            // Fallback: submit standard form if AJAX fails
            form.submit();
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Submit Application';
            submitIcon.classList.remove('hidden');
            spinner.classList.add('hidden');
        }
    }
</script>
