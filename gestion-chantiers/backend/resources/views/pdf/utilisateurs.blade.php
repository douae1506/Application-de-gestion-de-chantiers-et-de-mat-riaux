@extends('pdf.layout')
@section('content')
<table class="data-table">
    <thead>
        <tr>
            <th>Nom complet</th>
            <th>Email</th>
            <th>Rôle</th>
            <th>Téléphone</th>
            <th>Statut</th>
            <th>Dernière connexion</th>
        </tr>
    </thead>
    <tbody>
        @foreach($utilisateurs as $u)
        <tr>
            <td>{{ $u->nom_complet }}</td>
            <td>{{ $u->email }}</td>
            <td>{{ ucfirst(str_replace('_', ' ', $u->role)) }}</td>
            <td>{{ $u->telephone_mobile ?? $u->telephone_pro ?? '—' }}</td>
            <td>{{ $u->est_actif ? 'Actif' : 'Inactif' }}</td>
            <td>{{ $u->derniere_connexion_at?->format('d/m/Y H:i') ?? 'Jamais' }}</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="summary-row">
    <div class="summary-box"><div class="val">{{ $utilisateurs->count() }}</div><div class="lbl">Utilisateurs</div></div>
    <div class="summary-box"><div class="val">{{ $utilisateurs->where('est_actif', true)->count() }}</div><div class="lbl">Actifs</div></div>
</div>
@endsection
