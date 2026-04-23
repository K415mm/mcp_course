@extends('layouts.app')
@section('title', 'Gestion des Joueurs — ' . ($session->name ?? 'CARTHAGE SHIELD'))

@push('head')
<style>
/* Moderation styling consistent with moderator.blade.php */
.cs-mono { font-family: 'Space Mono', monospace; }
.user-admin-card { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 8px; padding: 16px; margin-bottom: 16px; }
.user-roster-row { background: rgba(255,255,255,.03); border: 1px solid rgba(255,255,255,.08); border-radius: 8px; padding: 12px; margin-bottom: 8px; }
.user-roster-row.banned { border-color: rgba(239,68,68,.35); background: rgba(239,68,68,.06); }
.user-mini-badge { display: inline-flex; align-items: center; gap: 4px; padding: 2px 7px; border-radius: 999px; font-size: .68rem; font-family: 'Space Mono', monospace; border: 1px solid rgba(255,255,255,.12); }
.user-meta-line { font-size: .8rem; color: rgba(255,255,255,.65); }
.user-roster-actions { display: grid; grid-template-columns: minmax(0,1fr) 150px auto auto auto; gap: 8px; align-items: center; margin-top: 8px; }
@media (max-width: 1200px) { .user-roster-actions { grid-template-columns: 1fr 1fr; } .user-roster-actions .btn { width: 100%; } }
</style>
@endpush

@section('content')
<div class="container-fluid py-4" id="csManagePlayers">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h2 class="mb-1 text-theme cs-mono"><i class="bi bi-people-fill me-2"></i>Gestion des Joueurs</h2>
                    <div class="text-white-50">Session : {{ $session->name }} ({{ $session->code }})</div>
                </div>
                <a href="{{ route('cs.moderator', $session->code) }}" class="btn btn-outline-theme fw-bold">
                    <i class="bi bi-arrow-left me-1"></i>Retour à la session
                </a>
            </div>

            {{-- Pré-affectation (Lobby) --}}
            <div class="card mb-4">
                <div class="card-body">
                    <div class="user-admin-card">
                        <div class="fw-bold text-theme mb-2" style="font-size: 1.1rem;"><i class="bi bi-person-plus me-2"></i>Affectation de nouveaux étudiants</div>
                        <div class="text-white-50 mb-3" id="assignUsersHelp">
                            Ajoutez des étudiants vérifiés aux entités. Recommandé avant le lancement, mais toujours possible en cours de session.
                        </div>
                        <div class="row g-3 align-items-start">
                            <div class="col-md-4">
                                <label class="form-label small text-white-50">Entité cible</label>
                                <select class="form-select" id="assignTeamType"></select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small text-white-50">Étudiants disponibles</label>
                                <select class="form-select" id="assignUserIds" multiple size="6"></select>
                                <div class="user-meta-line mt-1" id="assignableCount">0 étudiants disponibles</div>
                            </div>
                            <div class="col-12 mt-3 text-end">
                                <button class="btn btn-theme fw-bold px-4" id="assignUsersBtn" onclick="assignSelectedUsers()">Assigner à l'entité</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Joueurs actuels --}}
            <div class="card">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <div class="fw-bold text-theme" style="font-size: 1.1rem;"><i class="bi bi-shield-lock me-2"></i>Joueurs actifs dans la session</div>
                        <div class="badge bg-dark fs-6">Total: <span id="usersCount">0</span></div>
                    </div>
                    <div class="text-white-50 mb-3">
                        Déplacez, renommez, bannissez, ou supprimez les joueurs. Les changements s'appliqueront immédiatement pour tous les écrans.
                    </div>
                    
                    <div id="playersRosterArea">
                        <div class="text-white-50 text-center py-4">Chargement des joueurs...</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
const CODE  = '{{ $session->code }}';
const CSRF  = '{{ csrf_token() }}';

let latestSessionState = null;
let playersRosterCache = [];
let assignableUsersCache = [];
const teamsById = {};

