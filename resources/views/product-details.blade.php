<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>{{ $product->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container py-4">
    <a href="/products-demo" class="btn btn-secondary mb-3">⬅️ رجوع</a>

    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="card-title mb-3">{{ $product->name }}</h3>

            @php
                $images = is_string($product->pictures)
                    ? json_decode($product->pictures, true)
                    : $product->pictures;
            @endphp

            @if(!empty($images))
                <div id="carousel{{ $product->id }}" class="carousel slide mb-4" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($images as $index => $img)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ $img }}" class="d-block w-100" style="max-height:400px;object-fit:cover;">
                            </div>
                        @endforeach
                    </div>
                    @if(count($images) > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#carousel{{ $product->id }}" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon"></span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#carousel{{ $product->id }}" data-bs-slide="next">
                            <span class="carousel-control-next-icon"></span>
                        </button>
                    @endif
                </div>
            @endif

            <p class="fw-bold fs-5 text-success">{{ $product->price }} {{ $product->currency_id }}</p>

            @if($product->old_price)
                <p class="text-danger text-decoration-line-through">{{ $product->old_price }} {{ $product->currency_id }}</p>
            @endif

            @if($product->category)
                <p><strong>الفئة:</strong> {{ $product->category->name }}</p>
            @endif

            <p><strong>الوصف:</strong></p>
            <p class="text-muted">{{ $product->description }}</p>

            <div class="mt-4 d-flex justify-content-between">
                <a href="{{ $product->url }}" target="_blank" class="btn btn-outline-success w-50 me-2">
                   الموقع الأصلي
                </a>
                <a href="/products-demo" class="btn btn-outline-secondary w-50">عودة للقائمة</a>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
