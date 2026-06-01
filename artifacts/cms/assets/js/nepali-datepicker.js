/**
 * Nepali BS Date Picker
 * UI shows Bikram Sambat calendar in Nepali.
 * Selected date is converted to AD (YYYY-MM-DD) and stored
 * in the original <input>'s value — so forms POST English dates.
 *
 * Usage: automatically applied to every input[type="date"] on page.
 * Also manually: NepaliDatePicker.init(inputEl)
 */
(function () {
  'use strict';

  /* ── BS calendar data (days per month, year 2000–2095) ─────────────────── */
  const BS_DATA = {
    2000:[30,32,31,32,31,30,30,30,29,30,30,30],
    2001:[31,31,32,31,31,31,30,29,30,29,30,30],
    2002:[31,31,32,32,31,30,30,29,30,29,30,30],
    2003:[31,32,31,32,31,30,30,30,29,29,30,30],
    2004:[31,32,31,32,31,30,30,30,29,30,29,31],
    2005:[31,32,31,32,31,31,30,29,30,29,29,31],
    2006:[31,31,32,31,31,31,30,29,30,29,30,30],
    2007:[30,31,32,31,32,30,30,30,29,29,30,30],
    2008:[29,31,31,32,31,30,30,30,29,29,30,31],
    2009:[31,31,32,31,31,30,30,30,29,30,29,31],
    2010:[31,31,32,31,31,30,30,30,29,30,29,31],
    2011:[31,31,32,31,31,30,30,30,29,30,29,31],
    2012:[31,31,32,31,31,31,30,29,30,29,30,30],
    2013:[31,31,32,31,32,30,30,30,29,29,30,30],
    2014:[31,32,31,32,31,30,30,29,30,29,30,30],
    2015:[31,32,31,32,31,30,30,30,29,29,30,31],
    2016:[31,32,31,32,31,30,30,30,29,30,29,31],
    2017:[31,32,31,32,31,31,30,29,30,29,30,30],
    2018:[31,31,32,31,31,31,30,29,30,29,30,30],
    2019:[31,32,31,32,31,30,30,29,30,29,30,30],
    2020:[31,31,32,31,31,30,30,30,29,30,29,31],
    2021:[31,31,31,32,31,30,30,30,29,30,29,31],
    2022:[31,31,32,31,31,30,30,30,29,30,29,31],
    2023:[31,31,32,31,32,30,30,29,30,29,30,30],
    2024:[31,32,31,32,31,30,30,29,30,29,30,30],
    2025:[31,32,31,32,31,30,30,30,29,29,30,31],
    2026:[31,32,31,32,31,30,30,30,29,30,29,31],
    2027:[31,32,31,32,31,31,30,29,30,29,30,30],
    2028:[31,31,32,31,31,31,30,29,30,29,30,30],
    2029:[31,32,31,32,31,30,29,30,29,30,29,31],
    2030:[31,31,32,31,31,30,30,30,29,30,29,31],
    2031:[31,31,31,32,31,31,29,30,30,29,29,31],
    2032:[31,31,32,31,31,30,30,29,30,29,30,30],
    2033:[31,31,32,31,32,30,30,29,30,29,30,30],
    2034:[31,32,31,32,31,30,30,30,29,29,30,30],
    2035:[31,32,31,32,31,30,30,30,29,30,29,31],
    2036:[31,32,31,32,31,30,30,30,29,30,29,31],
    2037:[31,32,31,32,31,31,30,29,30,29,30,30],
    2038:[31,31,32,31,31,31,30,29,30,29,30,30],
    2039:[31,32,31,32,31,30,30,29,30,29,30,30],
    2040:[31,31,32,31,31,30,30,30,29,30,29,31],
    2041:[31,32,31,32,31,31,29,30,30,29,29,31],
    2042:[31,32,31,32,31,30,30,29,30,29,30,30],
    2043:[31,31,32,31,31,30,30,30,29,30,29,30],
    2044:[31,31,32,31,31,30,30,30,29,30,29,31],
    2045:[31,32,31,32,31,30,30,29,30,29,30,30],
    2046:[31,32,31,32,31,30,30,30,29,29,30,30],
    2047:[31,32,31,32,31,30,30,30,29,29,30,31],
    2048:[31,32,31,32,31,30,30,30,29,30,29,31],
    2049:[31,32,31,32,31,31,30,29,30,29,30,30],
    2050:[31,31,32,31,31,31,30,29,30,29,30,30],
    2051:[31,32,31,32,31,30,30,29,30,29,30,30],
    2052:[31,31,32,31,31,30,30,30,29,30,29,31],
    2053:[31,31,31,32,31,30,30,30,29,30,29,31],
    2054:[31,31,32,31,31,30,30,30,29,30,29,31],
    2055:[31,31,32,31,32,30,30,29,30,29,30,30],
    2056:[31,32,31,32,31,30,30,29,30,29,30,30],
    2057:[31,32,31,32,31,30,30,30,29,29,30,31],
    2058:[30,32,31,32,31,30,30,30,29,30,29,31],
    2059:[31,31,32,31,31,31,30,29,30,29,30,30],
    2060:[31,31,32,31,31,30,30,30,29,30,29,31],
    2061:[31,31,32,31,31,30,30,30,29,30,30,30],
    2062:[31,32,31,32,31,30,29,30,29,30,29,31],
    2063:[31,31,32,31,31,30,30,30,29,29,30,30],
    2064:[31,31,32,31,32,30,30,29,30,29,30,30],
    2065:[31,32,31,32,31,30,30,29,30,29,30,30],
    2066:[31,32,31,32,31,30,30,30,29,29,30,31],
    2067:[31,32,31,32,31,30,30,30,29,30,29,31],
    2068:[31,32,31,31,31,31,30,29,30,29,30,30],
    2069:[31,31,32,31,31,31,30,29,30,29,30,30],
    2070:[31,31,32,31,32,30,30,29,30,29,30,30],
    2071:[31,32,31,32,31,30,30,29,30,29,30,30],
    2072:[31,32,31,32,31,30,30,30,29,29,30,31],
    2073:[31,32,31,32,31,30,30,30,29,30,29,31],
    2074:[31,31,32,31,31,31,30,29,30,29,30,30],
    2075:[31,31,32,32,31,30,30,29,30,29,30,30],
    2076:[31,32,31,32,31,30,30,30,29,29,30,30],
    2077:[31,31,32,31,31,30,30,30,29,30,29,31],
    2078:[31,31,32,31,31,31,30,29,30,29,30,30],
    2079:[31,31,32,31,32,30,30,30,29,29,29,30],
    2080:[31,32,31,32,31,30,30,29,30,29,30,30],
    2081:[31,31,32,32,31,30,30,29,29,29,30,31],
    2082:[30,32,31,32,31,30,30,30,29,29,30,30],
    2083:[31,32,31,32,31,30,30,30,29,30,29,31],
    2084:[31,31,32,31,31,31,30,29,30,29,30,30],
    2085:[31,31,32,32,31,30,30,29,30,29,29,31],
    2086:[30,31,32,32,31,30,30,30,29,29,30,30],
    2087:[31,32,31,32,31,30,30,30,29,29,30,30],
    2088:[31,32,31,32,31,30,30,30,29,30,29,30],
    2089:[31,31,32,31,31,31,29,30,30,29,29,31],
    2090:[31,31,32,31,31,31,30,29,30,29,30,30],
    2091:[31,32,31,32,31,30,30,29,30,29,30,30],
    2092:[31,32,31,32,31,30,30,30,29,29,30,31],
    2093:[31,32,31,32,31,30,30,30,29,30,29,31],
    2094:[31,31,32,31,31,31,30,29,30,29,30,30],
    2095:[31,31,32,32,31,30,30,29,30,29,30,30],
  };

  /* Reference: BS 2000 Baisakh 1 = AD 1943 April 14 */
  const REF_BS = { y: 2000, m: 1, d: 1 };
  const REF_AD = new Date(1943, 3, 14); /* months 0-indexed */

  const BS_MONTHS_NP = ['बैशाख','जेठ','असार','साउन','भदौ','असोज','कार्तिक','मंसिर','पुष','माघ','फागुन','चैत'];
  const DAY_NAMES_NP = ['आ','सो','म','बु','बि','शु','श'];
  const DAY_NAMES_FULL = ['आइतबार','सोमबार','मंगलबार','बुधबार','बिहीबार','शुक्रबार','शनिबार'];
  const NP_NUMS = ['०','१','२','३','४','५','६','७','८','९'];

  function toNP(n) {
    return String(n).replace(/[0-9]/g, d => NP_NUMS[d]);
  }

  /* Count total BS days from reference to (y,m,d) */
  function bsDaysSinceRef(y, m, d) {
    let days = 0;
    for (let yr = REF_BS.y; yr < y; yr++) {
      if (!BS_DATA[yr]) break;
      days += BS_DATA[yr].reduce((a, b) => a + b, 0);
    }
    if (BS_DATA[y]) {
      for (let mo = 1; mo < m; mo++) days += BS_DATA[y][mo - 1];
    }
    days += (d - 1);
    return days;
  }

  /* Convert BS → AD (returns JS Date) */
  function bsToAd(y, m, d) {
    const diff = bsDaysSinceRef(y, m, d);
    const ad = new Date(REF_AD);
    ad.setDate(ad.getDate() + diff);
    return ad;
  }

  /* Convert AD (JS Date) → BS {y,m,d} */
  function adToBS(date) {
    const adDate = new Date(date.getFullYear(), date.getMonth(), date.getDate());
    const diff = Math.round((adDate - REF_AD) / 86400000);
    if (diff < 0) return null;
    let remaining = diff;
    let year = REF_BS.y;
    while (BS_DATA[year]) {
      const yearDays = BS_DATA[year].reduce((a, b) => a + b, 0);
      if (remaining < yearDays) break;
      remaining -= yearDays;
      year++;
    }
    if (!BS_DATA[year]) return null;
    let month = 1;
    for (let m = 0; m < 12; m++) {
      if (remaining < BS_DATA[year][m]) { month = m + 1; break; }
      remaining -= BS_DATA[year][m];
    }
    return { y: year, m: month, d: remaining + 1 };
  }

  /* Format AD date as YYYY-MM-DD string */
  function adToStr(d) {
    const yyyy = d.getFullYear();
    const mm = String(d.getMonth() + 1).padStart(2, '0');
    const dd = String(d.getDate()).padStart(2, '0');
    return `${yyyy}-${mm}-${dd}`;
  }

  /* Parse YYYY-MM-DD → JS Date */
  function parseAd(s) {
    if (!s || !/^\d{4}-\d{2}-\d{2}$/.test(s)) return null;
    const [y, m, d] = s.split('-').map(Number);
    return new Date(y, m - 1, d);
  }

  /* Return first weekday of a BS month (0=Sun) */
  function bsMonthFirstDay(bsy, bsm) {
    const ad = bsToAd(bsy, bsm, 1);
    return ad.getDay();
  }

  /* ── Picker State ────────────────────────────────────────── */
  let activeInput = null;  /* the hidden <input type="date"> */
  let activeDisplay = null; /* visible text input */
  let calEl = null;
  let viewY = 2082, viewM = 1;

  function getTodayBS() { return adToBS(new Date()); }

  function buildCalendar() {
    if (!calEl) return;
    const todayBS = getTodayBS();
    const mDays = BS_DATA[viewY] ? BS_DATA[viewY][viewM - 1] : 30;
    const firstDay = bsMonthFirstDay(viewY, viewM);

    let selectedBS = null;
    if (activeInput && activeInput.value) {
      const ad = parseAd(activeInput.value);
      if (ad) selectedBS = adToBS(ad);
    }

    calEl.innerHTML = '';

    /* Header */
    const hdr = document.createElement('div');
    hdr.style.cssText = 'display:flex;align-items:center;justify-content:space-between;padding:0.75rem 1rem;border-bottom:1px solid var(--border,#e5e7eb);';

    const prevBtn = document.createElement('button');
    prevBtn.type = 'button';
    prevBtn.innerHTML = '‹';
    prevBtn.style.cssText = 'width:1.75rem;height:1.75rem;border-radius:0.375rem;border:1px solid var(--border,#e5e7eb);background:none;cursor:pointer;font-size:1rem;color:var(--foreground,#111);display:grid;place-items:center;';
    prevBtn.onclick = (e) => { e.stopPropagation(); navigateMonth(-1); };

    const nextBtn = document.createElement('button');
    nextBtn.type = 'button';
    nextBtn.innerHTML = '›';
    nextBtn.style.cssText = prevBtn.style.cssText;
    nextBtn.onclick = (e) => { e.stopPropagation(); navigateMonth(1); };

    const title = document.createElement('div');
    title.style.cssText = 'font-weight:700;font-size:0.9375rem;cursor:pointer;user-select:none;display:flex;align-items:center;gap:0.375rem;';
    title.textContent = `${BS_MONTHS_NP[viewM - 1]} ${toNP(viewY)}`;
    title.title = `${BS_MONTHS_NP[viewM - 1]} ${viewY}`;

    hdr.appendChild(prevBtn);
    hdr.appendChild(title);
    hdr.appendChild(nextBtn);
    calEl.appendChild(hdr);

    /* Day-of-week row */
    const daysRow = document.createElement('div');
    daysRow.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:2px;padding:0.5rem 0.75rem 0.25rem;';
    DAY_NAMES_NP.forEach((d, i) => {
      const cell = document.createElement('div');
      cell.textContent = d;
      cell.style.cssText = `text-align:center;font-size:0.6875rem;font-weight:700;color:${i === 0 ? '#ef4444' : 'var(--muted-foreground,#6b7280)'};padding:0.1875rem 0;`;
      daysRow.appendChild(cell);
    });
    calEl.appendChild(daysRow);

    /* Date grid */
    const grid = document.createElement('div');
    grid.style.cssText = 'display:grid;grid-template-columns:repeat(7,1fr);gap:2px;padding:0 0.75rem 0.625rem;';

    /* Leading blanks */
    for (let i = 0; i < firstDay; i++) {
      const blank = document.createElement('div');
      grid.appendChild(blank);
    }

    for (let day = 1; day <= mDays; day++) {
      const btn = document.createElement('button');
      btn.type = 'button';
      btn.textContent = toNP(day);
      const isSun = (firstDay + day - 1) % 7 === 0;
      const isToday = todayBS && todayBS.y === viewY && todayBS.m === viewM && todayBS.d === day;
      const isSelected = selectedBS && selectedBS.y === viewY && selectedBS.m === viewM && selectedBS.d === day;

      let bg = 'transparent', color = isSun ? '#ef4444' : 'var(--foreground,#111)', fw = '500', border = 'none';
      if (isSelected) { bg = 'var(--primary,#2563eb)'; color = '#fff'; fw = '700'; border = 'none'; }
      else if (isToday) { bg = 'var(--primary-light,#eff6ff)'; color = 'var(--primary,#2563eb)'; fw = '700'; border = '1.5px solid var(--primary,#2563eb)'; }

      btn.style.cssText = `width:100%;aspect-ratio:1;border-radius:0.375rem;border:${border};background:${bg};color:${color};font-size:0.8125rem;font-weight:${fw};cursor:pointer;transition:background 0.1s;`;
      btn.onmouseenter = function () { if (!isSelected) this.style.background = 'var(--muted,#f3f4f6)'; };
      btn.onmouseleave = function () { if (!isSelected) this.style.background = isToday ? 'var(--primary-light,#eff6ff)' : 'transparent'; };
      btn.onclick = (e) => { e.stopPropagation(); selectDay(viewY, viewM, day); };
      grid.appendChild(btn);
    }
    calEl.appendChild(grid);

    /* Footer: Today / Clear */
    const foot = document.createElement('div');
    foot.style.cssText = 'display:flex;justify-content:space-between;padding:0.5rem 1rem 0.75rem;border-top:1px solid var(--border,#e5e7eb);';

    const todayBtn = document.createElement('button');
    todayBtn.type = 'button';
    todayBtn.textContent = 'आज';
    todayBtn.style.cssText = 'background:none;border:none;color:var(--primary,#2563eb);font-weight:600;font-size:0.8125rem;cursor:pointer;padding:0.25rem 0.5rem;border-radius:0.25rem;';
    todayBtn.onclick = (e) => { e.stopPropagation(); const t = getTodayBS(); if (t) selectDay(t.y, t.m, t.d); };

    const clearBtn = document.createElement('button');
    clearBtn.type = 'button';
    clearBtn.textContent = 'हटाउनुस्';
    clearBtn.style.cssText = 'background:none;border:none;color:var(--muted-foreground,#6b7280);font-weight:600;font-size:0.8125rem;cursor:pointer;padding:0.25rem 0.5rem;border-radius:0.25rem;';
    clearBtn.onclick = (e) => { e.stopPropagation(); clearDate(); };

    foot.appendChild(clearBtn);
    foot.appendChild(todayBtn);
    calEl.appendChild(foot);
  }

  function navigateMonth(dir) {
    viewM += dir;
    if (viewM > 12) { viewM = 1; viewY++; }
    if (viewM < 1)  { viewM = 12; viewY--; }
    buildCalendar();
  }

  function selectDay(y, m, d) {
    const ad = bsToAd(y, m, d);
    if (activeInput) activeInput.value = adToStr(ad);
    if (activeDisplay) activeDisplay.value = `${toNP(y)} ${BS_MONTHS_NP[m - 1]} ${toNP(d)}`;
    hidePicker();
    /* Dispatch change event so Alpine/listeners react */
    if (activeInput) activeInput.dispatchEvent(new Event('change', { bubbles: true }));
    if (activeDisplay) activeDisplay.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function clearDate() {
    if (activeInput)   activeInput.value   = '';
    if (activeDisplay) activeDisplay.value = '';
    hidePicker();
    if (activeInput)   activeInput.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function showPicker(hiddenInput, displayInput) {
    activeInput   = hiddenInput;
    activeDisplay = displayInput;

    /* Set view to current value or today */
    let bs = null;
    if (hiddenInput.value) {
      const ad = parseAd(hiddenInput.value);
      if (ad) bs = adToBS(ad);
    }
    if (!bs) bs = getTodayBS();
    if (bs) { viewY = bs.y; viewM = bs.m; }

    buildCalendar();

    /* Position the calendar below the display input */
    const rect = displayInput.getBoundingClientRect();
    const scrollY = window.scrollY || window.pageYOffset;
    const scrollX = window.scrollX || window.pageXOffset;
    calEl.style.top  = (rect.bottom + scrollY + 4) + 'px';
    calEl.style.left = (rect.left + scrollX) + 'px';
    calEl.style.display = 'block';
    calEl.style.zIndex = '99999';
    /* Prevent going off right edge */
    requestAnimationFrame(() => {
      const cw = calEl.offsetWidth;
      const vw = window.innerWidth;
      const left = rect.left + scrollX;
      if (left + cw > vw - 8) calEl.style.left = Math.max(4, vw - cw - 8) + 'px';
    });
  }

  function hidePicker() {
    if (calEl) calEl.style.display = 'none';
    activeInput   = null;
    activeDisplay = null;
  }

  /* ── Wrap an existing <input type="date"> ──────────────────── */
  function wrapInput(input) {
    if (input._npWrapped) return;
    input._npWrapped = true;

    /* Hide the real input; it keeps its name for POST */
    input.style.display = 'none';
    input.type = 'hidden'; /* prevents browser native picker */
    /* keep name/id/required on hidden input */

    /* Create visible display input */
    const display = document.createElement('input');
    display.type = 'text';
    display.readOnly = true;
    display.placeholder = 'मिति छान्नुस् (वि.सं.)';
    display.style.cssText = 'cursor:pointer;caret-color:transparent;';

    /* Copy classes and compute width */
    display.className = input.className;
    if (!display.className) display.className = 'form-input';

    /* Copy disabled/required states visually */
    if (input.disabled) display.disabled = true;

    /* Set initial display value from existing AD value */
    if (input.value) {
      const ad = parseAd(input.value);
      if (ad) {
        const bs = adToBS(ad);
        if (bs) display.value = `${toNP(bs.y)} ${BS_MONTHS_NP[bs.m - 1]} ${toNP(bs.d)}`;
      }
    }

    /* Add calendar icon */
    const wrapper = document.createElement('div');
    wrapper.style.cssText = 'position:relative;display:block;';
    input.parentNode.insertBefore(wrapper, input.nextSibling);
    wrapper.appendChild(input);
    wrapper.appendChild(display);

    const icon = document.createElement('span');
    icon.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>';
    icon.style.cssText = 'position:absolute;right:0.75rem;top:50%;transform:translateY(-50%);pointer-events:none;color:var(--muted-foreground,#6b7280);';
    wrapper.appendChild(icon);

    display.style.paddingRight = '2.25rem';

    display.addEventListener('click', (e) => {
      e.stopPropagation();
      if (calEl.style.display === 'block' && activeInput === input) {
        hidePicker();
      } else {
        showPicker(input, display);
      }
    });

    display.addEventListener('keydown', (e) => {
      if (e.key === 'Escape') hidePicker();
      if (e.key === 'Enter') { e.preventDefault(); display.click(); }
    });
  }

  /* ── Global calendar element ───────────────────────────── */
  function ensureCalEl() {
    if (calEl) return;
    calEl = document.createElement('div');
    calEl.id = 'np-datepicker-cal';
    calEl.style.cssText = [
      'display:none',
      'position:absolute',
      'z-index:99999',
      'background:var(--card,#fff)',
      'border:1px solid var(--border,#e5e7eb)',
      'border-radius:0.75rem',
      'box-shadow:0 8px 30px rgba(0,0,0,0.13)',
      'width:272px',
      'font-family:inherit',
      'overflow:hidden',
    ].join(';');
    document.body.appendChild(calEl);

    /* Close on outside click */
    document.addEventListener('click', (e) => {
      if (!calEl.contains(e.target) && e.target !== activeDisplay) hidePicker();
    });
    /* Close on scroll */
    window.addEventListener('scroll', hidePicker, { passive: true });
  }

  /* ── Public API ────────────────────────────────────────── */
  window.NepaliDatePicker = {
    init: function (input) {
      ensureCalEl();
      wrapInput(input);
    },
    initAll: function (scope) {
      const root = scope || document;
      root.querySelectorAll('input[type="date"]:not([data-no-np])').forEach(el => {
        ensureCalEl();
        wrapInput(el);
      });
    },
  };

  /* ── Auto-init on DOM ready ────────────────────────────── */
  function autoInit() {
    NepaliDatePicker.initAll(document);
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoInit);
  } else {
    autoInit();
  }

  /* Re-scan for date inputs added dynamically (e.g., inside Alpine modals) */
  const mo = new MutationObserver(function (mutations) {
    mutations.forEach(function (m) {
      m.addedNodes.forEach(function (node) {
        if (node.nodeType !== 1) return;
        if (node.matches && node.matches('input[type="date"]:not([data-no-np])')) {
          ensureCalEl(); wrapInput(node);
        }
        node.querySelectorAll && node.querySelectorAll('input[type="date"]:not([data-no-np])').forEach(el => {
          ensureCalEl(); wrapInput(el);
        });
      });
    });
  });
  document.addEventListener('DOMContentLoaded', function () {
    mo.observe(document.body, { childList: true, subtree: true });
  });

})();
