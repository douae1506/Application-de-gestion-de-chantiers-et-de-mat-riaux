@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Référence</th>
            <th>Nom</th>
            <th>Client</th>
            <th>Ville</th>
            <th>Type</th>
            <th>Statut</th>
            <th>Progression</th>
            <th>Budget (DH)</th>
            <th>Coût réel (DH)</th>
            <th>Date début</th>
        </tr>
    </thead>
    <tbody>
        @foreach($chantiers as $c)
        <tr>
            <td>{{ $c->reference }}</td>
            <td>{{ $c->nom }}</td>
            <td>{{ $c->client?->nom ?? '—' }}</td>
            <td>{{ $c->ville ?? '—' }}</td>
            <td>{{ $c->type_label }}</td>
            <td>{{ $c->statut_label }}</td>
            <td>{{ $c->progression }}%</td>
            <td>{{ number_format($c->budget_total, 2, ',', ' ') }}</td>
            <td>{{ number_format($c->cout_reel, 2, ',', ' ') }}</td>
            <td>{{ $c->date_debut?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $chantiers->count() }}</div><div class="lbl">Chantiers</div></div>
    <div class="summary-box"><div class="val">{{ number_format($chantiers->sum('budget_total'), 0, ',', ' ') }} DH</div><div class="lbl">Budget total</div></div>
    <div class="summary-box"><div class="val">{{ number_format($chantiers->sum('cout_reel'), 0, ',', ' ') }} DH</div><div class="lbl">Coût réel total</div></div>
</div>
@endsection
