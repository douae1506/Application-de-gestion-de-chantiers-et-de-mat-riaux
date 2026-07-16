@extends('pdf.layout')
@section('content')
<table style="width:100%; margin-bottom: 16px;">
    <tr>
        <td style="width:50%; font-size:10px; color:#64748b;">
            <strong style="color:#0f172a;">N° Bon :</strong> BT-{{ str_pad($transfert->id, 5, '0', STR_PAD_LEFT) }}<br>
            <strong style="color:#0f172a;">Date de transfert :</strong> {{ $transfert->date_transfert?->format('d/m/Y') ?? '—' }}
        </td>
        <td style="width:50%; font-size:10px; color:#64748b; text-align:right;">
            <strong style="color:#0f172a;">Dépôt source :</strong> {{ $transfert->stockSource?->nom ?? '—' }}<br>
            <strong style="color:#0f172a;">Dépôt destination :</strong> {{ $transfert->stockDestination?->nom ?? '—' }}
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
            <td>{{ $transfert->produit?->nom ?? '—' }}</td>
            <td>{{ $transfert->quantite }}</td>
            <td>{{ $transfert->produit?->unite ?? '—' }}</td>
        </tr>
    </tbody>
</table>

@if($transfert->observations)
<p style="margin-top:14px; font-size:10px;"><strong>Observations :</strong> {{ $transfert->observations }}</p>
@endif

<table style="width:100%; margin-top: 60px;">
    <tr>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Expédié par</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Réceptionné par</div>
        </td>
        <td style="width:33%; text-align:center; font-size:9.5px; color:#64748b;">
            <div style="border-top:1px solid #94a3b8; padding-top:6px; margin:0 10px;">Signature responsable dépôt</div>
        </td>
    </tr>
</table>
@endsection