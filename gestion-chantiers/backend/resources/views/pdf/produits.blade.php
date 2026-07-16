@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Nom</th>
            <th>Catégorie</th>
            <th>Unité</th>
            <th>Prix unitaire (DH)</th>
            <th>Stock total</th>
            <th>Valeur stock (DH)</th>
            <th>Statut</th>
            <th>Alerte</th>
        </tr>
    </thead>
    <tbody>
        @foreach($produits as $p)
        <tr>
            <td>{{ $p->nom }}</td>
            <td>{{ $p->categorie ?? '—' }}</td>
            <td>{{ $p->unite ?? '—' }}</td>
            <td>{{ number_format($p->prix_unitaire, 2, ',', ' ') }}</td>
            <td>{{ $p->stock_total }}</td>
            <td>{{ number_format($p->valeur_stock, 2, ',', ' ') }}</td>
            <td>{{ ucfirst($p->statut ?? '—') }}</td>
            <td>{{ $p->alerte_stock ? 'Oui' : 'Non' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $produits->count() }}</div><div class="lbl">Produits</div></div>
    <div class="summary-box"><div class="val">{{ number_format($produits->sum('valeur_stock'), 0, ',', ' ') }} DH</div><div class="lbl">Valeur stock totale</div></div>
    <div class="summary-box"><div class="val">{{ $produits->where('alerte_stock', true)->count() }}</div><div class="lbl">En alerte</div></div>
</div>
@endsection
