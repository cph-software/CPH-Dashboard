@extends('layouts.admin')

@section('page-style')
    <style>
        .ck-editor__editable_inline {
            min-height: 200px;
        }
    </style>
@endsection

@section('title', 'Edit Failure Code')

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0"><span class="text-muted fw-light">Master / Tyre Failure Codes /</span> Edit
                Failure Code</h4>
            <a href="{{ route('tyre-failure-codes.index') }}" class="btn btn-secondary">
                <i class="ri-arrow-left-line me-1"></i> Back
            </a>
        </div>

        <div class="card">
            <div class="card-body">
                <form action="{{ route('tyre-failure-codes.update', $failureCode->id) }}" method="POST"
                    enctype="multipart/form-data" id="editFailureCodeForm">
                    @csrf
                    @method('PUT')
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="failure_code" class="form-label">Failure Code (Standard)</label>
                            <input type="text" id="failure_code" name="failure_code" class="form-control"
                                value="{{ $failureCode->failure_code }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="failure_name" class="form-label">Failure Name (Standard)</label>
                            <input type="text" id="failure_name" name="failure_name" class="form-control"
                                value="{{ $failureCode->failure_name }}" required>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-12 mb-3">
                            <label for="display_name" class="form-label">Display Name / Alias (Custom Name per Site)</label>
                            <input type="text" id="display_name" name="display_name" class="form-control"
                                value="{{ $failureCode->display_name }}" placeholder="e.g. Luka Samping (Site ABC)">
                            <div class="form-text">Jika diisi, nama ini yang akan muncul di dashboard dan laporan.</div>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="image_1" class="form-label">Image 1</label>
                            <input type="file" id="image_1" name="image_1" class="form-control"
                                onchange="previewImage(this, 'preview_edit_img1')">
                            <div class="mt-2 text-center" style="display: {{ $failureCode->image_1 ? 'block' : 'none' }};">
                                <img src="{{ $failureCode->image_1 ? asset('storage/' . $failureCode->image_1) : '' }}"
                                    id="preview_edit_img1" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="image_2" class="form-label">Image 2</label>
                            <input type="file" id="image_2" name="image_2" class="form-control"
                                onchange="previewImage(this, 'preview_edit_img2')">
                            <div class="mt-2 text-center" style="display: {{ $failureCode->image_2 ? 'block' : 'none' }};">
                                <img src="{{ $failureCode->image_2 ? asset('storage/' . $failureCode->image_2) : '' }}"
                                    id="preview_edit_img2" class="img-fluid rounded" style="max-height: 300px;">
                            </div>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label for="default_category" class="form-label">Category</label>
                            <select name="default_category" class="form-select" required>
                                <option value="Scrap" {{ $failureCode->default_category == 'Scrap' ? 'selected' : '' }}>Scrap
                                </option>
                                <option value="Repair" {{ $failureCode->default_category == 'Repair' ? 'selected' : '' }}>
                                    Repair</option>
                                <option value="Claim" {{ $failureCode->default_category == 'Claim' ? 'selected' : '' }}>Claim
                                </option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="status" class="form-label">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="Active" {{ $failureCode->status == 'Active' ? 'selected' : '' }}>Active
                                </option>
                                <option value="Inactive" {{ $failureCode->status == 'Inactive' ? 'selected' : '' }}>Inactive
                                </option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-4">
                            <label for="description" class="form-label">Description</label>
                            <textarea id="description-editor"
                                name="description">{!! $failureCode->description !!}</textarea>
                        </div>
                        <div class="col-md-6 mb-4">
                            <label for="recommendations" class="form-label">Recommendations</label>
                            <textarea id="recommendations-editor"
                                name="recommendations">{!! $failureCode->recommendations !!}</textarea>
                        </div>
                    </div>
                    <div class="mt-4 text-end">
                        <button type="submit" class="btn btn-primary">Update Failure Code</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('vendor-script')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
@endsection

@section('page-script')
    <script>
        function previewImage(input, previewId) {
            const preview = document.getElementById(previewId);
            const container = preview.parentElement;

            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    preview.src = e.target.result;
                    container.style.display = 'block';
                }
                reader.readAsDataURL(input.files[0]);
            } else {
                // Keep existing image if present, otherwise hide
                const existingSrc = preview.getAttribute('src');
                if (existingSrc && existingSrc !== window.location.href) { // avoid empty src pointing to current page
                    // Do nothing, keep preview
                } else {
                    container.style.display = 'none';
                    preview.src = '';
                }
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            ClassicEditor
                .create(document.querySelector('#description-editor'))
                .catch(error => { console.error(error); });

            ClassicEditor
                .create(document.querySelector('#recommendations-editor'))
                .catch(error => { console.error(error); });
        });
    </script>
@endsection