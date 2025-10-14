<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>منتجات المتجر التجريبية</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <h2 class="text-center mb-4">🛍️ قائمة المنتجات التجريبية</h2>

    <div class="row g-4">
        @foreach ($products as $product)
            <div class="col-md-4">
                <div class="card shadow-sm h-100">
               @php

    // نفك JSON مرتين لو محتاج
    $images = $product->pictures;

    if (is_string($images)) {
        $decodedOnce = json_decode($images, true);
        $images = is_string($decodedOnce) ? json_decode($decodedOnce, true) : $decodedOnce;
    }
@endphp

@if(!empty($images) && is_array($images))
    <div id="carousel{{ $product->id }}" class="carousel slide" data-bs-ride="carousel">
        <div class="carousel-inner">
            @foreach ($images as $index => $img)
                <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                    <img src="{{ str_replace('\\/', '/', $img) }}" class="d-block w-50" alt="{{ $product->name }}" style="height: 250px; object-fit: cover;">
                </div>
            @endforeach
        </div>

        @if(count($images) > 1)
            <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $product->id }}" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $product->id }}" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        @endif
    </div>
@endif

                    <div class="card-body">
                        <h5 class="card-title">{{ $product->name }}</h5>
                        <p class="text-muted small mb-2">{{ $product->vendor }} | {{ $product->currency_id }}</p>
                        <p class="card-text">{{ Str::limit($product->description, 100) }}</p>
                        <p class="fw-bold text-success">{{ $product->price }} {{ $product->currency_id }}</p>
                        @if($product->old_price)
                            <p class="text-danger text-decoration-line-through">{{ $product->old_price }} {{ $product->currency_id }}</p>
                        @endif
                        @if($product->category)
                            <span class="badge bg-primary">{{ $product->category->name }}</span>
                        @endif
                    </div>
                              <div class="mt-3 d-flex justify-content-between">
    <!-- زرار عرض التفاصيل -->
    <a href="{{ route('product.show', $product->id) }}" class="btn btn-sm btn-outline-primary w-50 me-1">
        عرض التفاصيل
    </a>


</div>
                </div>
            </div>

        @endforeach
    </div>


</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
