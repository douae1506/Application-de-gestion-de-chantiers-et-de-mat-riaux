<?php
// app/Http/Controllers/ExpenseController.php
namespace App\Http\Controllers;

use App\Models\Projet;
use App\Models\ProjectExpense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index(Projet $projet)
    {
        return response()->json([
            'data' => $projet->expenses()->latest()->get()
        ]);
    }

    public function store(Request $request, Projet $projet)
    {
        $validated = $request->validate([
            'nom'     => 'required|string|max:255',
            'montant' => 'required|numeric|min:0',
            'date'    => 'nullable|date',
        ]);

        $expense = $projet->expenses()->create($validated);

        return response()->json([
            'success' => true,
            'data'    => $expense
        ], 201);
    }

    public function destroy(ProjectExpense $expense)
    {
        $expense->delete();
        return response()->json(['success' => true]);
    }
}