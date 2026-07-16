<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        @page { margin: 22px 26px 60px 26px; }
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            color: #0f172a;
            font-size: 11px;
            margin: 0;
        }
        .doc-header {
            display: table;
            width: 100%;
            border-bottom: 2px solid #2563eb;
            padding-bottom: 10px;
            margin-bottom: 16px;
        }
        .doc-header .company {
            display: table-cell;
            vertical-align: middle;
        }
        .doc-header .company .name {
            font-size: 17px;
            font-weight: bold;
            color: #1d4ed8;
        }
        .doc-header .company .tagline {
            font-size: 9px;
            color: #64748b;
            margin-top: 2px;
        }
        .doc-header .meta {
            display: table-cell;
            text-align: right;
            vertical-align: middle;
            font-size: 9px;
            color: #64748b;
        }
        .doc-title {
            font-size: 15px;
            font-weight: bold;
            color: #0f172a;
            margin: 0 0 4px 0;
        }
        .doc-subtitle {
            font-size: 9.5px;
            color: #64748b;
            margin: 0 0 14px 0;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 6px;
        }
        table.data-table th {
            background: #eff6ff;
            color: #1e3a8a;
            font-size: 9.5px;
            text-transform: uppercase;
            letter-spacing: 0.02em;
            text-align: left;
            padding: 6px 7px;
            border-bottom: 1.5px solid #bfdbfe;
        }
        table.data-table td {
            font-size: 10px;
            padding: 5px 7px;
            border-bottom: 1px solid #e7ecf3;
        }
        table.data-table tr:nth-child(even) td { background: #f8fafc; }
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 8px;
            font-size: 8.5px;
            font-weight: bold;
        }
        .badge-blue { background: #dbeafe; color: #1d4ed8; }
        .badge-green { background: #d1fae5; color: #047857; }
        .badge-amber { background: #fef3c7; color: #b45309; }
        .badge-red { background: #fee2e2; color: #b91c1c; }
        .badge-gray { background: #f1f5f9; color: #475569; }
        .footer {
            position: fixed;
            bottom: -40px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 8.5px;
            color: #94a3b8;
            border-top: 1px solid #e7ecf3;
            padding-top: 6px;
        }
        .summary-row {
            display: table;
            width: 100%;
            margin-top: 14px;
        }
        .summary-box {
            display: table-cell;
            text-align: center;
        }
        .summary-box .val { font-size: 14px; font-weight: bold; color: #1d4ed8; }
        .summary-box .lbl { font-size: 8.5px; color: #64748b; text-transform: uppercase; }
    </style>
</head>
<body>
    <div class="doc-header">
        <div class="company">
            <div class="name">{{ config('app.name', 'Gestion Chantiers') }}</div>
            <div class="tagline">Gestion de chantiers, projets &amp; stocks</div>
        </div>
        <div class="meta">
            Généré le {{ \Carbon\Carbon::now()->translatedFormat('d M Y à H:i') }}<br>
            @if(!empty($generatedBy)) Par {{ $generatedBy }} @endif
        </div>
    </div>

    <h1 class="doc-title">{{ $title ?? 'Document' }}</h1>
    @if(!empty($subtitle))
        <p class="doc-subtitle">{{ $subtitle }}</p>
    @endif

    @yield('content')

    <div class="footer">
        {{ config('app.name', 'Gestion Chantiers') }} — Document généré automatiquement · Page <span id="page"></span>
    </div>
</body>
</html>