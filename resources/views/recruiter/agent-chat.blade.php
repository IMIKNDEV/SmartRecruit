@extends('layouts.app')

@section('title', 'Agent IA')

@section('body_attrs')
 data-page="recruiterAgentChat"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>Agent IA</h1>
        <p class="page-sub">Générez des questions d'entretien et discutez avec l'assistant de recrutement.</p>
    </div>
    <button class="btn btn-ghost" type="button" id="newConvBtn"><x-icon name="plus" :size="16" /> Nouvelle conversation</button>
</div>

<div class="chat-layout">
    <aside class="card">
        <div style="padding:16px">
            <div class="side-section-label" style="margin-bottom:8px">Conversations</div>
            <div id="convList" style="display:grid;gap:6px"><div class="empty">Chargement…</div></div>
        </div>
    </aside>

    <div class="card chat-thread">
        <div class="agent-head" id="agentHead">
            <div style="display:flex;align-items:center;gap:10px">
                <span class="avatar avatar-sm" style="background:var(--accent,#6366f1);color:#fff">IA</span>
                <div>
                    <div style="font-weight:600;color:var(--ink)">Assistant SmartRecruit</div>
                    <div class="mono" style="font-size:11.5px;color:var(--slate)" id="convTitle">Aucune conversation</div>
                </div>
            </div>
        </div>
        <div class="chat-messages" id="chatThread"><div class="empty">Sélectionnez une conversation ou générez des questions.</div></div>
        <form class="chat-input" id="chatForm">
            <input class="input" type="text" id="chatText" placeholder="Écrivez un message…" disabled autocomplete="off">
            <button class="btn btn-primary" type="submit" id="chatSend" disabled><x-icon name="send" :size="16" /></button>
        </form>
    </div>
</div>
@endsection
