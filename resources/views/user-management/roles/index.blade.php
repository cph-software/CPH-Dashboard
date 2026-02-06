@extends('layouts.admin')

@section('title', 'Roles')

@section('vendor-style')
    <link rel="stylesheet"
        href="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('template/full-version/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css') }}" />
    <link rel="stylesheet"
        href="{{ asset('template/full-version/assets/vendor/libs/@form-validation/form-validation.css') }}" />
@endsection

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <h4 class="mb-1">Roles List</h4>
        <p class="mb-6">A role provided access to predefined menus and features so that depending on assigned role an
            administrator can have access to what he need.</p>

        <!-- Role cards -->
        <div class="row g-6">
            @foreach($roles as $role)
                <div class="col-xl-4 col-lg-6 col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center mb-4">
                                <h6 class="fw-normal mb-0 text-body">Total {{ $role->users_count }} users</h6>
                                <ul class="list-unstyled d-flex align-items-center avatar-group mb-0">
                                    {{-- Avatars would go here --}}
                                </ul>
                            </div>
                            <div class="d-flex justify-content-between align-items-end">
                                <div class="role-heading">
                                    <h5 class="mb-1">{{ $role->name }}</h5>
                                    <a href="javascript:;" data-bs-toggle="modal" data-bs-target="#editRoleModal"
                                        class="role-edit-modal" data-id="{{ $role->id }}"><span>Edit Role</span></a>
                                </div>
                                <a href="javascript:void(0);" class="text-secondary"><i
                                        class="ri-file-copy-line ri-22px"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            <div class="col-xl-4 col-lg-6 col-md-6">
                <div class="card h-100">
                    <div class="row h-100">
                        <div class="col-sm-5">
                            <div class="d-flex align-items-end h-100 justify-content-center mt-sm-0 mt-4">
                                <img src="{{ asset('template/full-version/assets/img/illustrations/add-new-role-illustration.png') }}"
                                    class="img-fluid" alt="Image" width="100">
                            </div>
                        </div>
                        <div class="col-sm-7">
                            <div class="card-body text-sm-end text-center ps-sm-0">
                                <button data-bs-target="#addRoleModal" data-bs-toggle="modal"
                                    class="btn btn-sm btn-primary mb-4 text-nowrap add-new-role">Add New Role</button>
                                <p class="mb-0">Add role, if it does not exist</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--/ Role cards -->

        <!-- Add Role Modal -->
        <div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered modal-add-new-role">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-10">
                        <div class="text-center mb-6">
                            <h4 class="role-title mb-2">Add New Role</h4>
                            <p>Set role permissions</p>
                        </div>
                        <!-- Add role form -->
                        <form id="addRoleForm" class="row g-6" method="POST" action="{{ route('roles.store') }}">
                            @csrf
                            <div class="col-12">
                                <label class="form-label" for="modalRoleName">Role Name</label>
                                <input type="text" id="modalRoleName" name="name" class="form-control"
                                    placeholder="Enter role name" tabindex="-1" />
                            </div>
                            <div class="col-12">
                                <h5 class="mb-2">Role Permissions</h5>
                                <!-- Permission table -->
                                <div class="table-responsive">
                                    <table class="table table-flush-spacing">
                                        <tbody>
                                            <tr>
                                                <td class="text-nowrap fw-medium text-heading">Administrator Access <i
                                                        class="ri-information-line" data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Allows a full access to the system"></i></td>
                                                <td>
                                                    <div class="d-flex justify-content-end">
                                                        <div class="form-check mb-0">
                                                            <input class="form-check-input" type="checkbox"
                                                                id="selectAll" />
                                                            <label class="form-check-label" for="selectAll"> Select All
                                                            </label>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            @foreach($menus as $menu)
                                                <tr>
                                                    <td class="text-nowrap fw-medium text-heading">{{ $menu->name }}</td>
                                                    <td>
                                                        <div class="d-flex justify-content-end">
                                                            <div class="form-check mb-0 me-4">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[{{ $menu->id }}][]" value="view"
                                                                    id="menuView{{ $menu->id }}" />
                                                                <label class="form-check-label" for="menuView{{ $menu->id }}">
                                                                    View </label>
                                                            </div>
                                                            <div class="form-check mb-0 me-4">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[{{ $menu->id }}][]" value="create"
                                                                    id="menuCreate{{ $menu->id }}" />
                                                                <label class="form-check-label" for="menuCreate{{ $menu->id }}">
                                                                    Create </label>
                                                            </div>
                                                            <div class="form-check mb-0 me-4">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[{{ $menu->id }}][]" value="update"
                                                                    id="menuUpdate{{ $menu->id }}" />
                                                                <label class="form-check-label" for="menuUpdate{{ $menu->id }}">
                                                                    Update </label>
                                                            </div>
                                                            <div class="form-check mb-0">
                                                                <input class="form-check-input" type="checkbox"
                                                                    name="permissions[{{ $menu->id }}][]" value="delete"
                                                                    id="menuDelete{{ $menu->id }}" />
                                                                <label class="form-check-label" for="menuDelete{{ $menu->id }}">
                                                                    Delete </label>
                                                            </div>
                                                        </div>
                                                    </td>
                                                </tr>
                                                @if($menu->children->count() > 0)
                                                    @foreach($menu->children as $child)
                                                        <tr>
                                                            <td class="text-nowrap fw-medium text-heading ps-6">— {{ $child->name }}
                                                            </td>
                                                            <td>
                                                                <div class="d-flex justify-content-end">
                                                                    <div class="form-check mb-0 me-4">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $child->id }}][]" value="view"
                                                                            id="menuView{{ $child->id }}" />
                                                                        <label class="form-check-label" for="menuView{{ $child->id }}">
                                                                            View </label>
                                                                    </div>
                                                                    <div class="form-check mb-0 me-4">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $child->id }}][]" value="create"
                                                                            id="menuCreate{{ $child->id }}" />
                                                                        <label class="form-check-label"
                                                                            for="menuCreate{{ $child->id }}"> Create </label>
                                                                    </div>
                                                                    <div class="form-check mb-0 me-4">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $child->id }}][]" value="update"
                                                                            id="menuUpdate{{ $child->id }}" />
                                                                        <label class="form-check-label"
                                                                            for="menuUpdate{{ $child->id }}"> Update </label>
                                                                    </div>
                                                                    <div class="form-check mb-0">
                                                                        <input class="form-check-input" type="checkbox"
                                                                            name="permissions[{{ $child->id }}][]" value="delete"
                                                                            id="menuDelete{{ $child->id }}" />
                                                                        <label class="form-check-label"
                                                                            for="menuDelete{{ $child->id }}"> Delete </label>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <!-- Permission table -->
                            </div>
                            <div class="col-12 text-center">
                                <button type="submit" class="btn btn-primary me-3">Submit</button>
                                <button type="reset" class="btn btn-outline-secondary" data-bs-dismiss="modal"
                                    aria-label="Close">Cancel</button>
                            </div>
                        </form>
                        <!--/ Add role form -->
                    </div>
                </div>
            </div>
        </div>
        <!--/ Add Role Modal -->
    </div>
