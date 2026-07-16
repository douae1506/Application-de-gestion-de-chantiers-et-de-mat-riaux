@extends('pdf.layout')
@section('content')
<table style="width:100%; margin-bottom: 16px;">
    <tr>
        <td style="width:50%; font-size:10px; color:#64748b;">
            <strong style="color:#0f172a;">N° Bon :</strong> BS-{{ str_pad($sortie->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong style="color:#0f172a;">Date de sortie :</strong> {{ $sortie->date_sortie?->format('d/m/Y') ?? '—' }}<br>
            <strong style="color:#0f172a;">Dépôt :</strong> {{ $sortie->stock?->nom ?? '—' }}
        </td>
        <td style="width:50%; font-size:10px; color:#64748b; text-align:right;">
            <strong style="color:#0f172a;">Chantier :</strong> {{ $sortie->chantier?->nom ?? '—' }}<br>
            <strong style="color:#0f172a;">Bénéficiaire :</strong> {{ $sortie->beneficiaire ?? '—' }}
        </td>
    </tr>
</table>

<table class="data-table">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Quantité</th>
            <th>Unité</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>{{ $sortie->produit?->nom ?? '—' }}</td>
            <td>{{ $sortie->quantite }}</td>
            <td>{{ $sortie->produit?->unite ?? '—' }}</td>
        </tr>
    </tbody>
</table>

@if($sortie->observations)
<p style="margin-top:14px; font-size:10px;"><strong>Observations :</strong> {{ $sortie->observations }}</p>
@endif

<table style="width:100%; margin-top: 60px;">
    <tr>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Remis par (magasinier)</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Reçu par (bénéficiaire)</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Autorisation chef de chantier</div>
        </td>
    </tr>
</table>
@endsection