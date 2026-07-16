<?php

namespace App\Http\Controllers;

use App\Models\Fournisseur;
use Illuminate\Http\Request;

class FournisseurController extends Controller
{
    public function index()
    {
        $fournisseurs = Fournisseur::latest()->get();
        return response()->json(['data' => $fournisseurs]);
    }
    public function show(Fournisseur $fournisseur)
    {
        return response()->json(['data' => $fournisseur]);
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nom'         => 'required|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255|unique:fournisseurs,email', // ← ici
            'telephone'   => 'nullable|string|max:50',
            'adresse'     => 'nullable|string',
            'ville'       => 'nullable|string|max:100',
            'pays'        => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'site_web'    => 'nullable|url|max:255',
            'observations'=> 'nullable|string',
        ]);

        $fournisseur = Fournisseur::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur créé avec succès.',
            'data'    => $fournisseur,
        ], 201);
    }
    public function update(Request $request, Fournisseur $fournisseur)
    {
        $validated = $request->validate([
            'nom'         => 'sometimes|string|max:255',
            'responsable' => 'nullable|string|max:255',
            'email'        => 'nullable|email|max:255|unique:fournisseurs,email,' . $fournisseur->id,
            'telephone'   => 'nullable|string|max:50',
            'adresse'     => 'nullable|string',
            'ville'       => 'nullable|string|max:100',
            'pays'        => 'nullable|string|max:100',
            'code_postal' => 'nullable|string|max:20',
            'site_web'    => 'nullable|url|max:255',
            'observations'=> 'nullable|string',
        ]);

        $fournisseur->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Fournisseur mis à jour.',
            'data'    => $fournisseur,
        ]);
    }
    public function destroy(Fournisseur $fournisseur)
    {
        $fournisseur->delete();
        return response()->json([
            'success' => true,
            'message' => 'Fournisseur supprimé.',
        ]);
    } 

    public function produits(Fournisseur $fournisseur)
{
    $produits = $fournisseur->produits()
        ->select('produits.id', 'produits.nom', 'produits.unite', 'produits.prix_unitaire')
        ->get()
        ->map(fn ($p) => [
            'id'            => $p->id,
            'nom'           => $p->nom,
            'unite'         => $p->unite,
            'prix_unitaire' => (float) $p->prix_unitaire,
        ]);

    return response()->json(['data' => $produits]);
}
}