@endsection

@section('vendor-script')
    <script src="{{ asset('template/full-version/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js') }}"></script>
    <script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/popular.js') }}"></script>
    <script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/bootstrap5.js') }}"></script>
    <script src="{{ asset('template/full-version/assets/vendor/libs/@form-validation/auto-focus.js') }}"></script>
@endsection

@section('page-script')
    <script>
        'use strict';
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.querySelector('#selectAll'),
                checkboxList = document.querySelectorAll('[type="checkbox"]');

            selectAll.addEventListener('change', t => {
                checkboxList.forEach(e => {
                    e.checked = t.target.checked;
                });
            });

            // Edit Role Logic
            $('.role-edit-modal').on('click', function() {
                const id = $(this).data('id');
                const url = '{{ url("user-management/roles") }}/' + id + '/edit';
                
                $.get(url, function(role) {
                    $('#modalRoleName').val(role.name);
                    $('#addRoleForm').attr('action', '{{ url("user-management/roles") }}/' + id);
                    $('#addRoleForm').append('<input type="hidden" name="_method" value="PUT">');
                    $('.role-title').text('Edit Role');
                    
                    // Reset checkboxes
                    checkboxList.forEach(e => e.checked = false);
                    
                    // Check relevant checkboxes
                    role.menus.forEach(menu => {
                        const permissions = JSON.parse(menu.pivot.permissions) || [];
                        permissions.forEach(p => {
                            $(`#menu${p.charAt(0).toUpperCase() + p.slice(1)}${menu.id}`).prop('checked', true);
                        });
                    });
                    
                    $('#addRoleModal').modal('show');
                });
            });

            // Reset form when opening "Add New Role"
            $('.add-new-role').on('click', function() {
                $('#modalRoleName').val('');
                $('#addRoleForm').attr('action', '{{ route("roles.store") }}');
                $('input[name="_method"]').remove();
                $('.role-title').text('Add New Role');
                checkboxList.forEach(e => e.checked = false);
            });
        });
    </script>
@endsection