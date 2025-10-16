<div class="accordion-item">
    <h2 class="accordion-header" id="heading{{ $category->id }}">
        <button class="accordion-button collapsed {{ $level > 0 ? 'sub-category' : '' }}"
                type="button" data-bs-toggle="collapse"
                data-bs-target="#collapse{{ $category->id }}"
                aria-expanded="false"
                aria-controls="collapse{{ $category->id }}"
                data-category-id="{{ $category->id }}">
            {{ str_repeat('⎯ ', $level) }} {{ $category->name }}
        </button>
    </h2>

    <div id="collapse{{ $category->id }}" class="accordion-collapse collapse"
         aria-labelledby="heading{{ $category->id }}">
        <div class="accordion-body" id="products-container-{{ $category->id }}">
            <p class="text-muted">اضغط لعرض الفئات الفرعية والمنتجات...</p>
        </div>

        @if($category->children && $category->children->count())
            <div class="accordion ms-3 mt-2">
                @foreach ($category->children as $child)
                    @include('partials.category-node', ['category' => $child, 'level' => $level + 1])
                @endforeach
            </div>
        @endif
    </div>
</div>
