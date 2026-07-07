<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\ChantierController;
use App\Http\Controllers\ProjetController;
use App\Http\Controllers\ProduitController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\MouvementController;
use App\Http\Controllers\RapportController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\PhaseController;
use App\Http\Controllers\FournisseurController;
use App\Http\Controllers\EventController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ResponsableController;

Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login',    [AuthController::class, 'login']);
    Route::post('/password/send-otp',    [\App\Http\Controllers\PasswordResetController::class, 'sendOtp']);
    Route::post('/password/verify-otp',  [\App\Http\Controllers\PasswordResetController::class, 'verifyOtp']);
    Route::post('/password/reset',       [\App\Http\Controllers\PasswordResetController::class, 'resetPassword']);
});

Route::middleware('auth:sanctum')->group(function () {

    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me',      [AuthController::class, 'me']);

    Route::middleware('role:admin')->prefix('admin')->group(function () {

        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Admin']));

        // USERS
        Route::get   ('/users',                  [UserController::class, 'index']);
        Route::post  ('/users',                  [UserController::class, 'store']);
        Route::get   ('/users/{user}',           [UserController::class, 'show']);
        Route::put   ('/users/{user}',           [UserController::class, 'update']);
        Route::delete('/users/{user}',           [UserController::class, 'destroy']);
        Route::delete('/users',                  [UserController::class, 'destroyMany']);
        Route::patch ('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);

        // CLIENTS
        Route::apiResource('clients', ClientController::class);

        // CHANTIERS
        Route::get   ('chantiers',                      [ChantierController::class, 'index']);
        Route::post  ('chantiers',                      [ChantierController::class, 'store']);
        Route::get   ('chantiers/{chantier}',           [ChantierController::class, 'show']);
        Route::put   ('chantiers/{chantier}',           [ChantierController::class, 'update']);
        Route::delete('chantiers/{chantier}',           [ChantierController::class, 'destroy']);
        Route::get   ('chantiers/{chantier}/projets',   [ChantierController::class, 'projets']);
        Route::post  ('chantiers/{chantier}/projets',   [ChantierController::class, 'storeProjet']);
        Route::post  ('chantiers/{chantier}/documents', [DocumentController::class, 'store']);
        Route::delete('documents/{document}',           [DocumentController::class, 'destroy']);

        // PROJETS
        Route::get   ('projets',               [ProjetController::class, 'index']);
        Route::get   ('projets/{projet}',      [ProjetController::class, 'show']);
        Route::post  ('projets',               [ProjetController::class, 'store']);
        Route::put   ('projets/{projet}',      [ProjetController::class, 'update']);
        Route::delete('projets/{projet}',      [ProjetController::class, 'destroy']);
        Route::get   ('projets/{projet}/expenses', [ExpenseController::class, 'index']);
        Route::post  ('projets/{projet}/expenses', [ExpenseController::class, 'store']);
        Route::delete('expenses/{expense}',        [ExpenseController::class, 'destroy']);
        Route::post  ('projets/{projet}/phases',   [PhaseController::class, 'store']);
        Route::put   ('phases/{phase}',            [PhaseController::class, 'update']);
        Route::delete('phases/{phase}',            [PhaseController::class, 'destroy']);

        // ─── PRODUITS (catalogue) ───────────────────────────────
        Route::get   ('produits',              [ProduitController::class, 'index']);
        Route::post  ('produits',              [ProduitController::class, 'store']);
        Route::get   ('produits/categories',   [ProduitController::class, 'categories']);
        Route::get   ('produits/{produit}',    [ProduitController::class, 'show']);
        Route::put   ('produits/{produit}',    [ProduitController::class, 'update']);
        Route::delete('produits/{produit}',    [ProduitController::class, 'destroy']);
        Route::get   ('/fournisseurs', [FournisseurController::class, 'index']);
        Route::post  ('/fournisseurs', [FournisseurController::class, 'store']);
        Route::get   ('/fournisseurs/{fournisseur}', [FournisseurController::class, 'show']);
        Route::put   ('/fournisseurs/{fournisseur}', [FournisseurController::class, 'update']);
        Route::delete('/fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy']);
        Route::get('fournisseurs/{fournisseur}/produits', [FournisseurController::class, 'produits']);
        Route::get('stocks/{stock}/produits/{produit}/pivot', [StockController::class, 'getPivot']);
        Route::put('/mouvements/sortie/{sortie}/affecter-projet', [MouvementController::class, 'affecterSortieAProjet']);
        Route::post('/mouvements/sortie/{sortie}/retour-stock', [MouvementController::class, 'retourStock']);
       
        // ─── DÉPÔTS (stocks) ────────────────────────────────────
        Route::get   ('stocks',                         [StockController::class, 'index']);
        Route::post  ('stocks',                         [StockController::class, 'store']);
        Route::get   ('stocks/{stock}',                 [StockController::class, 'show']);
        Route::put   ('stocks/{stock}',                 [StockController::class, 'update']);
        Route::delete('stocks/{stock}',                 [StockController::class, 'destroy']);
        Route::post  ('stocks/{stock}/affecter-produit',[StockController::class, 'affecterProduit']);
        Route::put   ('/stocks/{stock}/produits/{produit}', [StockController::class, 'updateProduitPivot']);
        // ─── MOUVEMENTS (entrées / sorties / transferts) ────────
        Route::get  ('mouvements',            [MouvementController::class, 'index']);
        Route::post ('mouvements/entree',     [MouvementController::class, 'entree']);
        Route::post ('mouvements/sortie',     [MouvementController::class, 'sortie']);
        Route::post ('mouvements/transfert',  [MouvementController::class, 'transfert']);
        Route::get  ('/admin/chantiers/{chantier}/projets', [ProjetController::class,'byChantier']);

        // ─── RAPPORTS ────────────────────────────────────────────
        Route::get  ('rapports/stock',        [RapportController::class, 'stock']);
        Route::get  ('rapports/mouvements',   [RapportController::class, 'mouvements']);
        Route::get  ('rapports/chantiers',    [RapportController::class, 'chantiers']);

        // Événements d'un chantier
        Route::get('/chantiers/{chantier}/events', [EventController::class, 'index']);
        Route::post('/chantiers/{chantier}/events', [EventController::class, 'store']);
        Route::get('/chantiers/{chantier}/events/{event}', [EventController::class, 'show']);
        Route::put('/chantiers/{chantier}/events/{event}', [EventController::class, 'update']);
        Route::delete('/chantiers/{chantier}/events/{event}', [EventController::class, 'destroy']);

        Route::get('/activities', [ActivityController::class, 'index']);
        Route::get('/activities/stats', [ActivityController::class, 'stats']);
        Route::get('/activities/subject/{type}/{id}', [ActivityController::class, 'bySubject']);
        Route::get('/dashboard/stats', [App\Http\Controllers\DashboardController::class, 'stats']);
        
    });

    Route::apiResource('projets', ProjetController::class)->only(['index', 'show', 'store', 'update', 'destroy']);

    Route::middleware('role:admin,chef_projet')->prefix('chef-projet')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Chef de Projet']));
    });

    // ─────────────────────────────────────────────────────────
    // ESPACE RESPONSABLE
    // ─────────────────────────────────────────────────────────
    Route::middleware('role:admin,responsable')->prefix('responsable')->group(function () {

        // 1. Tableau de bord
        Route::get('/dashboard', [ResponsableController::class, 'dashboard']);

        // 2. Consulter la liste des chantiers
        Route::get('/chantiers',            [ResponsableController::class, 'chantiers']);
        Route::get('/chantiers/{chantier}', [ResponsableController::class, 'chantierShow']);

        // 3. Créer un projet (dans un chantier)
        Route::post('/chantiers/{chantier}/projets', [ResponsableController::class, 'storeProjet']);

        // Projets du responsable
        Route::get('/projets',           [ResponsableController::class, 'projets']);
        Route::get('/projets/{projet}',  [ResponsableController::class, 'projetShow']);

        // 4. Planifier des phases (extend de "Créer un projet")
        Route::post('/projets/{projet}/phases', [ResponsableController::class, 'storePhase']);
        Route::put('/phases/{phase}',           [ResponsableController::class, 'updatePhase']);
        Route::delete('/phases/{phase}',        [ResponsableController::class, 'destroyPhase']);

        // 5. Mettre à jour l'avancement d'une phase
        Route::patch('/phases/{phase}/avancement', [ResponsableController::class, 'updateAvancement']);

        // 6. Visualiser le planning
        Route::get('/planning', [ResponsableController::class, 'planning']);

        // 7. Visualiser la consommation d'un projet
        Route::get('/projets/{projet}/consommation', [ResponsableController::class, 'consommation']);
    });

    Route::middleware('role:admin,magasinier')->prefix('magasinier')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Magasinier']));
    });
});