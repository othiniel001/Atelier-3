<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Journal;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Étape 2 : consultation du journal d'audit. */
class JournalController extends Controller
{
    public function index(Request $request): View
    {
        $journaux = Journal::with('user')
            ->when($request->query('action'), fn ($q, $a) => $q->where('action', $a))
            ->when($request->query('modele'), fn ($q, $m) => $q->where('modele_type', 'like', "%{$m}"))
            ->latest('created_at')->latest('id')
            ->paginate(50)
            ->withQueryString();

        return view('admin.journal.index', [
            'journaux' => $journaux,
            'actions' => Journal::distinct()->orderBy('action')->pluck('action'),
        ]);
    }
}
