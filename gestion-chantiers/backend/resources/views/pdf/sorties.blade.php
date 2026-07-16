@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Produit</th>
            <th>Dépôt</th>
            <th>Quantité</th>
            <th>Chantier</th>
            <th>Bénéficiaire</th>
            <th>Observations</th>
        </tr>
    </thead>
    <tbody>
        @foreach($sorties as $s)
        <tr>
            <td>{{ $s->date_sortie?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $s->produit?->nom ?? '—' }}</td>
            <td>{{ $s->stock?->nom ?? '—' }}</td>
            <td>{{ $s->quantite }} {{ $s->produit?->unite }}</td>
            <td>{{ $s->chantier?->nom ?? '—' }}</td>
            <td>{{ $s->beneficiaire ?? '—' }}</td>
            <td>{{ $s->observations ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $sorties->count() }}</div><div class="lbl">Sorties</div></div>
    <div class="summary-box"><div class="val">{{ number_format($sorties->sum('quantite'), 0, ',', ' ') }}</div><div class="lbl">Quantité totale</div></div>
</div>
@endsection