async function api(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/cs/${CODE}/api/${path}`, opts);
    return r.json();
}

async function apiChecked(path, method='GET', body=null) {
    const opts = {method, headers:{'X-CSRF-TOKEN':CSRF,'Content-Type':'application/json'}};
    if (body) opts.body = JSON.stringify(body);
    const r = await fetch(`/cs/${CODE}/api/${path}`, opts);
    let data = {};
    try { data = await r.json(); } catch (e) { throw new Error('Réponse serveur invalide.'); }
    if (!r.ok || data?.ok === false || data?.success === false) {
        const validationMessage = data?.errors ? Object.values(data.errors).flat()[0] : null;
        throw new Error(data?.error || validationMessage || 'Opération refusée.');
    }
    return data;
}

function showNotif(msg, type='info') {
    // Fallback basic notification (in case global toast isn't available)
    if (typeof window.showToast === 'function') {
        window.showToast(msg, type);
    } else {
        alert(`${type.toUpperCase()}: ${msg}`);
    }
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str).replace(/[&<>'"]/g, match => {
        const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;' };
        return map[match];
    });
}

function currentTeamsList() {
    return Object.values(teamsById).sort((a, b) => String(a.name || '').localeCompare(String(b.name || '')));
}

async function poll() {
    try {
        const d = await api('state');
        latestSessionState = d.session || null;
        
        if (d.teams) {
            d.teams.forEach(t => teamsById[t.id] = t);
        }

        playersRosterCache = Array.isArray(d.playersRoster) ? d.playersRoster.slice() : [];
        assignableUsersCache = Array.isArray(d.assignableUsers) ? d.assignableUsers.slice() : [];
        
        const usersCount = document.getElementById('usersCount');
        if (usersCount) usersCount.textContent = playersRosterCache.length;
        
        renderAssignmentControls();
        renderPlayersRoster();
    } catch(e) { console.warn('Poll error', e); }
}

function renderAssignmentControls() {
    const teamSelect = document.getElementById('assignTeamType');
    const userSelect = document.getElementById('assignUserIds');
    const assignBtn = document.getElementById('assignUsersBtn');
    const count = document.getElementById('assignableCount');
    if (!teamSelect || !userSelect || !assignBtn || !count) return;

    const selectedTeam = teamSelect.value;
    const teams = currentTeamsList();
    teamSelect.innerHTML = teams.length
        ? teams.map(team => `<option value="${escapeHtml(team.type)}">${escapeHtml(team.icon || '🛡️')} ${escapeHtml(team.name)}</option>`).join('')
        : '<option value="">Aucune entité disponible</option>';
    if (teams.some(team => team.type === selectedTeam)) {
        teamSelect.value = selectedTeam;
    }

    const selectedUsers = Array.from(userSelect.selectedOptions).map(option => option.value);
    userSelect.innerHTML = assignableUsersCache.length
        ? assignableUsersCache.map(user => `<option value="${user.id}">${escapeHtml(user.name)} · ${escapeHtml(user.email)}</option>`).join('')
        : '';
    selectedUsers.forEach(id => {
        const option = Array.from(userSelect.options).find(opt => opt.value === id);
        if (option) option.selected = true;
    });

    const isLobby = String(latestSessionState?.status || '').toLowerCase() === 'lobby';
    
    teamSelect.disabled = !teams.length;
    userSelect.disabled = !assignableUsersCache.length;
    assignBtn.disabled = !teams.length || !assignableUsersCache.length;
    
    count.textContent = `${assignableUsersCache.length} étudiants disponibles`;
}

function renderPlayersRoster() {
    const root = document.getElementById('playersRosterArea');
    if (!root) return;
    if (!playersRosterCache.length) {
        root.innerHTML = '<div class="text-white-50 text-center py-5">Aucun joueur affecté pour cette session.</div>';
        return;
    }

    const teams = currentTeamsList();
    const rows = playersRosterCache
        .slice()
        .sort((a, b) => `${a.teamName || ''}${a.displayName || ''}`.localeCompare(`${b.teamName || ''}${b.displayName || ''}`))
        .map(player => {
            const teamOptions = teams.map(team => `
                <option value="${escapeHtml(team.type)}" ${team.type === player.teamType ? 'selected' : ''}>
                    ${escapeHtml(team.name)}
                </option>
            `).join('');
            const badges = [
                `<span class="user-mini-badge" style="color:${player.isOnline ? '#22c55e' : 'rgba(255,255,255,.6)'}">${player.isOnline ? 'ONLINE' : 'OFFLINE'}</span>`,
                `<span class="user-mini-badge" style="color:${player.isBanned ? '#ef4444' : '#60a5fa'}">${player.isBanned ? 'BANNED' : 'ACTIVE'}</span>`,
                player.assignmentSource ? `<span class="user-mini-badge">${escapeHtml(String(player.assignmentSource).toUpperCase())}</span>` : '',
            ].join(' ');

            return `
                <div class="user-roster-row ${player.isBanned ? 'banned' : ''}" id="player-row-${player.id}">
                    <div class="d-flex align-items-start justify-content-between gap-2 mb-2">
                        <div>
                            <div class="fw-bold" style="font-size:1rem">${escapeHtml(player.displayName || 'Unnamed player')}</div>
                            <div class="user-meta-line">${escapeHtml(player.email || 'No linked email')} · ${escapeHtml(player.teamName || 'Sans entité')}</div>
                        </div>
                        <div class="d-flex gap-1 flex-wrap justify-content-end">${badges}</div>
                    </div>
                    ${player.isBanned && player.bannedReason ? `<div class="user-meta-line mb-2 text-danger">Raison: ${escapeHtml(player.bannedReason)}</div>` : ''}
                    <div class="user-roster-actions">
                        <input type="text" class="form-control form-control-sm" id="player-name-${player.id}" value="${escapeHtml(player.displayName || '')}" maxlength="80">
                        <select class="form-select form-select-sm" id="player-team-${player.id}">
                            ${teamOptions}
                        </select>
                        <button class="btn btn-sm btn-outline-theme" onclick="savePlayer(${player.id})"><i class="bi bi-check2"></i> Sauver</button>
                        <button class="btn btn-sm ${player.isBanned ? 'btn-outline-success' : 'btn-outline-warning'}" onclick="${player.isBanned ? `unbanPlayerAction(${player.id})` : `banPlayerAction(${player.id})`}">
                            ${player.isBanned ? '<i class="bi bi-unlock"></i> Unban' : '<i class="bi bi-slash-circle"></i> Ban'}
                        </button>
                        <button class="btn btn-sm btn-outline-danger" onclick="removePlayerAction(${player.id})"><i class="bi bi-trash"></i> Retirer</button>
                    </div>
                </div>
            `;
        })
        .join('');

    root.innerHTML = rows;
}

async function assignSelectedUsers() {
    const teamType = document.getElementById('assignTeamType')?.value;
    const selectedIds = Array.from(document.getElementById('assignUserIds')?.selectedOptions || []).map(option => parseInt(option.value, 10)).filter(Boolean);
    if (!teamType) {
        showNotif('Choisissez une entité cible.', 'warn');
        return;
    }
    if (!selectedIds.length) {
        showNotif('Sélectionnez au moins un étudiant.', 'warn');
        return;
    }

    try {
        if (selectedIds.length === 1) {
            await apiChecked('players/assign', 'POST', { user_id: selectedIds[0], team_type: teamType });
        } else {
            await apiChecked('players/assign-bulk', 'POST', { user_ids: selectedIds, team_type: teamType });
        }
        showNotif(`${selectedIds.length} utilisateur(s) affecté(s).`, 'success');
        await poll();
    } catch (e) {
        showNotif(e.message || 'Affectation refusée.', 'danger');
    }
}

async function savePlayer(playerId) {
    const name = (document.getElementById(`player-name-${playerId}`)?.value || '').trim();
    const teamType = document.getElementById(`player-team-${playerId}`)?.value;
    if (!name || !teamType) {
        showNotif('Nom et entité sont requis.', 'warn');
        return;
    }

    try {
        await apiChecked(`players/${playerId}`, 'PUT', {
            display_name: name,
            team_type: teamType,
        });
        showNotif('Joueur mis à jour.', 'success');
        await poll();
    } catch (e) {
        showNotif(e.message || 'Mise à jour refusée.', 'danger');
    }
}

async function banPlayerAction(playerId) {
    const reason = prompt('Raison du bannissement (optionnel) :', '') ?? '';
    try {
        await apiChecked(`players/${playerId}/ban`, 'POST', { reason: reason.trim() });
        showNotif('Joueur banni.', 'success');
        await poll();
    } catch (e) {
        showNotif(e.message || 'Bannissement refusé.', 'danger');
    }
}

async function unbanPlayerAction(playerId) {
    try {
        await apiChecked(`players/${playerId}/unban`, 'POST');
        showNotif('Joueur débanni.', 'success');
        await poll();
    } catch (e) {
        showNotif(e.message || 'Débannissement refusé.', 'danger');
    }
}

async function removePlayerAction(playerId) {
    if (!confirm('Supprimer ce joueur de la session ? Il devra être ré-assigné.')) return;
    try {
        await apiChecked(`players/${playerId}`, 'DELETE');
        showNotif('Joueur supprimé de la session.', 'success');
        await poll();
    } catch (e) {
        showNotif(e.message || 'Suppression refusée.', 'danger');
    }
}

// Initial poll
poll();
setInterval(poll, 3000);
</script>
@endpush