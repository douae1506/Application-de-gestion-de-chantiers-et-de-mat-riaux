@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Référence</th>
            <th>Nom</th>
            <th>Chantier</th>
            <th>Catégorie</th>
            <th>Statut</th>
            <th>Priorité</th>
            <th>Progression</th>
            <th>Budget (DH)</th>
            <th>Coût réel (DH)</th>
            <th>Échéance</th>
        </tr>
    </thead>
    <tbody>
        @foreach($projets as $p)
        <tr>
            <td>{{ $p->reference }}</td>
            <td>{{ $p->nom }}</td>
            <td>{{ $p->chantier?->nom ?? '—' }}</td>
            <td>{{ $p->categorie ?? '—' }}</td>
            <td>{{ $p->statut_label }}</td>
            <td>{{ ucfirst($p->priorite ?? '—') }}</td>
            <td>{{ $p->progression }}%</td>
            <td>{{ number_format($p->budget, 2, ',', ' ') }}</td>
            <td>{{ number_format($p->cout_reel, 2, ',', ' ') }}</td>
            <td>{{ $p->date_fin_prevue?->format('d/m/Y') ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $projets->count() }}</div><div class="lbl">Projets</div></div>
    <div class="summary-box"><div class="val">{{ number_format($projets->sum('budget'), 0, ',', ' ') }} DH</div><div class="lbl">Budget total</div></div>
    <div class="summary-box"><div class="val">{{ round($projets->avg('progression') ?: 0) }}%</div><div class="lbl">Progression moyenne</div></div>
</div>
@endsection
