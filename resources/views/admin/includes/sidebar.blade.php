@php
    $u = auth('admin')->user();
@endphp

<aside class="sidebar" id="sidebar">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <div class="brand-icon"><i class="bi bi-mortarboard-fill"></i></div>
        <span class="brand-text">{{ __('messages.edu_platform') }}</span>
    </div>

    <nav class="sidebar-nav">

        {{-- ── Main ─────────────────────────────────────────── --}}
        <div class="nav-label">{{ __('messages.main') }}</div>
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.dashboard') }}"
                   class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-speedometer2"></i>
                    <span>{{ __('messages.dashboard') }}</span>
                </a>
            </li>
        </ul>

        {{-- ── Users ────────────────────────────────────────── --}}
        @if($u?->canAny(['student-table','teacher-table','enrollment-table','class-table']))
        <div class="nav-label">{{ __('messages.users') }}</div>
        <ul>
            @if($u?->can('student-table'))
            <li class="nav-item">
                <a href="{{ route('admin.students.index') }}"
                   class="nav-link {{ request()->routeIs('admin.students.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-mortarboard"></i>
                    <span>{{ __('messages.students') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('teacher-table'))
            <li class="nav-item">
                <a href="{{ route('admin.teachers.index') }}"
                   class="nav-link {{ request()->routeIs('admin.teachers.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-person-workspace"></i>
                    <span>{{ __('messages.teachers') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('enrollment-table'))
            <li class="nav-item">
                <a href="{{ route('admin.enrollments.index') }}"
                   class="nav-link {{ request()->routeIs('admin.enrollments.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-journal-check"></i>
                    <span>{{ __('messages.enrollments') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('class-table'))
            <li class="nav-item">
                <a href="{{ route('admin.school-classes.index') }}"
                   class="nav-link {{ request()->routeIs('admin.school-classes.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-building"></i>
                    <span>{{ __('messages.classes_title') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        {{-- ── Academic ──────────────────────────────────────── --}}
        @php
            $showAcademic = $u?->canAny([
                'course-table','category-table','subject-table','exam-table',
                'question-bank-table','previous-exam-table','worksheet-table',
                'announcement-table','banner-table','weekly-planner-table',
                'notification-send','educational-note-table',
                'class-schedule-table','exam-schedule-table',
            ]) || $u?->is_super;
        @endphp
        @if($showAcademic)
        <div class="nav-label">{{ __('messages.academic') }}</div>
        <ul>

            {{-- Courses --}}
            @if($u?->canAny(['course-table','course-add','category-table']))
            <li class="nav-item">
                <a href="#courses-menu" class="nav-link" data-submenu="courses-menu"
                   aria-expanded="{{ request()->routeIs('admin.courses.*','admin.categories.*') ? 'true' : 'false' }}">
                    <i class="nav-icon bi bi-book"></i>
                    <span>{{ __('messages.courses') }}</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </a>
                <ul class="nav-submenu {{ request()->routeIs('admin.courses.*','admin.categories.*') ? 'show' : '' }}" id="courses-menu">
                    @if($u?->can('course-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.index') }}" class="nav-link">
                            <span>{{ __('messages.all_courses') }}</span>
                        </a>
                    </li>
                    @endif
                    @if($u?->can('course-add'))
                    <li class="nav-item">
                        <a href="{{ route('admin.courses.create') }}" class="nav-link">
                            <span>{{ __('messages.add_course') }}</span>
                        </a>
                    </li>
                    @endif
                    @if($u?->can('category-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.categories.index') }}" class="nav-link">
                            <span>{{ __('messages.categories') }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Subjects --}}
            @if($u?->can('subject-table'))
            <li class="nav-item">
                <a href="{{ route('admin.subjects.index') }}"
                   class="nav-link {{ request()->routeIs('admin.subjects.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-journals"></i>
                    <span>{{ __('messages.subjects') }}</span>
                </a>
            </li>
            @endif

            {{-- Exams --}}
            @if($u?->canAny(['exam-table','exam-add']))
            <li class="nav-item">
                <a href="#exams-menu" class="nav-link" data-submenu="exams-menu"
                   aria-expanded="{{ request()->routeIs('admin.exams.*') ? 'true' : 'false' }}">
                    <i class="nav-icon bi bi-clipboard-check"></i>
                    <span>{{ __('messages.exams') }}</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </a>
                <ul class="nav-submenu {{ request()->routeIs('admin.exams.*') ? 'show' : '' }}" id="exams-menu">
                    @if($u?->can('exam-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.index') }}" class="nav-link">
                            <span>{{ __('messages.all_exams') }}</span>
                        </a>
                    </li>
                    @endif
                    @if($u?->can('exam-add'))
                    <li class="nav-item">
                        <a href="{{ route('admin.exams.create') }}" class="nav-link">
                            <span>{{ __('messages.add_exam') }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- PDF Files --}}
            @if($u?->canAny(['question-bank-table','previous-exam-table','worksheet-table']))
            <li class="nav-item">
                <a href="#pdf-menu" class="nav-link" data-submenu="pdf-menu"
                   aria-expanded="{{ request()->routeIs('admin.question-banks.*','admin.previous-year-exams.*','admin.worksheets.*') ? 'true' : 'false' }}">
                    <i class="nav-icon bi bi-file-earmark-pdf"></i>
                    <span>{{ __('messages.pdf_files') }}</span>
                    <i class="nav-arrow bi bi-chevron-right"></i>
                </a>
                <ul class="nav-submenu {{ request()->routeIs('admin.question-banks.*','admin.previous-year-exams.*','admin.worksheets.*') ? 'show' : '' }}" id="pdf-menu">
                    @if($u?->can('question-bank-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.question-banks.index') }}"
                           class="nav-link {{ request()->routeIs('admin.question-banks.*') ? 'active' : '' }}">
                            <span>{{ __('messages.question_banks') }}</span>
                        </a>
                    </li>
                    @endif
                    @if($u?->can('previous-exam-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.previous-year-exams.index') }}"
                           class="nav-link {{ request()->routeIs('admin.previous-year-exams.*') ? 'active' : '' }}">
                            <span>{{ __('messages.previous_year_exams') }}</span>
                        </a>
                    </li>
                    @endif
                    @if($u?->can('worksheet-table'))
                    <li class="nav-item">
                        <a href="{{ route('admin.worksheets.index') }}"
                           class="nav-link {{ request()->routeIs('admin.worksheets.*') ? 'active' : '' }}">
                            <span>{{ __('messages.worksheets') }}</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </li>
            @endif

            {{-- Announcements --}}
            @if($u?->can('announcement-table'))
            <li class="nav-item">
                <a href="{{ route('admin.announcements.index') }}"
                   class="nav-link {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-megaphone"></i>
                    <span>{{ __('messages.announcements') }}</span>
                </a>
            </li>
            @endif

            {{-- Banners --}}
            @if($u?->can('banner-table'))
            <li class="nav-item">
                <a href="{{ route('admin.banners.index') }}"
                   class="nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-images"></i>
                    <span>{{ __('messages.banners') }}</span>
                </a>
            </li>
            @endif

            {{-- Weekly Planners --}}
            @if($u?->can('weekly-planner-table'))
            <li class="nav-item">
                <a href="{{ route('admin.weekly-planners.index') }}"
                   class="nav-link {{ request()->routeIs('admin.weekly-planners.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar-week"></i>
                    <span>المفكرة الأسبوعية</span>
                </a>
            </li>
            @endif

            {{-- Class Schedule --}}
            @if($u?->can('class-schedule-table'))
            <li class="nav-item">
                <a href="{{ route('admin.class-schedules.index') }}"
                   class="nav-link {{ request()->routeIs('admin.class-schedules.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar3"></i>
                    <span>جدول الحصص</span>
                </a>
            </li>
            @endif

            {{-- Exam Schedule --}}
            @if($u?->can('exam-schedule-table'))
            <li class="nav-item">
                <a href="{{ route('admin.exam-schedules.index') }}"
                   class="nav-link {{ request()->routeIs('admin.exam-schedules.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-calendar-check"></i>
                    <span>جدول الامتحانات</span>
                </a>
            </li>
            @endif

            {{-- Notifications --}}
            @if($u?->can('notification-send'))
            <li class="nav-item">
                <a href="{{ route('admin.notifications.send') }}"
                   class="nav-link {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-bell"></i>
                    <span>{{ __('messages.notifications') }}</span>
                </a>
            </li>
            @endif

            {{-- Educational Notes --}}
            @if($u?->can('educational-note-table'))
            <li class="nav-item">
                <a href="{{ route('admin.educational-notes.index') }}"
                   class="nav-link {{ request()->routeIs('admin.educational-notes.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-journal-text"></i>
                    <span>{{ __('messages.educational_notes') }}</span>
                </a>
            </li>
            @endif

            {{-- Conduct (super admin only — no permission defined) --}}
            @if($u?->is_super)
            <li class="nav-item">
                <a href="{{ route('admin.conduct.index') }}"
                   class="nav-link {{ request()->routeIs('admin.conduct.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-file-earmark-check"></i>
                    <span>{{ __('messages.conduct_title') }}</span>
                </a>
            </li>
            @endif

        </ul>
        @endif

        {{-- ── Cards ────────────────────────────────────────── --}}
        @if($u?->can('card-table'))
        <div class="nav-label">{{ __('messages.cards_management') }}</div>
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.cards.index') }}"
                   class="nav-link {{ request()->routeIs('admin.cards.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-credit-card"></i>
                    <span>{{ __('messages.cards') }}</span>
                </a>
            </li>
        </ul>
        @endif

        {{-- ── System ────────────────────────────────────────── --}}
        @if($u?->canAny(['role-table','employee-table','activity-log-table','contact-message-table','setting-edit']))
        <div class="nav-label">{{ __('messages.system') }}</div>
        <ul>
            @if($u?->can('role-table'))
            <li class="nav-item">
                <a href="{{ route('admin.role.index') }}"
                   class="nav-link {{ request()->routeIs('admin.role.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-shield-check"></i>
                    <span>{{ __('messages.roles_permissions') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('employee-table'))
            <li class="nav-item">
                <a href="{{ route('admin.employee.index') }}"
                   class="nav-link {{ request()->routeIs('admin.employee.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-people"></i>
                    <span>الموظفون</span>
                </a>
            </li>
            @endif

            @if($u?->can('activity-log-table'))
            <li class="nav-item">
                <a href="{{ route('admin.activity-log.index') }}"
                   class="nav-link {{ request()->routeIs('admin.activity-log.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-clock-history"></i>
                    <span>سجل النشاطات</span>
                </a>
            </li>
            @endif

            @if($u?->can('contact-message-table'))
            <li class="nav-item">
                <a href="{{ route('admin.contact_messages.index') }}"
                   class="nav-link {{ request()->routeIs('admin.contact_messages.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-envelope"></i>
                    <span>{{ __('messages.contact_messages') }}</span>
                </a>
            </li>
            @endif

            @if($u?->can('setting-edit'))
            <li class="nav-item">
                <a href="{{ route('admin.site-settings.edit') }}"
                   class="nav-link {{ request()->routeIs('admin.site-settings.*') ? 'active' : '' }}">
                    <i class="nav-icon bi bi-gear"></i>
                    <span>{{ __('messages.site_settings') }}</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

    </nav>

    {{-- Sidebar Footer --}}
    <div class="sidebar-footer">
        <ul>
            <li class="nav-item">
                <a href="{{ route('admin.login.edit', auth('admin')->id()) }}" class="nav-link">
                    <i class="nav-icon bi bi-gear"></i>
                    <span>{{ __('messages.settings') }}</span>
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link"
                   onclick="event.preventDefault(); document.getElementById('admin-logout-form').submit();">
                    <i class="nav-icon bi bi-box-arrow-right"></i>
                    <span>{{ __('messages.sign_out') }}</span>
                </a>
            </li>
        </ul>
        <button class="sidebar-collapse-btn" id="sidebarCollapseBtn" title="{{ __('messages.collapse_sidebar') }}">
            <i class="bi bi-arrow-bar-left"></i>
        </button>
    </div>

</aside>
