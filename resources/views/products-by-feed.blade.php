<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">

 <meta name="verify-admitad" content="a6dab9fb35" />
    <title>المنتجات حسب المورد (Feed)</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Tajawal", sans-serif;
        }
        .feed-title {
            color: #0d6efd;
            margin-top: 30px;
            font-weight: 700;
        }
        .category-title {
            font-size: 18px;
            color: #333;
            margin-top: 20px;
            margin-bottom: 10px;
        }
        .product-card {
            width: 220px;
            flex: 0 0 auto;
            margin: 10px;
        }
        .products-slider {
            display: flex;
            overflow-x: auto;
            gap: 10px;
            scroll-behavior: smooth;
            padding-bottom: 10px;
        }
        .products-slider::-webkit-scrollbar {
            height: 6px;
        }
        .products-slider::-webkit-scrollbar-thumb {
            background-color: #aaa;
            border-radius: 10px;
        }
        .btn-buy {
            background-color: #198754;
            color: white;
        }
        .btn-details {
            background-color: #0d6efd;
            color: white;
        }
        .card-title {
            font-size: 14px;
            min-height: 40px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-5">🛍️ المنتجات حسب المورد (Feed)</h2>

    @forelse($feedsData as $feed)
        <div class="feed-section mb-5">
            <h3 class="feed-title">🛒 {{ $feed['feed_name'] }}</h3>

            @forelse($feed['categories'] as $category)
                <h5 class="category-title">📂 {{ $category['name'] }}</h5>

                @if(!empty($category['products']))
                    <div class="products-slider">
                        @foreach($category['products'] as $p)
                            @php
                                $imageUrl = $p['pictures'][0] ?? asset('images/no-image.png');
                                $currency = $p['currency'] ?? 'SAR';
                                $sku = $p['sku'] ?? 'غير متوفر';
                                $priceValue = is_string($p['price'] ?? null)
                                    ? floatval(str_replace(',', '', $p['price']))
                                    : ($p['price'] ?? 0);
                                $oldPriceValue = is_string($p['old_price'] ?? null)
                                    ? floatval(str_replace(',', '', $p['old_price']))
                                    : ($p['old_price'] ?? null);
                            @endphp

                            <div class="product-card card shadow-sm">
                                <img src="{{ $imageUrl }}" class="card-img-top"
                                     alt="{{ $p['name'] }}" style="height:150px;object-fit:cover;">

                                <div class="card-body text-center">
                                    <h6 class="card-title">{{ $p['name'] }}</h6>

                                    {{-- ✅ السعر والخصم --}}
                                    @if($oldPriceValue && $oldPriceValue > $priceValue)
                                        <p class="text-danger text-decoration-line-through mb-1">
                                            {{ number_format($oldPriceValue, 2) }} {{ $currency }}
                                        </p>
                                    @endif
                                    <p class="text-success fw-bold mb-2">
                                        {{ number_format($priceValue, 2) }} {{ $currency }}
                                    </p>

                                    <small class="text-muted d-block mb-2">SKU: {{ $sku }}</small>

                                    <div class="d-flex justify-content-center gap-2">
                                        <a href="{{ $p['url'] ?? '#' }}" target="_blank" class="btn btn-sm btn-buy">
                                            🛒 اشتري الآن
                                        </a>
                                        <a href="/products/{{ $p['id'] }}" class="btn btn-sm btn-details">
                                            🔍 تفاصيل
                                        </a>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">لا توجد منتجات لهذه الفئة.</p>
                @endif

            @empty
                <p class="text-muted">لا توجد فئات مرتبطة بهذا المورد.</p>
            @endforelse
        </div>
    @empty
        <p class="text-muted text-center">لا توجد بيانات Feeds متاحة.</p>
    @endforelse
</div>

</body>
</html>
