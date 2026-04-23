@extends('layouts.app')
@section('title', 'CARTHAGE SHIELD — Entités Globales')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="page-header mb-0"><i class="bi bi-shield-lock text-theme me-2"></i>Entités Globales Carthage Shield</h1>
        <a href="{{ route('admin.cs.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>Retour</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-4">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show mb-4">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card mb-4">
        <div class="card-body">
            <h5 class="mb-3"><i class="bi bi-plus-circle me-2 text-theme"></i>Ajouter une entité</h5>
            <form method="POST" action="{{ route('admin.cs.entities.store') }}" enctype="multipart/form-data">
                @csrf
                <div class="row gx-2">
                    <div class="col-md-2 mb-2">
                        <label class="form-label small text-white-50">Type</label>
                        <input class="form-control form-control-sm" name="type" placeholder="ex: legal" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small text-white-50">Nom</label>
                        <input class="form-control form-control-sm" name="name" required>
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small text-white-50">Rôle</label>
                        <input class="form-control form-control-sm" name="role_label" required>
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="form-label small text-white-50">Couleur</label>
                        <input type="color" class="form-control form-control-sm w-100 px-1" name="color" value="#00b4d8">
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="form-label small text-white-50">Icône</label>
                        <input class="form-control form-control-sm text-center" name="icon" value="🛡️">
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="form-label small text-white-50">Ordre</label>
                        <input type="number" min="0" class="form-control form-control-sm" name="sort_order" value="0">
                    </div>
                    <div class="col-md-2 mb-2">
                        <label class="form-label small text-white-50">Mode rôle</label>
                        <select class="form-select form-select-sm" name="role_mode">
                            <option value="participant">Participant</option>
                            <option value="mentor">Mentor</option>
                        </select>
                    </div>
                    <div class="col-md-1 mb-2">
                        <label class="form-label small text-white-50">Logo</label>
                        <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3 mt-2 mb-3">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_scored" value="1" checked id="createIsScored">
                        <label class="form-check-label small" for="createIsScored">Score</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="can_vote" value="1" checked id="createCanVote">
                        <label class="form-check-label small" for="createCanVote">Vote</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="badge_eligible" value="1" checked id="createBadge">
                        <label class="form-check-label small" for="createBadge">Badge</label>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="show_in_ranking" value="1" checked id="createRanking">
                        <label class="form-check-label small" for="createRanking">Classement</label>
                    </div>
                </div>
                <button type="submit" class="btn btn-sm btn-theme">Ajouter</button>
            </form>
        </div>
    </div>

    <div class="row gx-4">
        @foreach($entities as $entity)
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card h-100">
                <div class="card-arrow"><div class="card-arrow-top-left"></div><div class="card-arrow-top-right"></div><div class="card-arrow-bottom-left"></div><div class="card-arrow-bottom-right"></div></div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.cs.entities.update', $entity) }}" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="d-flex align-items-center mb-3">
                            <div class="me-3">
                                @if($entity->logo_path)
                                    <img src="{{ Storage::url($entity->logo_path) }}" alt="{{ $entity->name }}" style="width: 60px; height: 60px; object-fit: contain; background: rgba(255,255,255,0.05); border-radius: 8px; padding: 4px;">
                                @else
                                    <div style="width: 60px; height: 60px; font-size: 2rem; display: flex; align-items: center; justify-content: center; background: rgba(255,255,255,0.05); border-radius: 8px;">
                                        {{ $entity->icon }}
                                    </div>
                                @endif
                            </div>
                            <div style="flex:1;">
                                <h5 class="mb-0" style="color: {{ $entity->color }}">{{ $entity->type }}</h5>
                                <div class="small text-white-50">#{{ $entity->id }}</div>
                            </div>
                        </div>

                        <div class="mb-2">
                            <label class="form-label small text-white-50">Type</label>
                            <input class="form-control form-control-sm" name="type" value="{{ $entity->type }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-white-50">Nom</label>
                            <input class="form-control form-control-sm" name="name" value="{{ $entity->name }}" required>
                        </div>
                        <div class="mb-2">
                            <label class="form-label small text-white-50">Rôle</label>
                            <input class="form-control form-control-sm" name="role_label" value="{{ $entity->role_label }}" required>
                        </div>
                        <div class="row gx-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-white-50">Couleur</label>
                                <input type="color" class="form-control form-control-sm w-100 px-1" name="color" value="{{ $entity->color }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-white-50">Icône Fallback</label>
                                <input class="form-control form-control-sm text-center" name="icon" value="{{ $entity->icon }}">
                            </div>
                        </div>
                        <div class="row gx-2 mb-2">
                            <div class="col-6">
                                <label class="form-label small text-white-50">Ordre</label>
                                <input type="number" min="0" class="form-control form-control-sm" name="sort_order" value="{{ $entity->sort_order ?? 0 }}">
                            </div>
                            <div class="col-6">
                                <label class="form-label small text-white-50">Mode rôle</label>
                                <select class="form-select form-select-sm" name="role_mode">
                                    <option value="participant" {{ ($entity->role_mode ?? 'participant') === 'participant' ? 'selected' : '' }}>Participant</option>
                                    <option value="mentor" {{ ($entity->role_mode ?? 'participant') === 'mentor' ? 'selected' : '' }}>Mentor</option>
                                </select>
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-3 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="is_scored" value="1" id="isScored{{ $entity->id }}" {{ $entity->is_scored ? 'checked' : '' }}>
                                <label class="form-check-label small" for="isScored{{ $entity->id }}">Score</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="can_vote" value="1" id="canVote{{ $entity->id }}" {{ $entity->can_vote ? 'checked' : '' }}>
                                <label class="form-check-label small" for="canVote{{ $entity->id }}">Vote</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="badge_eligible" value="1" id="badgeEligible{{ $entity->id }}" {{ $entity->badge_eligible ? 'checked' : '' }}>
                                <label class="form-check-label small" for="badgeEligible{{ $entity->id }}">Badge</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="show_in_ranking" value="1" id="showRank{{ $entity->id }}" {{ $entity->show_in_ranking ? 'checked' : '' }}>
                                <label class="form-check-label small" for="showRank{{ $entity->id }}">Classement</label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Logo Personnalisé (Image)</label>
                            <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-theme w-100 mb-2">Enregistrer</button>
                    </form>
                    <form method="POST" action="{{ route('admin.cs.entities.destroy', $entity) }}" onsubmit="return confirmFormSubmit(event, 'Supprimer cette entité ?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger w-100">Supprimer</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
