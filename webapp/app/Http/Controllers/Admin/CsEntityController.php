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
            foreach ($defaults as $index => $d) {
                CsEntity::create(array_merge($d, [
                    'sort_order' => $index + 1,
                    'is_scored' => $d['is_scored'] ?? true,
                    'can_vote' => $d['can_vote'] ?? true,
                    'badge_eligible' => $d['badge_eligible'] ?? true,
                    'show_in_ranking' => $d['show_in_ranking'] ?? true,
                    'role_mode' => $d['role_mode'] ?? 'participant',
                ]));
            }
        }

        $entities = CsEntity::orderBy('sort_order')->orderBy('id')->get();
        return view('admin.cs_entities.index', compact('entities'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type'       => 'required|string|max:30|alpha_dash|unique:cs_entities,type',
            'name'       => 'required|string|max:60',
            'role_label' => 'required|string|max:100',
            'color'      => 'required|string|max:20',
            'icon'       => 'nullable|string|max:10',
            'logo'       => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_scored' => 'nullable|boolean',
            'can_vote' => 'nullable|boolean',
            'badge_eligible' => 'nullable|boolean',
            'show_in_ranking' => 'nullable|boolean',
            'role_mode' => 'required|in:participant,mentor',
        ]);

        if ($request->hasFile('logo')) {
            $data['logo_path'] = $request->file('logo')->store('cs_logos', 'public');
        }

        $data['type'] = strtolower(trim($data['type']));
        $data['sort_order'] = (int) ($data['sort_order'] ?? (CsEntity::max('sort_order') + 1));
        $data['icon'] = $data['icon'] ?? '🛡️';
        $data['is_scored'] = (bool) ($data['is_scored'] ?? false);
        $data['can_vote'] = (bool) ($data['can_vote'] ?? false);
        $data['badge_eligible'] = (bool) ($data['badge_eligible'] ?? false);
        $data['show_in_ranking'] = (bool) ($data['show_in_ranking'] ?? false);

        CsEntity::create($data);

        return back()->with('success', 'Entité ajoutée : ' . $data['name']);
    }

    public function update(Request $request, CsEntity $entity)
    {
        $data = $request->validate([
            'type'       => 'required|string|max:30|alpha_dash|unique:cs_entities,type,' . $entity->id,
            'name'       => 'required|string|max:60',
            'role_label' => 'required|string|max:100',
            'color'      => 'required|string|max:20',
            'icon'       => 'nullable|string|max:10',
            'logo'       => 'nullable|image|max:2048',
            'sort_order' => 'nullable|integer|min:0|max:9999',
            'is_scored' => 'nullable|boolean',
            'can_vote' => 'nullable|boolean',
            'badge_eligible' => 'nullable|boolean',
            'show_in_ranking' => 'nullable|boolean',
            'role_mode' => 'required|in:participant,mentor',
        ]);

        if ($request->hasFile('logo')) {
            if ($entity->logo_path) {
                Storage::disk('public')->delete($entity->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('cs_logos', 'public');
        }

        $data['type'] = strtolower(trim($data['type']));
        $data['sort_order'] = (int) ($data['sort_order'] ?? $entity->sort_order);
        $data['icon'] = $data['icon'] ?? '🛡️';
        $data['is_scored'] = (bool) ($data['is_scored'] ?? false);
        $data['can_vote'] = (bool) ($data['can_vote'] ?? false);
        $data['badge_eligible'] = (bool) ($data['badge_eligible'] ?? false);
        $data['show_in_ranking'] = (bool) ($data['show_in_ranking'] ?? false);

        $entity->update($data);

        return back()->with('success', 'Entité mise à jour : ' . $entity->name);
    }

    public function destroy(CsEntity $entity)
    {
        if ($entity->logo_path) {
            Storage::disk('public')->delete($entity->logo_path);
        }

        $name = $entity->name;
        $entity->delete();

        return back()->with('success', 'Entité supprimée : ' . $name);
    }
}
