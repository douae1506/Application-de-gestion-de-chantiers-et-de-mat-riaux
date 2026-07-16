@extends('pdf.layout')
@section('content')
<table style="width:100%; margin-bottom: 16px;">
    <tr>
        <td style="width:50%; font-size:10px; color:#64748b;">
            <strong style="color:#0f172a;">N° Bon :</strong> BE-{{ str_pad($entree->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong style="color:#0f172a;">Date d'entrée :</strong> {{ $entree->date_entree?->format('d/m/Y') ?? '—' }}<br>
            <strong style="color:#0f172a;">Dépôt :</strong> {{ $entree->stock?->nom ?? '—' }}
        </td>
        <td style="width:50%; font-size:10px; color:#64748b; text-align:right;">
            <strong style="color:#0f172a;">Fournisseur :</strong> {{ $entree->fournisseur?->nom ?? '—' }}<br>
            <strong style="color:#0f172a;">N° Facture :</strong> {{ $entree->numero_facture ?? '—' }}
        </td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Unité</th>
            <th>Prix unitaire (DH)</th>
            <th>Montant (DH)</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $entree->produit?->nom ?? '—' }}</td>
            <td>{{ $entree->quantite }}</td>
            <td>{{ $entree->produit?->unite ?? '—' }}</td>
            <td>{{ number_format($entree->prix_unitaire, 2, ',', ' ') }}</td>
            <td>{{ number_format($entree->quantite * $entree->prix_unitaire, 2, ',', ' ') }}</td>
        </tr>
    </tbody>
</table>

@if($entree->observations)
<p style="margin-top:14px; font-size:10px;"><strong>Observations :</strong> {{ $entree->observations }}</p>
@endif

<table style="width:100%; margin-top: 60px;">
    <tr>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Réceptionné par</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Vérifié par</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Signature magasinier</div>
        </td>
    </tr>
</table>
@endsection