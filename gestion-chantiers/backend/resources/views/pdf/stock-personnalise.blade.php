@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            @if(in_array('produit', $columns)) <th>Produit</th> @endif
            @if(in_array('categorie', $columns)) <th>Catégorie</th> @endif
            @if(in_array('depot', $columns)) <th>Dépôt</th> @endif
            @if(in_array('emplacement', $columns)) <th>Emplacement</th> @endif
            @if(in_array('quantite', $columns)) <th>Quantité</th> @endif
            @if(in_array('seuil', $columns)) <th>Seuil min.</th> @endif
            @if(in_array('prix', $columns)) <th>Prix unit. (DH)</th> @endif
            @if(in_array('valeur', $columns)) <th>Valeur (DH)</th> @endif
            @if(in_array('etat', $columns)) <th>État</th> @endif
        </tr>
    </thead>
    <tbody>
        @foreach($lignes as $l)
        <tr>
            @if(in_array('produit', $columns)) <td>{{ $l['produit'] }}</td> @endif
            @if(in_array('categorie', $columns)) <td>{{ $l['categorie'] ?? '—' }}</td> @endif
            @if(in_array('depot', $columns)) <td>{{ $l['depot'] }}</td> @endif
            @if(in_array('emplacement', $columns)) <td>{{ $l['emplacement'] ?? '—' }}</td> @endif
            @if(in_array('quantite', $columns)) <td>{{ $l['quantite'] }} {{ $l['unite'] ?? '' }}</td> @endif
            @if(in_array('seuil', $columns)) <td>{{ $l['seuil'] }}</td> @endif
            @if(in_array('prix', $columns)) <td>{{ number_format($l['prix'], 2, ',', ' ') }}</td> @endif
            @if(in_array('valeur', $columns)) <td>{{ number_format($l['valeur'], 2, ',', ' ') }}</td> @endif
            @if(in_array('etat', $columns))
                <td>
                    @if($l['quantite'] <= $l['seuil'])
                        <span class="badge badge-red">Sous seuil</span>
                    @else
                        <span class="badge badge-green">OK</span>
                    @endif
                </td>
            @endif
        </tr>
        @endforeach
    </tbody>
</table>
<div class="summary-row">
    <div class="summary-box"><div class="val">{{ count($lignes) }}</div><div class="lbl">Lignes</div></div>
</div>
@endsection
