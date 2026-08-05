@extends('layouts.app')

@section('title', 'My profile')

@section('body_attrs')
 data-page="profile"
@endsection

@section('content')
<div class="page-head">
    <div>
        <h1>My profile</h1>
        <p class="page-sub">Your personal information. Your role is set at registration and cannot be changed.</p>
    </div>
</div>

<div class="detail-grid" style="align-items:start">
    <div class="detail-main" style="display:grid;gap:16px">
        <div class="rounded-card bg-white p-6 sm:p-8">
            <div class="mb-7 flex items-center gap-4">
                <span class="grid size-16 shrink-0 place-items-center rounded-full bg-accent text-lg font-bold text-dark" id="profileAvatar">U</span>
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-dark" id="profileName">User</h2>
                    <div class="mt-1.5 flex flex-wrap items-center gap-2.5">
                        <span class="tag tag-navy" id="profileRole">Candidate</span>
                        <span class="mono text-xs text-body" id="profileEmail">—</span>
                    </div>
                </div>
            </div>

            <form id="profileForm" novalidate>
                <div class="form-alert" style="display:none"></div>
                <div class="grid gap-x-4 sm:grid-cols-2">
                    <div class="form-group">
                        <label class="form-label" for="pfName">Full name</label>
                        <input class="input" type="text" id="pfName" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pfEmail">Email address</label>
                        <input class="input" type="email" id="pfEmail" maxlength="255" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pfPassword">New password <span class="font-normal text-body">(leave blank to keep current)</span></label>
                        <input class="input" type="password" id="pfPassword" minlength="8" autocomplete="new-password" placeholder="••••••••">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="pfPasswordConfirm">Confirm password</label>
                        <input class="input" type="password" id="pfPasswordConfirm" autocomplete="new-password" placeholder="••••••••">
                    </div>
                </div>
                <div class="mt-2 flex justify-end">
                    <button class="btn btn-primary" type="submit">Save changes</button>
                </div>
            </form>
        </div>
    </div>

    <aside class="detail-side">
        <div class="side-card">
            <div class="mb-3 flex items-center gap-2.5">
                <span class="size-2.5 rounded-full bg-secondary shadow-[0_0_0_4px_var(--color-surface)]"></span>
                <h3>Account</h3>
            </div>
            <dl class="kv">
                <dt>Role</dt><dd id="kvRole">—</dd>
                <dt>Member since</dt><dd id="kvJoined">—</dd>
            </dl>
        </div>
    </aside>
</div>
@endsection
