@extends('layouts.app')

@section('content')
<div class="container my-4">
    <div class="row">
        {{-- الصور --}}
        <div class="col-md-6">
            <div class="d-flex flex-column align-items-center">
                {{-- الصورة الرئيسية --}}
                <div class="border mb-3 p-2" style="width: 100%; max-height: 450px; overflow: hidden;">
                    <img id="mainImage" src="{{ $product->image_url }}" class="img-fluid w-100" alt="{{ $product->name }}">
                </div>

                {{-- الصور المصغرة --}}
                @if($product->image_urls && count($product->image_urls) > 1)
                    <div class="d-flex flex-wrap justify-content-center gap-2">
                        @foreach($product->image_urls as $img)
                            <img src="{{ $img }}"
                                 class="img-thumbnail"
                                 style="width: 80px; height: 80px; object-fit: contain; cursor: pointer;"
                                 onclick="document.getElementById('mainImage').src='{{ $img }}'">
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- تفاصيل المنتج --}}
        <div class="col-md-6">
            <h2>{{ $product->name }}</h2>
            <p><strong>Brand:</strong> {{ $product->brand }}</p>

            {{-- السعر --}}
            <p class="fs-4">
                <span class="text-danger fw-bold">${{ $product->sale_price ?? $product->price }}</span>
                @if($product->sale_price)
                    <del class="text-muted fs-6">${{ $product->price }}</del>
                @endif
            </p>

            {{-- التقييم --}}
            @if(!empty($product->product_rating['value']))
                <p>⭐ {{ $product->product_rating['value'] }} / 5 ({{ $product->product_rating['count'] }} reviews)</p>
            @endif

            {{-- المواصفات --}}
            @if($product->plp_specifications)
                <h5 class="mt-4">Specifications</h5>
                <ul>
                    @foreach($product->plp_specifications as $key => $value)
                        <li><strong>{{ $key }}:</strong> {{ $value }}</li>
                    @endforeach
                </ul>
            @endif

            {{-- الأزرار --}}
            <div class="mt-4">
                <a href="{{ $product->url }}" target="_blank" class="btn btn-outline-secondary">View on Noon</a>
                <a href="{{ $product->affiliate_url }}" target="_blank" class="btn btn-primary">Buy Now</a>
            </div>
        </div>
    </div>
</div>
@endsection
