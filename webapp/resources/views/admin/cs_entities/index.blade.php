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
                        <div class="mb-3">
                            <label class="form-label small text-white-50">Logo Personnalisé (Image)</label>
                            <input type="file" class="form-control form-control-sm" name="logo" accept="image/*">
                        </div>

                        <button type="submit" class="btn btn-sm btn-outline-theme w-100">Enregistrer</button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endsection
