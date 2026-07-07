@if ($value)
    <span class="text-muted-foreground" title="{{ $value }}">{{ \Illuminate\Support\Str::limit($value, 40) }}</span>
@else
    <span class="text-muted-foreground">—</span>
@endif
