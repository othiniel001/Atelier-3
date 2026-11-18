<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SectionRequest;
use App\Models\Section;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SectionController extends Controller
{
    public function index(): View
    {
        return view('admin.sections.index', ['sections' => Section::withCount('classes')->orderBy('nom')->get()]);
    }

    public function create(): View
    {
        return view('admin.sections.form', ['section' => new Section]);
    }

    public function store(SectionRequest $request): RedirectResponse
    {
        Section::create($request->validated());

        return redirect()->route('admin.sections.index')->with('succes', 'Section créée.');
    }

    public function edit(Section $section): View
    {
        return view('admin.sections.form', compact('section'));
    }

    public function update(SectionRequest $request, Section $section): RedirectResponse
    {
        $section->update($request->validated());

        return redirect()->route('admin.sections.index')->with('succes', 'Section mise à jour.');
    }

    public function destroy(Section $section): RedirectResponse
    {
        if ($section->classes()->exists()) {
            return back()->with('erreur', 'Impossible : cette section contient des classes.');
        }
        $section->delete();

        return back()->with('succes', 'Section supprimée.');
    }
}
