@extends('pdf.layout')
@section('content')
@foreach($stocks as $s)
<h3 style="font-size:12px; color:#1d4ed8; margin: 14px 0 4px;">
    {{ $s->nom }} <span style="color:#64748b; font-weight:normal;">({{ $s->type_label }} — {{ $s->code ?? '—' }})</span>
</h3>
<table class="data-table">
    <thead>
        <tr>
            <th>Produit</th>
            <th>Emplacement</th>
            <th>Quantité</th>
            <th>Stock minimum</th>
            <th>État</th>
        </tr>
    </thead>
    <tbody>
        @forelse($s->produits as $p)
        <tr>
            <td>{{ $p->nom }}</td>
            <td>{{ $p->pivot->emplacement ?? '—' }}</td>
            <td>{{ $p->pivot->quantite }} {{ $p->unite }}</td>
            <td>{{ $p->pivot->stock_minimum }}</td>
            <td>
                @if($p->pivot->quantite <= $p->pivot->stock_minimum)
                    <span class="badge badge-red">Sous seuil</span>
                @else
                    <span class="badge badge-green">OK</span>
                @endif
            </td>
        </tr>
        @empty
        <tr><td colspan="5" style="color:#94a3b8;">Aucun produit dans ce dépôt</td></tr>
        @endforelse
    </tbody>
</table>
@endforeach
@endsection
