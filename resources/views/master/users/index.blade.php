// resources/views/admin/users/index.blade.php
@extends('layouts.admin')

@section('title', 'Users Management')

@section('content')
<div class="container-fluid">
    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">{{ __('Users Management') }}</h1>
        <a href="{{ route('master.users.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
            <i class="fas fa-plus fa-sm text-white-50"></i> {{ __('Add New User') }}
        </a>
    </div>

    <!-- Users List Card -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">{{ __('All Users') }}</h6>
            
            <!-- Search Form -->
            <div class="col-md-4">
                <form action="{{ route('master.users.index') }}" method="GET" class="d-none d-sm-inline-block form-inline ml-auto">
                    <div class="input-group">
                        <input type="text" class="form-control small" name="search" placeholder="{{ __('Search...') }}" value="{{ request('search') }}">
                        <div class="input-group-append">
                            <button class="btn btn-primary" type="submit">
                                <i class="fas fa-search fa-sm"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('ID') }}</th>
                            <th>{{ __('Name') }}</th>
                            <th>{{ __('Email') }}</th>
                            <th>{{ __('Balance') }}</th>
                            <th>{{ __('Registered') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $user)
                        <tr>
                            <td>{{ $user->id }}</td>
                            <td>{{ $user->name ?? 'N/A' }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->formatted_balance }}</td>
                            <td>{{ $user->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="btn-group" role="group">
                                    <a href="{{ route('master.users.edit', $user) }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <button type="button" class="btn btn-warning btn-sm" data-bs-toggle="modal" data-bs-target="#adjustBalanceModal{{ $user->id }}">
                                        <i class="fas fa-coins"></i>
                                    </button>
                                    <button type="button" class="btn btn-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteUserModal{{ $user->id }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                                
                                <!-- Adjust Balance Modal -->
                                <div class="modal fade" id="adjustBalanceModal{{ $user->id }}" tabindex="-1" aria-labelledby="adjustBalanceModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('master.users.adjust-balance', $user) }}" method="POST">
                                                @csrf
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="adjustBalanceModalLabel{{ $user->id }}">{{ __('Adjust Balance for') }} {{ $user->name }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="mb-3">
                                                        <label for="currentBalance{{ $user->id }}" class="form-label">{{ __('Current Balance') }}</label>
                                                        <input type="text" class="form-control" id="currentBalance{{ $user->id }}" value="{{ $user->formatted_balance }}" disabled>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="amount{{ $user->id }}" class="form-label">{{ __('Adjustment Amount') }}</label>
                                                        <div class="input-group">
                                                            <span class="input-group-text">¥</span>
                                                            <input type="number" class="form-control" id="amount{{ $user->id }}" name="amount" step="0.01" required>
                                                        </div>
                                                        <div class="form-text">{{ __('Use negative values to decrease balance.') }}</div>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="notes{{ $user->id }}" class="form-label">{{ __('Notes') }}</label>
                                                        <textarea class="form-control" id="notes{{ $user->id }}" name="notes" required></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-primary">{{ __('Adjust Balance') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Delete User Modal -->
                                <div class="modal fade" id="deleteUserModal{{ $user->id }}" tabindex="-1" aria-labelledby="deleteUserModalLabel{{ $user->id }}" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <form action="{{ route('master.users.destroy', $user) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteUserModalLabel{{ $user->id }}">{{ __('Delete User') }}</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>{{ __('Are you sure you want to delete this user?') }}</p>
                                                    <p class="text-danger">{{ __('This action cannot be undone. All user data will be permanently deleted.') }}</p>
                                                    <div class="alert alert-warning">
                                                        <strong>{{ __('User details:') }}</strong><br>
                                                        {{ __('Name:') }} {{ $user->name ?? 'N/A' }}<br>
                                                        {{ __('Email:') }} {{ $user->email }}<br>
                                                        {{ __('Registered:') }} {{ $user->created_at->format('Y-m-d') }}
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">{{ __('Cancel') }}</button>
                                                    <button type="submit" class="btn btn-danger">{{ __('Delete User') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">{{ __('No users found.') }}</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <!-- Pagination -->
            <div class="d-flex justify-content-center mt-4">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection