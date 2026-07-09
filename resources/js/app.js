import $ from 'jquery';
// Core DataTables (no default theme — styled with shadcn tokens in app.css).
// Side-effect import registers the jQuery plugin ($.fn.DataTable) that the
// Yajra-generated init scripts call. window assignment prevents tree-shaking.
import DataTable from 'datatables.net';
// Side-effect import: registers $.fn.validate (jQuery already imported above).
import 'jquery-validation';
import {
    createIcons,
    LayoutDashboard, Users, Settings, Search,
    Sun, Moon, Menu, ChevronDown, LogOut, Plus, TrendingUp, TrendingDown, Ellipsis,
    User, Mail, Check, ChevronsUpDown, X, Pencil, Trash2,
    Landmark, Wallet, FileText, ArrowLeftRight, BookOpen, Calendar, AlertCircle,
    ArrowDownLeft, ArrowUpRight, ArrowRight, ArrowLeft, Eye, EyeOff, ShieldCheck,
    CircleCheck, TriangleAlert, Info, FileSpreadsheet, RotateCcw, ChevronLeft, ChevronRight,
} from 'lucide';

window.$ = window.jQuery = $;
window.DataTable = DataTable;

// Lucide: replace every <i data-lucide="..."> placeholder with an SVG. Re-run after
// each DataTables draw so ajax-injected rows (action buttons) get icons too.
// Placeholders carry their own size class (e.g. size-4), preserved onto the <svg>.
const lucideIcons = {
    LayoutDashboard, Users, Settings, Search,
    Sun, Moon, Menu, ChevronDown, LogOut, Plus, TrendingUp, TrendingDown, Ellipsis,
    User, Mail, Check, ChevronsUpDown, X, Pencil, Trash2,
    Landmark, Wallet, FileText, ArrowLeftRight, BookOpen, Calendar, AlertCircle,
    ArrowDownLeft, ArrowUpRight, ArrowRight, ArrowLeft, Eye, EyeOff, ShieldCheck,
    CircleCheck, TriangleAlert, Info, FileSpreadsheet, RotateCcw, ChevronLeft, ChevronRight,
};
function renderIcons() {
    createIcons({ icons: lucideIcons });
}
document.addEventListener('DOMContentLoaded', renderIcons);

// Date cell renderer for server-side DataTables. Server stores/sorts ISO (Y-m-d)
// so chronological order is correct; this formats ISO → "DD MMM YYYY" for display only.
const DT_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
window.fmtDate = function (iso) {
    if (!iso || iso === '-') {
        return '<span class="text-muted-foreground">—</span>';
    }
    const [y, m, d] = String(iso).split('-');
    return '<span class="text-muted-foreground">' + d + ' ' + (DT_MONTHS[parseInt(m, 10) - 1] || '') + ' ' + y + '</span>';
};
$(document).on('draw.dt', renderIcons);

// Form validation (jquery-validation). Forms opt in with [data-validate].
// Rules come from HTML5 `required` / `data-rule-*` attributes on each field, so
// the initializer stays generic. Custom comboboxes are a hidden input + button,
// so ignore:[] includes them and highlight/errorPlacement target the trigger/wrapper.
const ERR_RING = ['border-destructive', 'ring-1', 'ring-destructive/20'];
// Custom fields (combobox, datepicker) are a hidden input + a trigger button —
// place errors after the wrapper and ring the trigger, not the hidden input.
function comboWrapper(el) {
    return $(el).closest('[data-combobox], [data-datepicker]');
}
function wrapperTrigger($w) {
    return $w.find('[data-combobox-trigger], [data-datepicker-trigger]');
}
function initFormValidation() {
    if (!$.fn.validate) {
        return;
    }
    $('form[data-validate]').each(function () {
        $(this).validate({
            ignore: [],
            errorElement: 'p',
            errorClass: 'mt-1 text-xs font-medium text-destructive',
            submitHandler(form) {
                // Valid: submit for real. Native submit() bypasses re-validation
                // (no recursion back into this handler).
                form.submit();
            },
            errorPlacement(error, element) {
                const combo = comboWrapper(element);
                error.insertAfter(combo.length ? combo : element);
            },
            highlight(element) {
                const combo = comboWrapper(element);
                (combo.length ? wrapperTrigger(combo) : $(element)).addClass(ERR_RING);
            },
            unhighlight(element) {
                const combo = comboWrapper(element);
                (combo.length ? wrapperTrigger(combo) : $(element)).removeClass(ERR_RING);
            },
        });
    });
}
document.addEventListener('DOMContentLoaded', initFormValidation);

