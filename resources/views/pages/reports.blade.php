<x-layouts.admin title="Reports">
    <x-slot:subtitle>Generate financial statements and summaries.</x-slot:subtitle>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
        @foreach ($reportTypes as $r)
            <a href="{{ route('reports.show', $r['slug']) }}" class="group block">
                <x-ui.card class="h-full transition-colors hover:border-primary/40 hover:bg-accent/40">
                    <x-ui.card-content class="flex items-start gap-4 pt-6">
                        <span class="flex size-11 shrink-0 items-center justify-center rounded-lg bg-primary/10 text-primary transition-colors group-hover:bg-primary group-hover:text-primary-foreground">
                            <x-ui.icon :name="$r['icon']" class="size-5" />
                        </span>
                        <div class="min-w-0">
                            <p class="font-semibold leading-tight">{{ $r['title'] }}</p>
                            <p class="mt-1 text-sm text-muted-foreground">{{ $r['desc'] }}</p>
                            <span class="mt-3 inline-flex items-center gap-1 text-xs font-medium text-primary">
                                Generate <x-ui.icon name="arrow-right" class="size-3.5" />
                            </span>
                        </div>
                    </x-ui.card-content>
                </x-ui.card>
            </a>
        @endforeach
    </div>
</x-layouts.admin>
