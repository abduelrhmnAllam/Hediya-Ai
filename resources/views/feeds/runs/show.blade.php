@extends('layouts.app')

@section('content')
<div class="max-w-5xl mx-auto py-14 space-y-10">

    <div>
        <h1 class="text-3xl font-bold">Feed Run #{{ $run->id }}</h1>
        <p class="text-gray-500 mt-1 text-sm">
            {{ $run->created_at->format('Y-m-d H:i') }}
        </p>
    </div>

    <div class="bg-white shadow rounded-xl p-6 space-y-1 text-sm">
        <div><b>File:</b> {{ $run->file_name }}</div>
        <div><b>Feed Code:</b> {{ $run->feed->code }}</div>
        <div><b>Country:</b> {{ $run->feed->country_code }}</div>
        <div><b>Currency:</b> {{ $run->feed->default_currency }}</div>
        <div><b>Total Offers Imported:</b> {{ $total_products }}</div>
    </div>

    <div>
        <h2 class="text-xl font-semibold mb-3">Brands</h2>
        <div class="bg-white shadow rounded-xl p-6 space-y-2 text-sm">
            @foreach($brands as $b)
                <div class="flex justify-between">
                    <span>{{ $b->brand_name ?: '— No brand —' }}</span>
                    <span class="font-mono">{{ $b->cnt }}</span>
                </div>
            @endforeach
        </div>
    </div>

   <div>
    <h2 class="text-xl font-semibold mb-3">Categories</h2>
    <div class="bg-white shadow rounded-xl p-6 space-y-2 text-sm">
        @foreach($categories as $c)
            <div class="flex justify-between">
                <span>{{ $c->cat_name }}</span>
                <span class="font-mono">{{ $c->cnt }}</span>
            </div>
        @endforeach
    </div>
</div>


</div>
@endsection
