<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ClasseRequest;
use App\Models\Classe;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ClasseController extends Controller
{
    public function index(): View
    {
        $classes = Classe::with('section')->withCount('eleves')->orderBy('section_id')->orderBy('niveau')->get();

        return view('admin.classes.index', compact('classes'));
    }

    public function create(): View
    {
        return view('admin.classes.form', ['classe' => new Classe, 'sections' => Section::orderBy('nom')->get()]);
    }

    public function store(ClasseRequest $request): RedirectResponse
    {
        Classe::create($request->validated());

        return redirect()->route('admin.classes.index')->with('succes', 'Classe créée.');
    }

    public function edit(Classe $classe): View
    {
        return view('admin.classes.form', ['classe' => $classe, 'sections' => Section::orderBy('nom')->get()]);
    }

    public function update(ClasseRequest $request, Classe $classe): RedirectResponse
    {
        $classe->update($request->validated());

        return redirect()->route('admin.classes.index')->with('succes', 'Classe mise à jour.');
    }

    public function destroy(Classe $classe): RedirectResponse
    {
        if ($classe->eleves()->exists()) {
            return back()->with('erreur', 'Impossible : des élèves sont rattachés à cette classe.');
        }
        $classe->delete();

        return back()->with('succes', 'Classe supprimée.');
    }
}
