<?php

namespace App\Http\Controllers;
use App\Models\Client;

use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
    return response()->json([
        'data' => Client::orderBy('created_at','desc')->get()
    ]);
    }

public function show(Client $client)
{
     $client->load('chantiers');

    return response()->json([
        'success' => true,
        'data' => $client
    ]);
}
    public function store(Request $request)
{
    $validated = $request->validate([
    'nom' => 'required|string|max:255',
    'prenom' => 'nullable|string|max:255',
    'email' => 'nullable|email|unique:clients,email',
    'telephone' => 'nullable|string|max:50',
    'telephone_secondaire' => 'nullable|string|max:50',
    'ville' => 'nullable|string|max:255',
    'adresse' => 'nullable|string|max:500',
    'entreprise' => 'nullable|string|max:255',
    'type_client' => 'required|in:particulier,entreprise',
    'notes' => 'nullable|string',
    'dernier_contact' => 'nullable|date',
]);

   

    $client = Client::create($validated);

    return response()->json([
        'success' => true,
        'message' => 'Client créé avec succès',
        'data' => $client
    ], 201);
}

    public function update(Request $request, Client $client)
    {
   $validated = $request->validate([
    'nom' => 'required|string|max:255',
    'prenom' => 'nullable|string|max:255',
    'email' => 'nullable|email|unique:clients,email,' . $client->id,
    'telephone' => 'nullable|string|max:50',
    'telephone_secondaire' => 'nullable|string|max:50',
    'ville' => 'nullable|string|max:255',
    'adresse' => 'nullable|string|max:500',
    'entreprise' => 'nullable|string|max:255',
    'type_client' => 'required|in:particulier,entreprise',
    'notes' => 'nullable|string',
    'dernier_contact' => 'nullable|date',
]);

$client->update($validated);

return response()->json([
    'success' => true,
    'data' => $client
]);
    }

    public function destroy(Client $client)
    {
        $client->delete();

        return response()->json(['message' => 'Client supprimé']);
    }
}