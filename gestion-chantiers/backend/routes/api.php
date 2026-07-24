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
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ExportController;
use App\Http\Controllers\PrintController;
use App\Http\Controllers\AiAssistantController;

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

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::get('/unread-count', [NotificationController::class, 'unreadCount']);
        Route::put('/{id}/read', [NotificationController::class, 'markAsRead']);
        Route::put('/read-all', [NotificationController::class, 'markAllAsRead']);
    });
    // ────────────────────────────────────────────────────────────────
    // Espace applicatif partagé : admin, responsable, chef_projet et
    // magasinier accèdent tous à ces routes. L'accès fin est contrôlé
    // permission par permission (voir config/permissions.php), pas
    // par le rôle. Le préfixe "admin" est conservé pour rester
    // compatible avec le frontend existant.
    // ────────────────────────────────────────────────────────────────
    Route::prefix('admin')->group(function () {

        Route::middleware('permission:view_dashboard')->get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord']));
        Route::middleware('permission:view_dashboard')->get('/dashboard/stats', [DashboardController::class, 'stats']);

        // USERS (admin uniquement dans la matrice de permissions)
        // Route statique déclarée AVANT /users/{user} pour éviter qu'elle ne
        // soit interceptée par la contrainte de permission 'view_users'.
        Route::middleware('permission:create_projets,edit_projets')->get('/users/chefs-projet', [UserController::class, 'chefsProjet']);
        Route::middleware('permission:view_users')->group(function () {
            Route::get('/users',        [UserController::class, 'index']);
            Route::get('/users/{user}', [UserController::class, 'show']);
        });
        Route::middleware('permission:create_users')->post('/users', [UserController::class, 'store']);
        Route::middleware('permission:edit_users')->group(function () {
            Route::put('/users/{user}', [UserController::class, 'update']);
            Route::patch('/users/{user}/toggle-status', [UserController::class, 'toggleStatus']);
        });
        Route::middleware('permission:delete_users')->group(function () {
            Route::delete('/users/{user}', [UserController::class, 'destroy']);
            Route::delete('/users',        [UserController::class, 'destroyMany']);
        });

        // CLIENTS
        Route::middleware('permission:view_clients')->group(function () {
            Route::get('/clients',           [ClientController::class, 'index']);
            Route::get('/clients/{client}',  [ClientController::class, 'show']);
        });
        Route::middleware('permission:create_clients')->post('/clients', [ClientController::class, 'store']);
        Route::middleware('permission:edit_clients')->put('/clients/{client}', [ClientController::class, 'update']);
        Route::middleware('permission:delete_clients')->delete('/clients/{client}', [ClientController::class, 'destroy']);

        // CHANTIERS
        Route::middleware('permission:view_chantiers')->group(function () {
            Route::get('chantiers',                    [ChantierController::class, 'index']);
            Route::get('chantiers/{chantier}',          [ChantierController::class, 'show']);
            Route::get('chantiers/{chantier}/projets',  [ChantierController::class, 'projets']);
        });
        Route::middleware('permission:create_chantiers')->post('chantiers', [ChantierController::class, 'store']);
        Route::middleware('permission:edit_chantiers')->put('chantiers/{chantier}', [ChantierController::class, 'update']);
        Route::middleware('permission:delete_chantiers')->delete('chantiers/{chantier}', [ChantierController::class, 'destroy']);
        Route::middleware('permission:create_projets')->post('chantiers/{chantier}/projets', [ChantierController::class, 'storeProjet']);
        Route::middleware('permission:create_documents')->post('chantiers/{chantier}/documents', [DocumentController::class, 'store']);
        Route::middleware('permission:delete_documents')->delete('documents/{document}', [DocumentController::class, 'destroy']);
        Route::middleware('permission:view_documents')->get('chantiers/{chantier}/documents', [DocumentController::class, 'index']);

        // PROJETS
        Route::middleware('permission:view_projets')->group(function () {
            Route::get('projets',          [ProjetController::class, 'index']);
            Route::get('projets/{projet}', [ProjetController::class, 'show']);
            Route::get('/chantiers/{chantier}/projets', [ProjetController::class, 'byChantier']);
        });
        Route::middleware('permission:create_projets')->post('projets', [ProjetController::class, 'store']);
        Route::middleware('permission:edit_projets')->put('projets/{projet}', [ProjetController::class, 'update']);
        Route::middleware('permission:delete_projets')->delete('projets/{projet}', [ProjetController::class, 'destroy']);

        Route::middleware('permission:view_depenses')->get('projets/{projet}/expenses', [ExpenseController::class, 'index']);
        Route::middleware('permission:create_depenses')->post('projets/{projet}/expenses', [ExpenseController::class, 'store']);
        Route::middleware('permission:delete_depenses')->delete('expenses/{expense}', [ExpenseController::class, 'destroy']);

        Route::middleware('permission:create_phases')->post('projets/{projet}/phases', [PhaseController::class, 'store']);
        Route::middleware('permission:edit_phases')->put('phases/{phase}', [PhaseController::class, 'update']);
        Route::middleware('permission:delete_phases')->delete('phases/{phase}', [PhaseController::class, 'destroy']);

        // ─── PRODUITS (catalogue) ───────────────────────────────
        Route::middleware('permission:view_produits')->group(function () {
            Route::get('produits',              [ProduitController::class, 'index']);
            Route::get('produits/categories',   [ProduitController::class, 'categories']);
            Route::get('produits/{produit}',    [ProduitController::class, 'show']);
        });
        Route::middleware('permission:create_produits')->post('produits', [ProduitController::class, 'store']);
        Route::middleware('permission:edit_produits')->put('produits/{produit}', [ProduitController::class, 'update']);
        Route::middleware('permission:delete_produits')->delete('produits/{produit}', [ProduitController::class, 'destroy']);

        // ─── FOURNISSEURS ────────────────────────────────────────
        Route::middleware('permission:view_fournisseurs')->group(function () {
            Route::get('/fournisseurs',                        [FournisseurController::class, 'index']);
            Route::get('/fournisseurs/{fournisseur}',          [FournisseurController::class, 'show']);
            Route::get('fournisseurs/{fournisseur}/produits',  [FournisseurController::class, 'produits']);
        });
        Route::middleware('permission:create_fournisseurs')->post('/fournisseurs', [FournisseurController::class, 'store']);
        Route::middleware('permission:edit_fournisseurs')->put('/fournisseurs/{fournisseur}', [FournisseurController::class, 'update']);
        Route::middleware('permission:delete_fournisseurs')->delete('/fournisseurs/{fournisseur}', [FournisseurController::class, 'destroy']);

        // ─── DÉPÔTS (stocks) ────────────────────────────────────
        Route::middleware('permission:view_stocks')->group(function () {
            Route::get('stocks',                                   [StockController::class, 'index']);
            Route::get('stocks/{stock}',                           [StockController::class, 'show']);
            Route::get('stocks/{stock}/produits/{produit}/pivot',  [StockController::class, 'getPivot']);
        });
        Route::middleware('permission:manage_stocks')->group(function () {
            Route::post('stocks',                                     [StockController::class, 'store']);
            Route::put('stocks/{stock}',                              [StockController::class, 'update']);
            Route::delete('stocks/{stock}',                           [StockController::class, 'destroy']);
            Route::post('stocks/{stock}/affecter-produit',           [StockController::class, 'affecterProduit']);
            Route::put('/stocks/{stock}/produits/{produit}',         [StockController::class, 'updateProduitPivot']);
        });

        // ─── MOUVEMENTS (entrées / sorties / transferts) ────────
        Route::middleware('permission:view_mouvements')->get('mouvements', [MouvementController::class, 'index']);
        Route::middleware('permission:create_entree')->post('mouvements/entree', [MouvementController::class, 'entree']);
        Route::middleware('permission:create_sortie')->post('mouvements/sortie', [MouvementController::class, 'sortie']);
        // Affecter un matériel sorti à un projet / annuler (retour au stock) :
        // permission dédiée, distincte de 'create_sortie', pour pouvoir
        // l'accorder au responsable sans l'accorder au magasinier.
        Route::middleware('permission:affecter_sortie_projet')->group(function () {
            Route::put('/mouvements/sortie/{sortie}/affecter-projet', [MouvementController::class, 'affecterSortieAProjet']);
            Route::post('/mouvements/sortie/{sortie}/retour-stock',   [MouvementController::class, 'retourStock']);
            Route::post('/mouvements/sortie/retour-stock-bulk',       [MouvementController::class, 'retourStockBulk']);
        });
        Route::middleware('permission:create_transfert')->post('mouvements/transfert', [MouvementController::class, 'transfert']);
        Route::middleware('permission:delete_mouvement')->delete('mouvements/{mouvement}', [MouvementController::class, 'destroy']);

        // ─── EXPORTS PDF ─────────────────────────────────────────
        Route::prefix('exports')->group(function () {
            Route::middleware('permission:view_chantiers')->get('chantiers',    [ExportController::class, 'chantiers']);
            Route::middleware('permission:view_projets')->get('projets',       [ExportController::class, 'projets']);
            Route::middleware('permission:view_produits')->get('produits',     [ExportController::class, 'produits']);
            Route::middleware('permission:view_stocks')->get('stocks',         [ExportController::class, 'stocks']);
            Route::middleware('permission:view_mouvements')->get('entrees',    [ExportController::class, 'entrees']);
            Route::middleware('permission:view_mouvements')->get('sorties',    [ExportController::class, 'sorties']);
            Route::middleware('permission:view_mouvements')->get('transferts', [ExportController::class, 'transferts']);
            Route::middleware('permission:view_users')->get('utilisateurs',    [ExportController::class, 'utilisateurs']);
        });

        // ─── IMPRESSION (bons + liste stock personnalisée) ──────
        Route::prefix('print')->group(function () {
            Route::middleware('permission:view_mouvements')->get('bon-entree/{entree}',       [PrintController::class, 'bonEntree']);
            Route::middleware('permission:view_mouvements')->get('bon-sortie/{sortie}',       [PrintController::class, 'bonSortie']);
            Route::middleware('permission:view_mouvements')->get('bon-transfert/{transfert}', [PrintController::class, 'bonTransfert']);
            Route::middleware('permission:view_stocks')->get('stock-personnalise',            [PrintController::class, 'stockPersonnalise']);
        });

        // ─── RAPPORTS (réservé admin, aucune permission dédiée dans la matrice) ──
        Route::middleware('role:admin')->group(function () {
            Route::get('rapports/stock',        [RapportController::class, 'stock']);
            Route::get('rapports/mouvements',   [RapportController::class, 'mouvements']);
            Route::get('rapports/chantiers',    [RapportController::class, 'chantiers']);
        });

        // ─── ASSISTANT IA (Gemini) ────────────────────────────────
        // Chat : accessible à tous les rôles ayant accès au tableau de bord.
        Route::middleware('permission:view_dashboard')->post('ai/chat', [AiAssistantController::class, 'chat']);
        // Résumé IA d'un chantier : mêmes droits que la consultation d'un chantier.
        Route::middleware('permission:view_chantiers')->post('ai/chantiers/{chantier}/resume', [AiAssistantController::class, 'chantierResume']);
        // Analyse IA du stock : mêmes droits que la consultation des dépôts.
        Route::middleware('permission:view_stocks')->post('ai/stock/analyse', [AiAssistantController::class, 'stockAnalyse']);

Route::middleware('permission:view_evenements')->get(
    '/events',
    [EventController::class, 'all']
);
Route::middleware('permission:create_evenements')->post(
    '/events',
    [EventController::class, 'storeGlobal']
);
        // Événements d'un chantier
        Route::middleware('permission:view_evenements')->group(function () {
            Route::get('/chantiers/{chantier}/events',            [EventController::class, 'index']);
            Route::get('/chantiers/{chantier}/events/{event}',    [EventController::class, 'show']);
        });
        Route::middleware('permission:create_evenements')->post('/chantiers/{chantier}/events', [EventController::class, 'store']);
        Route::middleware('permission:edit_evenements')->put('/chantiers/{chantier}/events/{event}', [EventController::class, 'update']);
        Route::middleware('permission:delete_evenements')->delete('/chantiers/{chantier}/events/{event}', [EventController::class, 'destroy']);

        // Historique (réservé admin, aucune permission dédiée dans la matrice)
        Route::middleware('role:admin')->group(function () {
            Route::get('/activities',                       [ActivityController::class, 'index']);
            Route::get('/activities/stats',                 [ActivityController::class, 'stats']);
            Route::get('/activities/subject/{type}/{id}',   [ActivityController::class, 'bySubject']);
        });
    });

    // Anciennes routes "dashboard" par rôle : conservées pour compatibilité,
    // mais /admin/dashboard (permission:view_dashboard) fait déjà le travail
    // pour les 4 rôles désormais.
    Route::middleware('role:admin,chef_projet')->prefix('chef-projet')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Chef de Projet']));
    });

    Route::middleware('role:admin,magasinier')->prefix('magasinier')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Magasinier']));
    });

    Route::middleware('role:admin,responsable')->prefix('responsable')->group(function () {
        Route::get('/dashboard', fn () => response()->json(['message' => 'Tableau de bord Responsable']));
    });
});