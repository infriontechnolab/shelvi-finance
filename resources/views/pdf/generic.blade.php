<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        * { box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; color: #18181b; font-size: 10px; margin: 0; }
        .head { border-bottom: 2px solid #e4572e; padding-bottom: 10px; margin-bottom: 14px; }
        .brand { color: #e4572e; font-size: 18px; font-weight: bold; letter-spacing: -0.5px; }
        .title { font-size: 15px; font-weight: bold; margin: 8px 0 2px; }
        .desc { color: #71717a; font-size: 11px; }
        .meta { color: #71717a; font-size: 10px; margin-top: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 6px; }
        thead th { background: #f4f4f5; border-bottom: 1px solid #d4d4d8; padding: 6px 8px; font-size: 9px;
            text-transform: uppercase; letter-spacing: 0.3px; color: #52525b; white-space: nowrap; }
        tbody td { border-bottom: 1px solid #ececef; padding: 5px 8px; }
        tbody tr:nth-child(even) td { background: #fafafa; }
        .r { text-align: right; }
        .l { text-align: left; }
        .num { font-family: DejaVu Sans Mono, monospace; }
        .first { font-weight: bold; }
        .empty { text-align: center; padding: 30px; color: #a1a1aa; }
        .foot { margin-top: 14px; text-align: right; color: #a1a1aa; font-size: 9px; }
    </style>
</head>
<body>
    <div class="head">
        <div class="brand">Shelvi Finance</div>
        <div class="title">{{ $title }}</div>
        @if (! empty($subtitle))
            <div class="desc">{{ $subtitle }}</div>
        @endif
        <div class="meta">Generated: {{ now()->format('d M Y, g:i A') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                @foreach ($columns as $col)
                    <th class="{{ ($col['align'] ?? 'left') === 'right' ? 'r' : 'l' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    @foreach ($row as $i => $cell)
                        <td class="{{ ($columns[$i]['align'] ?? 'left') === 'right' ? 'r num' : 'l' }} {{ $i === 0 ? 'first' : '' }}">{!! $cell !!}</td>
                    @endforeach
                </tr>
            @empty
                <tr><td class="empty" colspan="{{ count($columns) }}">No records.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">{{ count($rows) }} {{ \Illuminate\Support\Str::plural('row', count($rows)) }}</div>
</body>
</html>