// Datepicker — styled calendar popover (replaces native <input type=date>).
// Value is kept as Y-m-d in a hidden input (what forms/validation expect); the
// trigger button shows a human label. Delegated events, so ajax-injected forms
// work too. The visible month is stashed on the popover dataset (vy/vm).
function dpHuman(iso) {
    if (!iso) return '';
    const [y, m, d] = iso.split('-');
    return d + ' ' + (DT_MONTHS[parseInt(m, 10) - 1] || '') + ' ' + y;
}
function dpISO(y, m, d) {
    return y + '-' + String(m + 1).padStart(2, '0') + '-' + String(d).padStart(2, '0');
}
function dpRender(picker) {
    const pop = picker.querySelector('[data-datepicker-popover]');
    const selected = picker.querySelector('[data-datepicker-value]').value || '';
    let vy = parseInt(pop.dataset.vy, 10);
    let vm = parseInt(pop.dataset.vm, 10);
    if (Number.isNaN(vy) || Number.isNaN(vm)) {
        const base = selected ? new Date(selected + 'T00:00:00') : new Date();
        vy = base.getFullYear();
        vm = base.getMonth();
        pop.dataset.vy = vy;
        pop.dataset.vm = vm;
    }
    picker.querySelector('[data-datepicker-title]').textContent = DT_MONTHS[vm] + ' ' + vy;

    const offset = new Date(vy, vm, 1).getDay();          // 0 = Sunday
    const days = new Date(vy, vm + 1, 0).getDate();
    const todayISO = dpISO(new Date().getFullYear(), new Date().getMonth(), new Date().getDate());
    let html = '';
    for (let i = 0; i < offset; i++) html += '<span></span>';
    for (let d = 1; d <= days; d++) {
        const iso = dpISO(vy, vm, d);
        const sel = iso === selected;
        const cls = sel
            ? 'bg-primary text-primary-foreground'
            : 'hover:bg-accent hover:text-accent-foreground' + (iso === todayISO ? ' border border-border' : '');
        html += '<button type="button" data-datepicker-day="' + iso + '" class="inline-flex size-8 items-center justify-center rounded-md text-sm transition-colors ' + cls + '">' + d + '</button>';
    }
    picker.querySelector('[data-datepicker-grid]').innerHTML = html;
    renderIcons();
}
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-datepicker-trigger]');
    const openPops = document.querySelectorAll('[data-datepicker-popover]:not([hidden])');

    if (trigger) {
        const picker = trigger.closest('[data-datepicker]');
        const pop = picker.querySelector('[data-datepicker-popover]');
        const willOpen = pop.hidden;
        openPops.forEach((p) => { if (p !== pop) p.hidden = true; });
        if (willOpen) {
            delete pop.dataset.vy;   // re-centre on the selected value / today
            delete pop.dataset.vm;
            dpRender(picker);
            pop.hidden = false;
        } else {
            pop.hidden = true;
        }
        return;
    }

    const nav = e.target.closest('[data-datepicker-prev], [data-datepicker-next]');
    if (nav) {
        const picker = nav.closest('[data-datepicker]');
        const pop = picker.querySelector('[data-datepicker-popover]');
        let vy = parseInt(pop.dataset.vy, 10);
        let vm = parseInt(pop.dataset.vm, 10) + (nav.matches('[data-datepicker-prev]') ? -1 : 1);
        if (vm < 0) { vm = 11; vy--; }
        if (vm > 11) { vm = 0; vy++; }
        pop.dataset.vy = vy;
        pop.dataset.vm = vm;
        dpRender(picker);
        return;
    }

    const day = e.target.closest('[data-datepicker-day]');
    if (day) {
        const picker = day.closest('[data-datepicker]');
        const hidden = picker.querySelector('[data-datepicker-value]');
        const label = picker.querySelector('[data-datepicker-label]');
        hidden.value = day.dataset.datepickerDay;
        label.textContent = dpHuman(hidden.value);
        label.classList.remove('text-muted-foreground');
        picker.querySelector('[data-datepicker-popover]').hidden = true;
        if (window.jQuery && window.jQuery(hidden).valid) window.jQuery(hidden).valid();
        return;
    }

    // Click outside any datepicker closes open calendars.
    if (!e.target.closest('[data-datepicker]')) openPops.forEach((p) => (p.hidden = true));
});

// Theme controller — light/dark, persisted to localStorage, follows system when unset.
// No-flash boot runs inline in <head> (see admin layout) BEFORE this file loads.
const STORAGE_KEY = 'shelvi-theme';

function systemPrefersDark() {
    return window.matchMedia('(prefers-color-scheme: dark)').matches;
}

