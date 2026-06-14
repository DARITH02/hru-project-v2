@extends('layouts.app')

@section('title', 'Telegram Bots Management')

@section('content')
<div class="container-fluid py-4">
    <div class="row">
        <!-- Add New Bot Form -->
        <div class="col-md-4">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-plus-circle me-2"></i>Add New Telegram Bot</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('admin.telegram-bots.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label">Bot Name</label>
                            <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" placeholder="e.g. Attendance Alerts Bot" value="{{ old('name') }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="bot_token" class="form-label">Bot Token</label>
                            <input type="password" name="bot_token" id="bot_token" class="form-control @error('bot_token') is-invalid @enderror" placeholder="123456789:ABCDefgh..." required>
                            <small class="text-muted">Get this from @BotFather on Telegram.</small>
                            @error('bot_token')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <hr>
                        <div class="alert alert-info py-2 small">
                            <i class="fas fa-info-circle me-1"></i> <strong>Note:</strong> Start a chat with your bot or add it to a group and send a message <strong>before</strong> adding it here to automatically detect the Chat ID.
                        </div>
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-save me-1"></i> Add Bot & Fetch Chat ID
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Bots List -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0 text-primary">Registered Bots</h5>
                    <span class="badge bg-primary">{{ $bots->count() }} Bot(s)</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Bot Name</th>
                                    <th>Chat ID</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end pe-4">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($bots as $bot)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center">
                                            <div class="avatar avatar-sm bg-gradient-primary rounded-circle me-2 d-flex align-items-center justify-content-center text-white" style="width: 32px; height: 32px;">
                                                <i class="fab fa-telegram-plane"></i>
                                            </div>
                                            <div>
                                                <span class="fw-bold d-block">{{ $bot->name }}</span>
                                                <small class="text-muted">Token: ****{{ substr($bot->bot_token, -6) }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($bot->chat_id)
                                            <code class="text-primary fw-bold">{{ $bot->chat_id }}</code>
                                        @else
                                            <span class="badge bg-warning text-dark">Missing ID</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($bot->is_active)
                                            <span class="badge bg-success shadow-sm">
                                                <i class="fas fa-check-circle me-1"></i>Active
                                            </span>
                                        @else
                                            <span class="badge bg-secondary opacity-75">Inactive</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex justify-content-end gap-2">
                                            <!-- Set Active -->
                                            @if(!$bot->is_active)
                                            <form action="{{ route('admin.telegram-bots.active', $bot->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success" title="Set as Active Notification Bot">
                                                    <i class="fas fa-power-off"></i>
                                                </button>
                                            </form>
                                            @endif

                                            <!-- Send Test -->
                                            <form action="{{ route('admin.telegram-bots.test', $bot->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-info" title="Send Test Message" {{ !$bot->chat_id ? 'disabled' : '' }}>
                                                    <i class="fas fa-paper-plane"></i>
                                                </button>
                                            </form>

                                            <!-- Delete -->
                                            <form action="{{ route('admin.telegram-bots.destroy', $bot->id) }}" method="POST" onsubmit="return confirmSubmit(event, 'Are you sure you want to delete this bot?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete Bot">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="4" class="text-center py-5">
                                        <div class="text-muted">
                                            <i class="fas fa-robot fa-3x mb-3 opacity-25"></i>
                                            <p class="mb-0">No Telegram Bots configured yet.</p>
                                            <small>Add your first bot to start receiving notifications.</small>
                                        </div>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
