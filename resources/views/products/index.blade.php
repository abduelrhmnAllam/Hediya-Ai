@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="mb-4">All Products</h1>

    <div class="row">
        @foreach($products as $product)
            <div class="col-md-3 mb-4">
                <div class="card h-100">
                    {{-- صورة المنتج --}}
                    @if($product->image_url)
                        <a href="{{ route('products.show', $product) }}">
                            <img src="{{ $product->image_url }}" class="card-img-top" alt="{{ $product->name }}">
                        </a>
                    @endif

                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ Str::limit($product->name, 40) }}</h5>
                        <p class="text-muted mb-1"><strong>Brand:</strong> {{ $product->brand }}</p>

                        {{-- السعر --}}
                        <p class="mb-1">
                            <span class="text-danger fw-bold">${{ $product->sale_price ?? $product->price }}</span>
                            @if($product->sale_price)
                                <del class="text-muted">${{ $product->price }}</del>
                            @endif
                        </p>

                        {{-- التقييم --}}
                        @if(!empty($product->product_rating['value']))
                            <p class="mb-1">⭐ {{ $product->product_rating['value'] }} ({{ $product->product_rating['count'] }} reviews)</p>
                        @endif

                        <div class="mt-auto">
                            <a href="{{ route('products.show', $product) }}" class="btn btn-sm btn-outline-primary">View Details</a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center">
        {{ $products->links() }}
    </div>
</div>
@endsection
