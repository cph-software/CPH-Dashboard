@extends('layouts.admin')

@section('title', 'Menus')

@section('vendor-style')
    <link rel="stylesheet"
        href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">Menus List</h4>
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuModal">Add New Menu</button>
        </div>

        <!-- Menus Table -->
        <div class="card">
            <div class="card-datatable table-responsive">
                <table class="datatables-menus table border-top">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Application</th>
                            <th>Parent</th>
                            <th>URL</th>
                            <th>Icon</th>
                            <th>Order</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $menu)
                            <tr>
                                <td>{{ $menu->name }}</td>
                                <td>{{ $menu->aplikasi->name ?? '-' }}</td>
                                <td>{{ $menu->parent->name ?? 'Root' }}</td>
                                <td><code>{{ $menu->url }}</code></td>
                                <td><i class="ri {{ $menu->icon }}"></i></td>
                                <td>{{ $menu->order_no }}</td>
                                <td>
                                    @if($menu->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-inline-block text-nowrap">
                                        <button class="btn btn-sm btn-icon edit-menu" data-id="{{ $menu->id }}"><i
                                                class="ri-edit-box-line"></i></button>
                                        <button class="btn btn-sm btn-icon delete-menu" data-id="{{ $menu->id }}"><i
                                                class="ri-delete-bin-line"></i></button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Menu Modal -->
        <div class="modal fade" id="addMenuModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Menu</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('menus.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label">Application</label>
                                <select name="aplikasi_id" class="form-select" required>
                                    @foreach($aplikasi as $app)
                                        <option value="{{ $app->id }}">{{ $app->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Parent Menu</label>
                                <select name="parent_id" class="form-select">
                                    <option value="">Root</option>
                                    @foreach($parentMenus as $pm)
                                        <option value="{{ $pm->id }}">{{ $pm->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Name</label>
                                <input type="text" name="name" class="form-control" required placeholder="e.g. Dashboard">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">URL</label>
                                <input type="text" name="url" class="form-control" required placeholder="e.g. dashboard">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Icon (Remix Icon Class)</label>
                                <input type="text" name="icon" class="form-control" placeholder="e.g. ri-home-line">
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Order Number</label>
                                <input type="number" name="order_no" class="form-control" value="0">
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Save Menu</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
@endsection

@section('page-script')
    <script>
        $(document).ready(function () {
            $('.datatables-menus').DataTable({
                order: [[1, 'asc'], [5, 'asc']]
            });
        });
    </script>
@endsection