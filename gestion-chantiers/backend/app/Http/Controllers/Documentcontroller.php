<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Chantier;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    // ─── Ajouter un document ──────────────────────────────────
    public function store(Request $request, Chantier $chantier)
    {
        $validated = $request->validate([
            'nom'     => 'required|string|max:255',
            'type'    => 'required|string|max:100',
            'fichier' => 'required|file|max:20480', 
        ]);

        $file = $request->file('fichier');
        $path = $file->store('documents/chantiers/' . $chantier->id, 'public');

        $document = Document::create([
            'chantier_id' => $chantier->id,
            'nom'         => $validated['nom'],
            'type'        => $validated['type'],
            'fichier'     => $path,
            'taille'      => $file->getSize(),
            'uploaded_by' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Document ajouté',
            'data'    => [
                'id'           => $document->id,
                'nom'          => $document->nom,
                'type'         => $document->type,
                'taille_format'=> $document->taille_formatee ?? $this->formatSize($document->taille),
                'fichier'      => asset('storage/' . $document->fichier),
                'created_at'   => $document->created_at->format('d/m/Y'),
            ],
        ], 201);
    }

    // ─── Supprimer un document ────────────────────────────────
    public function destroy(Document $document)
    {
        if ($document->fichier && \Storage::disk('public')->exists($document->fichier)) {
            \Storage::disk('public')->delete($document->fichier);
        }
        $document->delete();
        return response()->json(['success' => true, 'message' => 'Document supprimé']);
    }

    private function formatSize(int $bytes): string
    {
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' Mo';
        if ($bytes >= 1024) return round($bytes / 1024, 1) . ' Ko';
        return $bytes . ' o';
    }
}