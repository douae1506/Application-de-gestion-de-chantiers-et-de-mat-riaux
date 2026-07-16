@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Produit</th>
            <th>Dépôt source</th>
            <th>Dépôt destination</th>
            <th>Quantité</th>
            <th>Observations</th>
        </tr>
    </thead>
    <tbody>
        @foreach($transferts as $t)
        <tr>
            <td>{{ $t->date_transfert?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $t->produit?->nom ?? '—' }}</td>
            <td>{{ $t->stockSource?->nom ?? '—' }}</td>
            <td>{{ $t->stockDestination?->nom ?? '—' }}</td>
            <td>{{ $t->quantite }} {{ $t->produit?->unite }}</td>
            <td>{{ $t->observations ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $transferts->count() }}</div><div class="lbl">Transferts</div></div>
    <div class="summary-box"><div class="val">{{ number_format($transferts->sum('quantite'), 0, ',', ' ') }}</div><div class="lbl">Quantité totale</div></div>
</div>
@endsection
