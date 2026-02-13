@extends('layouts.admin')

@section('title', 'Failure Code Detail - ' . $failureCode->failure_code)

@section('content')
    <div class="container-xxl flex-grow-1 container-p-y">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold py-3 mb-0">
                <span class="text-muted fw-light">Master / Failure Codes /</span> {{ $failureCode->failure_code }}
            </h4>
            <div class="d-flex gap-2">
                <a href="{{ route('tyre-failure-codes.index') }}" class="btn btn-outline-secondary">
                    <i class="icon-base ri ri-arrow-left-line me-1"></i> Back
                </a>
                <a href="{{ route('tyre-failure-codes.edit', $failureCode->id) }}" class="btn btn-primary">
                    <i class="icon-base ri ri-pencil-line me-1"></i> Edit
                </a>
            </div>
        </div>

        <div class="row">
            <!-- Guidebook Content -->
            <div class="col-lg-8">
                <div class="card mb-4 shadow-sm border-0">
                    <div class="card-header d-flex justify-content-between align-items-center bg-lighter">
                        <h5 class="mb-0 fw-bold"><i class="ri-book-read-line me-2"></i> FAILURE GUIDEBOOK</h5>
                        <span
                            class="badge bg-label-{{ $failureCode->default_category == 'Scrap' ? 'danger' : ($failureCode->default_category == 'Repair' ? 'warning' : 'primary') }} px-3 py-2">
                            CATEGORY: {{ strtoupper($failureCode->default_category) }}
                        </span>
                    </div>
                    <div class="card-body pt-4">
                        <div class="text-center mb-4 pb-2">
                            <h2 class="fw-bold text-primary mb-1">
                                @if($failureCode->display_name)
                                    {{ $failureCode->display_name }}
                                @else
                                    {{ $failureCode->failure_code }}
                                @endif
                            </h2>
                            <h4 class="text-secondary">
                                @if($failureCode->display_name)
                                    <span class="small text-muted">({{ $failureCode->failure_code }} -
                                        {{ $failureCode->failure_name }})</span>
                                @else
                                    {{ $failureCode->failure_name }}
                                @endif
                            </h4>
                        </div>

                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <div
                                    class="image-container rounded border p-2 bg-light text-center h-100 d-flex flex-column">
                                    <p class="small text-muted mb-2 fw-medium uppercase">VISUAL REFERENCE 1</p>
                                    @if($failureCode->image_1)
                                        <img src="{{ asset('storage/' . $failureCode->image_1) }}"
                                            class="img-fluid rounded shadow-sm flex-grow-1 object-fit-cover"
                                            style="max-height: 300px; min-height: 250px;" alt="Visual 1">
                                    @else
                                        <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted bg-dark text-white rounded"
                                            style="min-height: 250px;">
                                            <span>No Image Available</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div
                                    class="image-container rounded border p-2 bg-light text-center h-100 d-flex flex-column">
                                    <p class="small text-muted mb-2 fw-medium uppercase">VISUAL REFERENCE 2</p>
                                    @if($failureCode->image_2)
                                        <img src="{{ asset('storage/' . $failureCode->image_2) }}"
                                            class="img-fluid rounded shadow-sm flex-grow-1 object-fit-cover"
                                            style="max-height: 300px; min-height: 250px;" alt="Visual 2">
                                    @else
                                        <div class="flex-grow-1 d-flex align-items-center justify-content-center text-muted bg-dark text-white rounded"
                                            style="min-height: 250px;">
                                            <span>No Image Available</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="failure-description mb-4">
                            <h5 class="fw-bold border-bottom pb-2 mb-3"><i class="ri-information-line me-2"></i> Description
                                & Characteristics</h5>
                            <div class="p-3 bg-light rounded text-body lh-lg">
                                {!! nl2br(e($failureCode->description ?? 'No description provided.')) !!}
                            </div>
                        </div>

                        <div class="failure-recommendations">
                            <h5 class="fw-bold border-bottom pb-2 mb-3 text-warning"><i class="ri-lightbulb-line me-2"></i>
                                Maintenance Recommendations</h5>
                            <div class="p-3 bg-warning-subtle text-warning-emphasis border border-warning rounded lh-lg">
                                {!! nl2br(e($failureCode->recommendations ?? 'No recommendations provided.')) !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Status & Stats Info -->
            <div class="col-lg-4">
                <div class="card mb-4 border-primary shadow-sm">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">System Information</h5>
                        <ul class="list-unstyled mb-0">
                            <li class="d-flex mb-3">
                                <span class="fw-medium text-muted me-2">Status:</span>
                                <span
                                    class="badge bg-label-{{ $failureCode->status == 'Active' ? 'success' : 'secondary' }} ms-auto">
                                    {{ $failureCode->status }}
                                </span>
                            </li>
                            <li class="d-flex mb-3 border-top pt-3">
                                <span class="fw-medium text-muted me-2">Created At:</span>
                                <span class="ms-auto">{{ $failureCode->created_at->format('d M Y H:i') }}</span>
                            </li>
                            <li class="d-flex mb-0 border-top pt-3">
                                <span class="fw-medium text-muted me-2">Last Updated:</span>
                                <span class="ms-auto">{{ $failureCode->updated_at->format('d M Y H:i') }}</span>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Frequency & Usage -->
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <h5 class="card-title fw-bold mb-3">Historical Data</h5>
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm bg-label-primary rounded p-2 me-3">
                                <i class="ri-repeat-line font-size-20"></i>
                            </div>
                            <div>
                                <h6 class="mb-0">Total Occurrences</h6>
                                <small class="text-muted">{{ $failureCode->movements()->count() }} cases recorded</small>
                            </div>
                            <h5 class="ms-auto mb-0 fw-bold">{{ $failureCode->movements()->count() }}</h5>
                        </div>

                        @if($failureCode->movements()->count() > 0)
                            <div class="alert alert-info d-flex align-items-center" role="alert">
                                <i class="ri-information-line me-2"></i>
                                <div>
                                    This failure code is actively used in performance tracking.
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('page-style')
    <style>
        .image-container img {
            transition: transform 0.3s ease;
            cursor: zoom-in;
        }

        .image-container img:hover {
            transform: scale(1.02);
        }

        .bg-lighter {
            background-color: #f8f9fa;
        }

        .bg-warning-subtle {
            background-color: #fff9db !important;
        }

        .text-warning-emphasis {
            color: #664d03 !important;
        }
    </style>
@endsection