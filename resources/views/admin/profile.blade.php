@extends('layouts.app')

@section('content')
@php
    $initial = strtoupper(substr($user->name ?? 'S', 0, 1));
    $roleLabel = strtoupper(str_replace('_', ' ', $user->role ?? 'super_admin'));
    $profilePhotoUrl = $user->primaryPhoto?->url;
@endphp

<div class="page-header">
    <div>
        <div class="breadcrumb">
            <span>Admin</span>
            <span class="breadcrumb-sep">/</span>
            <span class="breadcrumb-current">Profile</span>
        </div>
        <h1 class="page-title">Super Admin Profile</h1>
        <p class="page-subtitle">Manage your own account identity, contact details, and password.</p>
    </div>
</div>

@if (session('success'))
    <div class="panel" style="margin-bottom:18px;border-color:color-mix(in srgb,var(--green) 28%,var(--border));background:color-mix(in srgb,var(--green) 8%,var(--surface));">
        <div style="padding:14px 18px;color:var(--green);font-family:var(--font-mono);font-size:11px;font-weight:800;letter-spacing:.06em;text-transform:uppercase;">
            {{ session('success') }}
        </div>
    </div>
@endif

@if ($errors->any())
    <div class="panel" style="margin-bottom:18px;border-color:color-mix(in srgb,var(--red) 28%,var(--border));background:color-mix(in srgb,var(--red) 7%,var(--surface));">
        <div style="padding:14px 18px;color:var(--red);font-size:12px;line-height:1.7;">
            @foreach ($errors->all() as $error)
                <div>{{ $error }}</div>
            @endforeach
        </div>
    </div>
@endif

<div class="main-grid" style="grid-template-columns:minmax(0,1fr) 340px;align-items:start;">
    <form action="{{ route('admin.profile.update') }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="panel">
            <div class="panel-head" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--accent);box-shadow:0 0 10px var(--accent)"></div>
                <span style="font-family:var(--font-mono);font-size:10px;font-weight:800;letter-spacing:.12em;color:var(--text2);text-transform:uppercase;">Account Information</span>
            </div>
            <div class="panel-body" style="padding:24px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="profile-name">Full name</label>
                        <input id="profile-name" name="name" class="form-input" type="text" value="{{ old('name', $user->name) }}" autocomplete="name" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="profile-email">Email address</label>
                        <input id="profile-email" name="email" class="form-input" type="email" value="{{ old('email', $user->email) }}" autocomplete="email" required>
                    </div>
                </div>

                <div class="form-grid-2" style="margin-top:12px;">
                    <div class="form-group">
                        <label class="form-label" for="profile-phone">Phone number</label>
                        <input id="profile-phone" name="phone" class="form-input" type="text" value="{{ old('phone', $user->phone) }}" autocomplete="tel" placeholder="+855 ...">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="profile-current-password">Current password</label>
                        <input id="profile-current-password" name="current_password" class="form-input" type="password" autocomplete="current-password" required>
                        <p style="margin:6px 0 0;color:var(--muted);font-family:var(--font-mono);font-size:9px;">Required to save profile changes.</p>
                    </div>
                </div>

                <div class="form-group" style="margin-top:12px;margin-bottom:0;">
                    <label class="form-label" for="profile-photo">Profile photo</label>
                    <input id="profile-photo" name="profile_photo" class="form-input" type="file" accept="image/*">
                    <p style="margin:6px 0 0;color:var(--muted);font-family:var(--font-mono);font-size:9px;">JPG, PNG, WEBP, or GIF up to 5MB.</p>
                </div>
            </div>
        </div>

        <div class="panel" style="margin-top:20px;">
            <div class="panel-head" style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;align-items:center;gap:10px;">
                <div style="width:8px;height:8px;border-radius:50%;background:var(--amber);box-shadow:0 0 10px var(--amber)"></div>
                <span style="font-family:var(--font-mono);font-size:10px;font-weight:800;letter-spacing:.12em;color:var(--text2);text-transform:uppercase;">Password</span>
            </div>
            <div class="panel-body" style="padding:24px;">
                <div class="form-grid-2">
                    <div class="form-group">
                        <label class="form-label" for="profile-password">New password</label>
                        <input id="profile-password" name="password" class="form-input" type="password" autocomplete="new-password" placeholder="Leave blank to keep current password">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="profile-password-confirmation">Confirm new password</label>
                        <input id="profile-password-confirmation" name="password_confirmation" class="form-input" type="password" autocomplete="new-password">
                    </div>
                </div>

                <div style="margin-top:22px;border-top:1px solid var(--border);padding-top:20px;display:flex;justify-content:flex-end;gap:10px;flex-wrap:wrap;">
                    <a href="{{ route('admin.dashboard') }}" class="btn-secondary" style="height:42px;display:inline-flex;align-items:center;text-decoration:none;">Cancel</a>
                    <button type="submit" class="btn-primary" style="height:42px;min-width:180px;font-weight:800;letter-spacing:.05em;">Save Profile</button>
                </div>
            </div>
        </div>
    </form>

    <aside class="panel">
        <div class="panel-body" style="padding:24px;">
            <div style="display:flex;align-items:center;gap:14px;">
                @if($profilePhotoUrl)
                    <img src="{{ $profilePhotoUrl }}" alt="{{ $user->name }}" style="width:64px;height:64px;border-radius:18px;object-fit:cover;border:1px solid var(--border);">
                @else
                    <div style="width:64px;height:64px;border-radius:18px;background:linear-gradient(135deg,var(--accent),#0f766e);color:#fff;display:flex;align-items:center;justify-content:center;font-size:24px;font-weight:900;font-family:var(--font-display);">
                        {{ $initial }}
                    </div>
                @endif
                <div style="min-width:0;">
                    <div style="font-family:var(--font-display);font-size:18px;font-weight:900;color:var(--text);line-height:1.2;">{{ $user->name }}</div>
                    <div style="margin-top:5px;font-family:var(--font-mono);font-size:9px;font-weight:800;letter-spacing:.1em;color:var(--accent);">{{ $roleLabel }}</div>
                </div>
            </div>

            <div style="margin-top:22px;display:grid;gap:12px;">
                <div style="padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);">
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Email</div>
                    <div style="margin-top:5px;font-size:12px;color:var(--text);word-break:break-word;">{{ $user->email }}</div>
                </div>
                <div style="padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);">
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Phone</div>
                    <div style="margin-top:5px;font-size:12px;color:var(--text);">{{ $user->phone ?: 'Not set' }}</div>
                </div>
                <div style="padding:12px;border:1px solid var(--border);border-radius:10px;background:var(--surface2);">
                    <div style="font-family:var(--font-mono);font-size:9px;color:var(--muted);font-weight:800;letter-spacing:.08em;text-transform:uppercase;">Account</div>
                    <div style="margin-top:5px;font-size:12px;color:var(--text);">Created {{ optional($user->created_at)->format('M d, Y') }}</div>
                </div>
            </div>
        </div>
    </aside>
</div>
@endsection
