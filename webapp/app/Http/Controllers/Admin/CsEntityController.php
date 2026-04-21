<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CsEntity;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CsEntityController extends Controller
{
    public function index()
    {
        // First populate default if empty
        if (CsEntity::count() === 0) {
            $defaults = \App\Models\CsTeam::defaultTeams(); // it falls back to hardcoded since DB is empty
            foreach($defaults as $d) {
                CsEntity::create($d);
            }
        }
        $entities = CsEntity::all();
        return view('admin.cs_entities.index', compact('entities'));
    }

    public function update(Request $request, CsEntity $entity)
    {
        $data = $request->validate([
            'name'       => 'required|string|max:60',
            'role_label' => 'required|string|max:100',
            'color'      => 'required|string|max:20',
            'icon'       => 'nullable|string|max:10',
            'logo'       => 'nullable|image|max:2048'
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('cs_logos', 'public');
        }

        $entity->update($data);

        return back()->with('success', 'Entité mise à jour : ' . $entity->name);
    }
}
