<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

 <meta name="verify-admitad" content="a6dab9fb35" />
    <title>{{ $product->name }} - تفاصيل المنتج</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Tajawal", sans-serif;
        }
        .product-image {
            width: 100%;
            height: 320px;
            object-fit: cover;
            border-radius: 12px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        .badge-custom {
            font-size: 0.9rem;
            padding: 6px 10px;
            margin-left: 5px;
        }
        .thumbs {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }
        .thumbs img {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            border: 2px solid transparent;
        }
        .thumbs img:hover {
            border-color: #0d6efd;
        }
        .price-old {
            color: #dc3545;
            text-decoration: line-through;
            font-size: 1.1rem;
        }
        .price-new {
            color: #198754;
            font-weight: bold;
            font-size: 1.4rem;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <a href="/" class="btn btn-secondary mb-4">← رجوع إلى قائمة المنتجات</a>

    <div class="row">
        {{-- 🔹 عرض الصور --}}
        <div class="col-md-6">
            @php
                $mainImage = $mainImage ?? $product->images->sortBy('sort_order')->first()?->url;
                $otherImages = $otherImages ?? $product->images->sortBy('sort_order')->pluck('url')->toArray();
            @endphp

            @if($mainImage)
                <img src="{{ $mainImage }}" id="mainImage" class="product-image shadow" alt="{{ $product->name }}">
                @if(count($otherImages) > 1)
                    <div class="thumbs">
                        @foreach($otherImages as $img)
                            @if($img !== $mainImage)
                                <img src="{{ $img }}" onclick="document.getElementById('mainImage').src='{{ $img }}'" alt="صورة إضافية">
                            @endif
                        @endforeach
                    </div>
                @endif
            @else
                <div class="bg-secondary text-white text-center p-5 rounded">
                    لا توجد صورة متاحة
                </div>
            @endif
        </div>

        {{-- 🔹 عرض تفاصيل المنتج --}}
        <div class="col-md-6">
            <h2 class="mb-3">{{ $product->name }}</h2>
            @if($product->original_name)
                <h6 class="text-muted mb-3">{{ $product->original_name }}</h6>
            @endif

          {{-- ✅ السعر --}}
@if($oldPrice && $oldPrice > $price)
    <p class="price-old mb-1">
        {{ number_format($oldPrice, 2) }} {{ $currency }}
    </p>
@endif

<p class="price-new mb-2">
    {{ number_format($price, 2) }} {{ $currency }}
</p>


            <p class="text-muted">رمز المنتج (SKU): <strong>{{ $sku }}</strong></p>

            <p class="mb-3 text-secondary">
                {{ $product->short_description ?? 'لا يوجد وصف مختصر.' }}
            </p>

            @if($product->long_description)
                <div class="mb-4">
                    <h5>الوصف الكامل:</h5>
                    <p>{{ $product->long_description }}</p>
                </div>
            @endif

            @if($product->brand)
                <p><strong>العلامة التجارية:</strong> {{ $product->brand->name }}</p>
            @endif

            @if($product->categories && count($product->categories) > 0)
                <p>
                    <strong>الفئة:</strong>
                    @foreach($product->categories as $cat)
                        <span class="badge bg-primary badge-custom">{{ $cat->name }}</span>
                    @endforeach
                </p>
            @endif

            @if($product->colors && count($product->colors) > 0)
                <p>
                    <strong>الألوان المتاحة:</strong>
                    @foreach($product->colors as $color)
                        <span class="badge bg-light text-dark border badge-custom">{{ $color->color }}</span>
                    @endforeach
                </p>
            @endif

            @if($product->sizes && count($product->sizes) > 0)
                <p>
                    <strong>المقاسات المتاحة:</strong>
                    @foreach($product->sizes as $size)
                        <span class="badge bg-info text-dark badge-custom">{{ $size->size }}</span>
                    @endforeach
                </p>
            @endif

            @if($url && $url !== '#')
                <a href="{{ $url }}" target="_blank" class="btn btn-success mt-3">
                    🛒 شراء المنتج
                </a>
            @endif
        </div>
    </div>
</div>

</body>
</html>
