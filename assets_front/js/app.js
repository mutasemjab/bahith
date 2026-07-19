/* ═══════════════════════════════════════════════
   APP — Overlay SPA (data from window.APP_DATA)
═══════════════════════════════════════════════ */
const APP = (() => {

  /* ─── DATA (injected by Blade) ─── */
  const DATA           = window.APP_DATA || {};
  const GRADES_DATA    = DATA.grades        || [];
  const TAWJIHI_GRADES = DATA.tawjihiGrades || [];
  const FIELDS         = DATA.fields        || [];
  const GENERATIONS    = DATA.generations   || [];
  const COURSES_URL    = DATA.coursesUrl    || '/courses';

  const ORDINALS = ['','الأول','الثاني','الثالث','الرابع','الخامس',
                    'السادس','السابع','الثامن','التاسع','العاشر'];

  /* ─── STATE ─── */
  let stack = [];
  let cur = { page: null, gradeN: null, sem: null, subj: null, subjSlug: null,
              genY: null, fieldId: null, tawjihiGradeId: null };

  /* ─── HELPERS ─── */
  const $  = id => document.getElementById(id);
  const overlay = $('app-overlay');

  function showOvPage(id) {
    document.querySelectorAll('.ov-page').forEach(p => p.classList.remove('ov-active','ov-animate'));
    const el = $('ovp-' + id);
    if (!el) return;
    el.classList.add('ov-active');
    void el.offsetWidth;
    el.classList.add('ov-animate');
    overlay.scrollTop = 0;
    $('ov-back').hidden = stack.length === 0;
    renderBreadcrumb();
  }

  function push(nextPage, state) {
    stack.push({ ...cur });
    Object.assign(cur, state, { page: nextPage });
    showOvPage(nextPage);
  }

  function renderBreadcrumb() {
    const crumbs = [];
    const pg = cur.page;
    if (['grades','subjects'].includes(pg) && cur.gradeN) {
      crumbs.push({ l:'الصفوف', p:'grades' });
      if (pg === 'subjects') crumbs.push({ l:'الصف '+ORDINALS[cur.gradeN], p:'subjects' });
    } else if (pg === 'grades') {
      crumbs.push({ l:'الصفوف', p:'grades' });
    } else if (['tawjihi','tsubjects','fields','fsubjects'].includes(pg)) {
      crumbs.push({ l:'التوجيهي', p:'tawjihi' });
      if (cur.tawjihiGradeId) {
        const tg = TAWJIHI_GRADES.find(x => x.id === cur.tawjihiGradeId);
        if (tg && (pg === 'tsubjects' || pg === 'fields' || pg === 'fsubjects')) {
          crumbs.push({ l: tg.label, p: tg.type === 'subjects' ? 'tsubjects' : 'fields' });
        }
      }
      if (pg === 'fsubjects' && cur.fieldId) {
        const f = FIELDS.find(x => x.id === cur.fieldId);
        if (f) crumbs.push({ l: (f.icon||'')+ ' ' + f.label.substring(0,14), p:'fsubjects' });
      }
    }
    $('ov-bc').innerHTML = crumbs.map(function(c, i) {
      var isLast  = i === crumbs.length - 1;
      var onclick = isLast ? '' : ' onclick="APP.jumpTo(\'' + c.p + '\')"';
      var sep     = isLast ? '' : '<span class="ov-bc-sep">/</span>';
      return '<span class="ov-bc-item' + (isLast ? ' current' : '') + '"' + onclick + '>' + c.l + '</span>' + sep;
    }).join('');
  }

  function animStagger(selector) {
    if (typeof gsap === 'undefined') return;
    const els = document.querySelectorAll(selector);
    gsap.fromTo(els, {opacity:0,y:22}, {opacity:1,y:0,stagger:0.06,duration:0.45,ease:'power3.out'});
  }

  /* ─── RENDERERS ─── */

  function renderGrades() {
    $('ov-grade-list').innerHTML = GRADES_DATA.map(g => `
      <div class="ov-grade-card" onclick="APP.go('grade-subjects',${g.n})">
        <div class="ogc-num">${String(g.n).padStart(2,'0')}</div>
        <div class="ogc-ord">الصف ${ORDINALS[g.n]}</div>
        <div class="ogc-label">${g.label}</div>
        <div class="ogc-sub">${g.stage}</div>
        <div class="ogc-footer">
          <span class="ogc-badge">${g.subjects.length} مادة</span>
          <div class="ogc-arrow">→</div>
        </div>
      </div>
    `).join('');
    animStagger('#ov-grade-list .ov-grade-card');
  }

  function renderSubjects() {
    const g = GRADES_DATA.find(x => x.n === cur.gradeN);
    const semLbl = cur.sem === 1 ? 'الأول' : 'الثاني';
    $('subj-ey').textContent = (g ? g.label : '') + ' — الفصل ' + semLbl;
    $('subj-ttl').innerHTML  = 'مواد الفصل <span class="a-blue">' + semLbl + '</span>';

    $('ov-tab-1').classList.toggle('active', cur.sem === 1);
    $('ov-tab-2').classList.toggle('active', cur.sem === 2);

    const subjects = (g && g.semesters && g.semesters[cur.sem]) ? g.semesters[cur.sem] : [];
    $('ov-subj-list').innerHTML = subjects.map(s => {
      const count = s.courses_count > 0 ? `${s.courses_count} دورة` : 'استعرض الدورات';
      return `
        <div class="ov-subj-chip" onclick="APP.goToCourses(${s.id})">
          <div class="ov-subj-icon ${s.bg}">${s.e}</div>
          <div>
            <div class="ov-subj-label">${s.l}</div>
            <div class="ov-subj-count">${count}</div>
          </div>
        </div>`;
    }).join('');
    animStagger('#ov-subj-list .ov-subj-chip');
  }

  /* Tawjihi grade picker (11 / 12) */
  function renderTawjihiGrades() {
    const icons = { subjects: '📖', fields: '🎯' };
    $('ov-tw-grade-list').innerHTML = TAWJIHI_GRADES.map(g => `
      <div class="ov-grade-card" onclick="APP.go('tawjihi-grade',${g.id})">
        <div class="ogc-num">${g.type === 'subjects' ? '11' : '12'}</div>
        <div class="ogc-ord">${g.label}</div>
        <div class="ogc-label">${g.type === 'subjects' ? 'مواد مباشرة' : 'فروع متعددة'}</div>
        <div class="ogc-sub">التوجيهي</div>
        <div class="ogc-footer">
          <span class="ogc-badge">${g.type === 'subjects' ? g.subjects.length + ' مادة' : g.fields.length + ' فرع'}</span>
          <div class="ogc-arrow">→</div>
        </div>
      </div>
    `).join('');
    animStagger('#ov-tw-grade-list .ov-grade-card');
  }

  /* Grade 11 direct subjects */
  function renderTsubjects() {
    const tg = TAWJIHI_GRADES.find(x => x.id === cur.tawjihiGradeId);
    if (!tg) return;
    $('ts-ey').textContent  = 'التوجيهي — ' + tg.label;
    $('ts-ttl').textContent = 'مواد ' + tg.label;
    const subjects = tg.subjects || [];
    $('ov-ts-subj-list').innerHTML = subjects.map(s => {
      const count = s.courses_count > 0 ? `${s.courses_count} دورة` : 'استعرض الدورات';
      return `
        <div class="ov-subj-chip" onclick="APP.goToCourses(${s.id})">
          <div class="ov-subj-icon ${s.bg}">${s.e}</div>
          <div>
            <div class="ov-subj-label">${s.l}</div>
            <div class="ov-subj-count">${count}</div>
          </div>
        </div>`;
    }).join('');
    animStagger('#ov-ts-subj-list .ov-subj-chip');
  }

  function renderFields() {
    const tg = TAWJIHI_GRADES.find(g => g.id === cur.tawjihiGradeId);
    const gradeFields = (tg && tg.fields) ? tg.fields : FIELDS;
    $('fld-ey').textContent = (tg ? tg.label : 'الصف الثاني عشر') + ' — اختر الفرع';
    $('ov-field-list').innerHTML = gradeFields.map(f => `
      <div class="ov-field-card" onclick="APP.go('fsubjects',${f.id})">
        <div class="ov-field-overlay"></div>
        <div class="ov-field-content">
          <div class="ov-field-icon">${f.icon || '📚'}</div>
          <div class="ov-field-title">${f.label}</div>
          <div class="ov-field-sub">${f.sub || ''}</div>
        </div>
      </div>
    `).join('');
    animStagger('#ov-field-list .ov-field-card');
  }

  function renderFsubjects() {
    const tg = TAWJIHI_GRADES.find(g => g.id === cur.tawjihiGradeId);
    // Find field in the current grade's fields (or fall back to global FIELDS)
    const fieldsList = (tg && tg.fields) ? tg.fields : FIELDS;
    const f = fieldsList.find(x => x.id === cur.fieldId);
    if (!f) return;

    $('fs-ey').textContent = (tg ? tg.label + ' — ' : '') + f.label;
    $('fs-ttl').innerHTML  = `${f.icon || '📚'} <span class="a-blue">${f.label}</span>`;
    $('fs-sb').textContent = f.sub || '';

    const chipHtml = list => list.map(s => {
      const count = s.courses_count > 0 ? `${s.courses_count} دورة` : 'استعرض الدورات';
      return `
        <div class="ov-subj-chip" onclick="APP.goToCourses(${s.id})">
          <div class="ov-subj-icon ${s.bg}">${s.e}</div>
          <div>
            <div class="ov-subj-label">${s.l}</div>
            <div class="ov-subj-count">${count}</div>
          </div>
        </div>`;
    }).join('');

    $('ov-comp-count').textContent = `${f.comp.length} مادة`;
    $('ov-comp-list').innerHTML = chipHtml(f.comp);
    animStagger('#ov-comp-list .ov-subj-chip');

    // Hide elective section when empty (e.g. Grade 11 subjects)
    const elecSec = $('ov-elec-sec');
    if (elecSec) elecSec.style.display = f.elec.length > 0 ? '' : 'none';
    if (f.elec.length > 0) {
      $('ov-elec-count').textContent = `${f.elec.length} مادة`;
      $('ov-elec-list').innerHTML = chipHtml(f.elec);
      setTimeout(() => animStagger('#ov-elec-list .ov-subj-chip'), 120);
    }
  }

  /* ─── PUBLIC API ─── */
  return {
    open(type) {
      stack = [];
      Object.assign(cur, { page:null, gradeN:null, sem:null, subj:null, subjSlug:null,
                            genY:null, fieldId:null, tawjihiGradeId:null });
      overlay.classList.add('visible');
      document.body.style.overflow = 'hidden';

      if (type === 'grades') {
        cur.page = 'grades';
        renderGrades();
        showOvPage('grades');
      } else {
        cur.page = 'tawjihi';
        renderTawjihiGrades();
        showOvPage('tawjihi');
      }
    },

    go(page, val) {
      if (page === 'grade-subjects') {
        push('subjects', { gradeN: val, sem: 1 });
        renderSubjects();
      } else if (page === 'tawjihi-grade') {
        const tg = TAWJIHI_GRADES.find(g => g.id === val);
        if (!tg) return;
        if (tg.type === 'subjects') {
          push('tsubjects', { tawjihiGradeId: val });
          renderTsubjects();
        } else {
          push('fields', { tawjihiGradeId: val });
          renderFields();
        }
      } else if (page === 'fields') {
        push('fields', { genY: val });
        renderFields();
      } else if (page === 'fsubjects') {
        push('fsubjects', { fieldId: val });
        renderFsubjects();
      }
    },

    goToCourses(id) {
      window.location.href = COURSES_URL + '?subject=' + id;
    },

    setSemester(n) {
      cur.sem = n;
      renderSubjects();
    },

    back() {
      if (stack.length === 0) { this.closeOverlay(); return; }
      const prev = stack.pop();
      Object.assign(cur, prev);
      showOvPage(cur.page);
      if (cur.page === 'grades')    renderGrades();
      if (cur.page === 'subjects')  renderSubjects();
      if (cur.page === 'tawjihi')   renderTawjihiGrades();
      if (cur.page === 'tsubjects') renderTsubjects();
      if (cur.page === 'fields')    renderFields();
      if (cur.page === 'fsubjects') renderFsubjects();
    },

    jumpTo(page) {
      while (stack.length > 0 && cur.page !== page) {
        const prev = stack.pop();
        Object.assign(cur, prev);
      }
      showOvPage(cur.page);
      if (cur.page === 'grades')    renderGrades();
      if (cur.page === 'subjects')  renderSubjects();
      if (cur.page === 'tawjihi')   renderTawjihiGrades();
      if (cur.page === 'tsubjects') renderTsubjects();
      if (cur.page === 'fields')    renderFields();
      if (cur.page === 'fsubjects') renderFsubjects();
    },

    closeOverlay() {
      overlay.classList.remove('visible');
      document.body.style.overflow = '';
      stack = [];
    }
  };
})();

document.addEventListener('keydown', e => {
  if (e.key === 'Escape') APP.closeOverlay();
});
