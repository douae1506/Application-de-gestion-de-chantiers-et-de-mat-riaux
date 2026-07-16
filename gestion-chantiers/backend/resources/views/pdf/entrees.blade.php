@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Date</th>
            <th>Produit</th>
            <th>Dépôt</th>
            <th>Quantité</th>
            <th>Prix unit. (DH)</th>
            <th>Valeur (DH)</th>
            <th>Fournisseur</th>
            <th>N° Facture</th>
        </tr>
    </thead>
    <tbody>
        @foreach($entrees as $e)
        <tr>
            <td>{{ $e->date_entree?->format('d/m/Y') ?? '—' }}</td>
            <td>{{ $e->produit?->nom ?? '—' }}</td>
            <td>{{ $e->stock?->nom ?? '—' }}</td>
            <td>{{ $e->quantite }} {{ $e->produit?->unite }}</td>
            <td>{{ number_format($e->prix_unitaire, 2, ',', ' ') }}</td>
            <td>{{ number_format($e->quantite * $e->prix_unitaire, 2, ',', ' ') }}</td>
            <td>{{ $e->fournisseur?->nom ?? '—' }}</td>
            <td>{{ $e->numero_facture ?? '—' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $entrees->count() }}</div><div class="lbl">Entrées</div></div>
    <div class="summary-box"><div class="val">{{ number_format($entrees->sum(fn($e) => $e->quantite * $e->prix_unitaire), 0, ',', ' ') }} DH</div><div class="lbl">Valeur totale</div></div>
</div>
@endsection
