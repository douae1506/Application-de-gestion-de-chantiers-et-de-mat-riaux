<?php
	
	namespace App\Http\Controllers;
	use App\Models\Event;
	use App\Models\Chantier;
	use Illuminate\Http\Request;
	use Illuminate\Validation\Rule;
	
	class EventController extends Controller
	{
	    // Récupérer tous les événements d'un chantier
	    public function index(Chantier $chantier)
	    {
	        $events = $chantier->events()->orderBy('date', 'asc')->orderBy('heure', 'asc')->get();
	        return response()->json(['data' => $events]);
	    }
	
	    // Créer un événement pour un chantier
	    public function store(Request $request, Chantier $chantier)
	    {
	        $validated = $request->validate([
	            'titre'       => 'required|string|max:255',
	            'description' => 'nullable|string',
	            'date'        => 'required|date|after_or_equal:today', 
	            'heure'       => 'nullable|date_format:H:i',
	            'type'        => ['required', Rule::in(['reunion', 'livraison', 'inspection', 'autre'])],
	            'statut'      => ['sometimes', Rule::in(['a_venir', 'termine', 'annule'])],
	            'rappel'      => 'sometimes|boolean',
	        ]);
	
	        $validated['chantier_id'] = $chantier->id;
	        $validated['statut'] = $validated['statut'] ?? 'a_venir';
	
	        $event = Event::create($validated);
	
	        return response()->json([
	            'success' => true,
	            'data'    => $event,
	        ], 201);
	    }
	
	    // Afficher un événement (sécurisé : doit appartenir au chantier de l'URL)
	    public function show(Chantier $chantier, Event $event)
	    {
	        $this->ensureBelongsToChantier($chantier, $event);
	        return response()->json(['data' => $event]);
	    }
	
	    // Mettre à jour un événement
	    public function update(Request $request, Chantier $chantier, Event $event)
	    {
	        $this->ensureBelongsToChantier($chantier, $event);
	
	        $validated = $request->validate([
	            'titre'       => 'sometimes|string|max:255',
	            'description' => 'nullable|string',
	            'date'        => 'sometimes|date', 
	            'heure'       => 'nullable|date_format:H:i',
	            'type'        => ['sometimes', Rule::in(['reunion', 'livraison', 'inspection', 'autre'])],
	            'statut'      => ['sometimes', Rule::in(['a_venir', 'termine', 'annule'])],
	            'rappel'      => 'sometimes|boolean',
	        ]);
	
	        $event->update($validated);
	
	        return response()->json([
	            'success' => true,
	            'data'    => $event,
	        ]);
	    }
	
	    // Supprimer un événement
	    public function destroy(Chantier $chantier, Event $event)
	    {
	        $this->ensureBelongsToChantier($chantier, $event);
	        $event->delete();
	        return response()->json(['success' => true]);
	    }
	
	    // Sécurité : empêche d'agir sur l'événement d'un autre chantier 
	    private function ensureBelongsToChantier(Chantier $chantier, Event $event): void
	    {
	        if ($event->chantier_id !== $chantier->id) {
	            abort(404, 'Événement introuvable pour ce chantier.');
	        }
	    }
	
	    public function all(Request $request)
	{
	    $userId = $request->user()->id;
	    $role = $request->user()->role;

	    // Les événements liés à un chantier sont visibles par toute l'équipe.
	    // Les événements "personnels" (sans chantier) ne sont visibles que
	    // par leur créateur.
	    // Le magasinier n'étant pas rattaché aux chantiers, il ne voit que
	    // son propre agenda personnel.
	    $query = Event::with('chantier');

	    if ($role === 'magasinier') {
	        $query->whereNull('chantier_id')->where('user_id', $userId);
	    } else {
	        $query->where(function ($q) use ($userId) {
	            $q->whereNotNull('chantier_id')
	              ->orWhere('user_id', $userId);
	        });
	    }
	
	    if ($request->filled('month')) {
	        $query->whereMonth('date', $request->month);
	    }
	
	    if ($request->filled('year')) {
	        $query->whereYear('date', $request->year);
	    }
   	
   	    $events = $query
   	        ->orderBy('date')
   	        ->orderBy('heure')
   	        ->get()
   	        ->map(function ($event) {
   	            return [
   	                'id' => $event->id,
   	                'titre' => $event->titre,
   	                'description' => $event->description,
   	                'date' => $event->date,
   	                'heure' => $event->heure,
   	                'type' => $event->type,
   	                'statut' => $event->statut,
   	                'chantier_id' => $event->chantier_id,
   	                'chantier_nom' => optional($event->chantier)->nom,
   	            ];
   	        });
   	
   	    return response()->json([
   	        'data' => $events
   	    ]);
   	}
   	
   	public function storeGlobal(Request $request)
   	{
   	    // chantier_id est désormais optionnel : un événement sans chantier
   	    // est un événement "personnel", rattaché à l'utilisateur connecté.
   	    $validated = $request->validate([
   	        'chantier_id' => 'nullable|exists:chantiers,id',
   	        'titre'       => 'required|string|max:255',
   	        'description' => 'nullable|string',
   	        'date'        => 'required|date',
   	        'heure'       => 'nullable|date_format:H:i',
   	        'type'        => ['required', Rule::in(['reunion', 'livraison', 'inspection', 'autre'])],
   	    ]);
   	
   	    $validated['statut'] = 'a_venir';
   	    $validated['user_id'] = $request->user()->id;
   	
   	    // Le magasinier ne gere pas de chantiers : ses evenements sont
   	    // toujours personnels, quel que soit ce qui est envoye.
   	    if ($request->user()->role === 'magasinier') {
   	        $validated['chantier_id'] = null;
   	    }
   	
   	    $event = Event::create($validated);
   	
   	    return response()->json([
   	        'success' => true,
   	        'data' => $event
   	    ], 201);
   	}
   	}