function resolved() {
    const stored = localStorage.getItem(STORAGE_KEY);
    if (stored === 'light' || stored === 'dark') return stored;
    return systemPrefersDark() ? 'dark' : 'light';
}

function apply(theme) {
    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.querySelectorAll('[data-theme-icon]').forEach((el) => {
        el.hidden = el.dataset.themeIcon !== theme;
    });
}

function setTheme(theme) {
    localStorage.setItem(STORAGE_KEY, theme);
    apply(theme);
}

function toggleTheme() {
    setTheme(resolved() === 'dark' ? 'light' : 'dark');
}

// Wire up toggle buttons + sync icons on load.
document.addEventListener('DOMContentLoaded', () => {
    apply(resolved());
    document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
        btn.addEventListener('click', toggleTheme);
    });
});

// Follow system changes only while user hasn't explicitly chosen.
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', () => {
    if (!localStorage.getItem(STORAGE_KEY)) apply(resolved());
});

// Lightweight dropdown/menu toggling (no JS framework). Click [data-menu-trigger="id"]
// to toggle the element with [data-menu="id"]; outside-click closes.
document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-menu-trigger]');
    const openMenus = document.querySelectorAll('[data-menu]:not([hidden])');

    if (trigger) {
        const id = trigger.dataset.menuTrigger;
        const menu = document.querySelector(`[data-menu="${id}"]`);
        const wasOpen = menu && !menu.hidden;
        openMenus.forEach((m) => (m.hidden = true));
        if (menu) menu.hidden = wasOpen;
        return;
    }

    // Click inside an open menu: leave open. Otherwise close all.
    if (!e.target.closest('[data-menu]')) {
        openMenus.forEach((m) => (m.hidden = true));
    }
});

// Confirm-gated actions (e.g. row Delete) via a themed dialog (markup in admin layout).
// If the trigger sits inside a form (a real DELETE), the confirmed action submits
// that form; otherwise it falls back to removing the row from the current view.
const confirmModal = document.querySelector('[data-confirm-modal]');
let pendingConfirmEl = null;

// Global toast. type ∈ success | error | warning | info (falls back to success).
// Clones the matching Blade <template> so all styling stays in scanned markup.
function showToast(message, type = 'success') {
    const region = document.querySelector('[data-toast-region]');
    const tpl = document.querySelector(`[data-toast-tpl="${type}"]`)
        || document.querySelector('[data-toast-tpl="success"]');
    if (!region || !tpl) {
        return;
    }
    const node = tpl.content.firstElementChild.cloneNode(true);
    node.querySelector('[data-toast-message]').textContent = message;
    region.appendChild(node);
    renderIcons();

    const dismiss = () => {
        clearTimeout(timer);
        node.style.opacity = '0';
        node.style.transition = 'opacity 150ms';
        setTimeout(() => node.remove(), 150);
    };
    const timer = setTimeout(dismiss, type === 'error' ? 6000 : 4000);
    node.querySelector('[data-toast-close]')?.addEventListener('click', dismiss);
}
window.showToast = showToast;

function openConfirm(el) {
    if (!confirmModal) {
        return;
    }
    pendingConfirmEl = el;
    confirmModal.querySelector('[data-confirm-message]').textContent =
        el.dataset.confirm || 'This action cannot be undone.';
    confirmModal.hidden = false;
    document.body.style.overflow = 'hidden';
}

function closeConfirm() {
    if (!confirmModal) {
        return;
    }
    confirmModal.hidden = true;
    pendingConfirmEl = null;
    document.body.style.overflow = '';
}

function runConfirmedDelete(el) {
    // Real delete: submit the enclosing form (method-spoofed DELETE + CSRF).
    const form = el.closest('form[data-delete-form]') || el.closest('form');
    if (form) {
        form.submit();
        return;
    }
    // Fallback (rows without a delete route): drop the row from the current view.
    const row = el.closest('tr');
    if (row && window.jQuery) {
        $(row).fadeOut(150, function () { $(this).remove(); });
    } else if (row) {
        row.remove();
    }
    showToast('Record deleted');
}

document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-confirm]');
    if (trigger) {
        e.preventDefault();
        openConfirm(trigger);
        return;
    }
    if (e.target.closest('[data-confirm-cancel]') || e.target.closest('[data-confirm-backdrop]')) {
        closeConfirm();
        return;
    }
    if (e.target.closest('[data-confirm-ok]')) {
        const el = pendingConfirmEl;
        closeConfirm();
        if (el) {
            runConfirmedDelete(el);
        }
    }
});

document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && confirmModal && !confirmModal.hidden) {
        closeConfirm();
    }
});

