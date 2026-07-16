<?php

namespace App\Http\Controllers;

use App\Models\Chantier;
use App\Models\EntreeStock;
use App\Models\Produit;
use App\Models\Projet;
use App\Models\SortieStock;
use App\Models\Stock;
use App\Models\Transfert;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    private function author(Request $request): string
    {
        return $request->user()?->nom_complet ?? '—';
    }

    public function chantiers(Request $request)
    {
        $query = Chantier::with('client');
        if ($request->filled('search')) $query->search($request->search);
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('type')) $query->where('type', $request->type);
        $chantiers = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.chantiers', [
            'chantiers' => $chantiers,
            'title' => 'Liste des chantiers',
            'subtitle' => $chantiers->count() . ' chantier(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('chantiers_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function projets(Request $request)
    {
        $query = Projet::with('chantier');
        if ($request->filled('search')) $query->where('nom', 'like', '%' . $request->search . '%');
        if ($request->filled('statut')) $query->where('statut', $request->statut);
        if ($request->filled('chantier_id')) $query->where('chantier_id', $request->chantier_id);
        $projets = $query->orderBy('created_at', 'desc')->get();

        $pdf = Pdf::loadView('pdf.projets', [
            'projets' => $projets,
            'title' => 'Liste des projets',
            'subtitle' => $projets->count() . ' projet(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('projets_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function produits(Request $request)
    {
        $query = Produit::with('stocks');
        if ($request->filled('search')) $query->where('nom', 'like', '%' . $request->search . '%');
        if ($request->filled('categorie')) $query->where('categorie', $request->categorie);
        $produits = $query->orderBy('nom')->get();

        $pdf = Pdf::loadView('pdf.produits', [
            'produits' => $produits,
            'title' => 'Liste des produits',
            'subtitle' => $produits->count() . ' produit(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('produits_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function stocks(Request $request)
    {
        $query = Stock::with('produits');
        if ($request->filled('stock_id')) $query->where('id', $request->stock_id);
        $stocks = $query->orderBy('nom')->get();

        $pdf = Pdf::loadView('pdf.stocks', [
            'stocks' => $stocks,
            'title' => 'État des stocks',
            'subtitle' => $stocks->count() . ' dépôt(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('stocks_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function entrees(Request $request)
    {
        $query = EntreeStock::with(['produit', 'stock', 'fournisseur']);
        if ($request->filled('stock_id')) $query->where('stock_id', $request->stock_id);
        if ($request->filled('produit_id')) $query->where('produit_id', $request->produit_id);
        if ($request->filled('date_debut')) $query->whereDate('date_entree', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date_entree', '<=', $request->date_fin);
        $entrees = $query->orderBy('date_entree', 'desc')->get();

        $pdf = Pdf::loadView('pdf.entrees', [
            'entrees' => $entrees,
            'title' => "Liste des entrées de stock",
            'subtitle' => $entrees->count() . ' entrée(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('entrees_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function sorties(Request $request)
    {
        $query = SortieStock::with(['produit', 'stock', 'chantier']);
        if ($request->filled('stock_id')) $query->where('stock_id', $request->stock_id);
        if ($request->filled('produit_id')) $query->where('produit_id', $request->produit_id);
        if ($request->filled('date_debut')) $query->whereDate('date_sortie', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date_sortie', '<=', $request->date_fin);
        $sorties = $query->orderBy('date_sortie', 'desc')->get();

        $pdf = Pdf::loadView('pdf.sorties', [
            'sorties' => $sorties,
            'title' => 'Liste des sorties de stock',
            'subtitle' => $sorties->count() . ' sortie(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('sorties_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function transferts(Request $request)
    {
        $query = Transfert::with(['produit', 'stockSource', 'stockDestination']);
        if ($request->filled('stock_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('stock_source_id', $request->stock_id)
                  ->orWhere('stock_destination_id', $request->stock_id);
            });
        }
        if ($request->filled('produit_id')) $query->where('produit_id', $request->produit_id);
        if ($request->filled('date_debut')) $query->whereDate('date_transfert', '>=', $request->date_debut);
        if ($request->filled('date_fin')) $query->whereDate('date_transfert', '<=', $request->date_fin);
        $transferts = $query->orderBy('date_transfert', 'desc')->get();

        $pdf = Pdf::loadView('pdf.transferts', [
            'transferts' => $transferts,
            'title' => 'Liste des transferts de stock',
            'subtitle' => $transferts->count() . ' transfert(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'landscape');

        return $pdf->download('transferts_' . now()->format('Y-m-d_His') . '.pdf');
    }

    public function utilisateurs(Request $request)
    {
        $query = User::query();
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nom', 'like', '%' . $request->search . '%')
                  ->orWhere('prenom', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }
        if ($request->filled('role')) $query->where('role', $request->role);
        $utilisateurs = $query->orderBy('nom')->get();

        $pdf = Pdf::loadView('pdf.utilisateurs', [
            'utilisateurs' => $utilisateurs,
            'title' => 'Liste des utilisateurs',
            'subtitle' => $utilisateurs->count() . ' utilisateur(s)',
            'generatedBy' => $this->author($request),
        ])->setPaper('a4', 'portrait');

        return $pdf->download('utilisateurs_' . now()->format('Y-m-d_His') . '.pdf');
    }
}
