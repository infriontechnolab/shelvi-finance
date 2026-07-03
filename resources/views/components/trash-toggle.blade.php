@props(['table'])

{{-- Superadmin-only "Show deleted" toggle. Flips its DataTable to soft-deleted
     rows (Restore / Delete-forever) and back, without leaving the page. --}}
@can('trash.view')
    <x-ui.button variant="outline" size="sm" :id="$table.'-trash'" data-on="0">
        <x-ui.icon name="trash-2" /> <span data-trash-label>Show deleted</span>
    </x-ui.button>

    @push('scripts')
        <script type="module">
            (function () {
                const btn = document.getElementById(@js($table.'-trash'));
                if (!btn) return;
                btn.addEventListener('click', () => {
                    const on = btn.dataset.on === '1' ? '0' : '1';
                    btn.dataset.on = on;
                    btn.querySelector('[data-trash-label]').textContent = on === '1' ? 'Show active' : 'Show deleted';
                    btn.classList.toggle('bg-accent', on === '1');
                    btn.classList.toggle('text-accent-foreground', on === '1');
                    window.LaravelDataTables?.[@js($table)]?.draw();
                });
            })();
        </script>
    @endpush
@endcan