// Surface server flash messages (success/error/warning/info) after any redirect.
(function showFlashToasts() {
    const el = document.querySelector('[data-flash-json]');
    if (!el) {
        return;
    }
    let flashes;
    try {
        flashes = JSON.parse(el.textContent);
    } catch {
        return;
    }
    Object.entries(flashes).forEach(([type, message]) => message && showToast(message, type));
})();

// shadcn-style combobox (Blade-native, no React). Searchable popover whose
// hidden input dispatches `change` so DataTable filters etc. can react.
function filterCombobox(box) {
    const q = (box.querySelector('[data-combobox-search]')?.value || '').toLowerCase();
    let visible = 0;
    box.querySelectorAll('[data-combobox-item]').forEach((item) => {
        const match = (item.dataset.label || item.textContent).toLowerCase().includes(q);
        item.hidden = !match;
        if (match) visible++;
    });
    const empty = box.querySelector('[data-combobox-empty]');
    if (empty) empty.hidden = visible > 0;
}

function closeComboboxPopover(pop) {
    pop.hidden = true;
    pop.closest('[data-combobox]')?.querySelector('[data-combobox-backdrop]')?.setAttribute('hidden', '');
}

function selectComboboxItem(item) {
    const box = item.closest('[data-combobox]');
    const input = box.querySelector('[data-combobox-input]');
    input.value = item.dataset.value;
    const label = box.querySelector('[data-combobox-label]');
    label.textContent = item.dataset.label;
    label.classList.toggle('text-muted-foreground', item.dataset.value === '');
    box.querySelectorAll('[data-combobox-item]').forEach((i) => i.setAttribute('aria-selected', i === item ? 'true' : 'false'));
    closeComboboxPopover(box.querySelector('[data-combobox-popover]'));
    input.dispatchEvent(new Event('change', { bubbles: true }));

    // Re-run validation for this field once the form has been validated at least once.
    const form = $(input).closest('form[data-validate]');
    if (form.length && form.data('validator')) {
        $(input).valid();
    }
}

document.addEventListener('click', (e) => {
    const trigger = e.target.closest('[data-combobox-trigger]');
    const openPops = document.querySelectorAll('[data-combobox-popover]:not([hidden])');

    if (trigger) {
        const box = trigger.closest('[data-combobox]');
        const pop = box.querySelector('[data-combobox-popover]');
        const wasOpen = !pop.hidden;
        openPops.forEach(closeComboboxPopover);
        if (wasOpen) return;
        pop.hidden = false;
        box.querySelector('[data-combobox-backdrop]')?.removeAttribute('hidden');
        const s = pop.querySelector('[data-combobox-search]');
        if (s) { s.value = ''; filterCombobox(box); setTimeout(() => s.focus(), 0); }
        return;
    }

    const item = e.target.closest('[data-combobox-item]');
    if (item) { selectComboboxItem(item); return; }

    // Click outside any combobox (including on its own backdrop) closes open popovers.
    if (!e.target.closest('[data-combobox-popover], [data-combobox-trigger]')) openPops.forEach(closeComboboxPopover);
});

document.addEventListener('input', (e) => {
    if (e.target.matches('[data-combobox-search]')) filterCombobox(e.target.closest('[data-combobox]'));
});

// Mobile sidebar toggle (overlay).
document.addEventListener('click', (e) => {
    if (e.target.closest('[data-sidebar-toggle]')) {
        document.querySelector('[data-sidebar]')?.classList.toggle('-translate-x-full');
        document.querySelector('[data-sidebar-backdrop]')?.classList.toggle('hidden');
    } else if (e.target.closest('[data-sidebar-backdrop]')) {
        document.querySelector('[data-sidebar]')?.classList.add('-translate-x-full');
        document.querySelector('[data-sidebar-backdrop]')?.classList.add('hidden');
    }
});

// Desktop sidebar collapse to icon rail (persisted).
const SIDEBAR_KEY = 'shelvi-sidebar';

function applySidebar() {
    const aside = document.querySelector('[data-sidebar]');
    if (aside) aside.dataset.collapsed = localStorage.getItem(SIDEBAR_KEY) === 'collapsed' ? 'true' : 'false';
}

document.addEventListener('DOMContentLoaded', applySidebar);

document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-sidebar-collapse]')) return;
    const aside = document.querySelector('[data-sidebar]');
    if (!aside) return;
    const collapsed = aside.dataset.collapsed === 'true';
    localStorage.setItem(SIDEBAR_KEY, collapsed ? 'expanded' : 'collapsed');
    aside.dataset.collapsed = collapsed ? 'false' : 'true';
});
