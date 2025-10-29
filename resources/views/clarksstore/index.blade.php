<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الفئات والمنتجات | Clarksstore</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f8f9fa; }
        .product-card {
            width: 220px;
            flex: 0 0 auto;
            margin-left: 10px;
            margin-right: 10px;
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
    </style>
</head>
<body>

<div class="container py-4">
    <h2 class="text-center mb-4">🛍️ تصفح فئات Clarks ومنتجاتها</h2>

    <div class="accordion" id="categoriesAccordion">
        @foreach ($categories as $category)
            <div class="accordion-item mb-2">
                <h2 class="accordion-header" id="heading-{{ $category->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $category->id }}" data-category-id="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                </h2>
                <div id="collapse-{{ $category->id }}" class="accordion-collapse collapse"
                     aria-labelledby="heading-{{ $category->id }}" data-bs-parent="#categoriesAccordion">
                    <div class="accordion-body" id="products-container-{{ $category->id }}">
                        ⏳ جاري تحميل البيانات...
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', async () => {
    const accordionItems = document.querySelectorAll('.accordion-item');

    for (const item of accordionItems) {
        const button = item.querySelector('.accordion-button');
        const collapse = item.querySelector('.accordion-collapse');
        const categoryId = button?.dataset.categoryId;
        const container = document.getElementById('products-container-' + categoryId);

        if (!categoryId || !container) continue;

        // نفتح أول فئة كمثال (اختياري)
        if (parseInt(categoryId) === 1) {
            collapse.classList.add('show');
            button.classList.remove('collapsed');
            button.setAttribute('aria-expanded', 'true');
        }

        // رسالة تحميل مؤقتة
        container.innerHTML = '<p class="text-info">⏳ جاري تحميل الفئات والمنتجات...</p>';

        try {
            // ✅ نستخدم الآن المسار الجديد داخل prefix "clarksstore"
            const response = await fetch(`{{ url('clarksstore/categories') }}/${categoryId}/tree`);

            if (!response.ok) {
                container.innerHTML = '<p class="text-danger">⚠️ خطأ في تحميل البيانات.</p>';
                continue;
            }

            const data = await response.json();
            const products = data.category.products || [];

            let html = '';

            if (products.length > 0) {
                html += `
                    <hr>
                    <h6 class="mb-3">🛒 المنتجات:</h6>
                    <div class="products-slider">
                `;

            products.forEach(p => {
    let img = null;

    // ✅ معالجة الصور
    if (p.pictures && p.pictures.length > 0) {
        img = p.pictures[0];
        if (typeof img === 'string') {
            img = img.replace(/\\/g, '/').trim();
            img = encodeURI(img);
        }
    }

    html += `
        <div class="product-card card shadow-sm">
            ${img
                ? `<img src="${img}" class="card-img-top" alt="${p.name}" style="height:180px;object-fit:cover;border-bottom:1px solid #eee;">`
                : `<div class="bg-secondary text-white text-center p-5">No Image</div>`}
            <div class="card-body text-center">
                <h6 class="card-title">${p.name}</h6>
                <p class="text-success fw-bold mb-2">
                    ${Number(p.price).toFixed(2)} <span class="text-dark small">AED</span>
                </p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="${p.url}" target="_blank" class="btn btn-sm btn-buy">🛒 Buy Now</a>
                </div>
            </div>
        </div>
    `;
});

                html += '</div>';
            } else {
                html = '<p class="text-muted">لا توجد منتجات في هذه الفئة.</p>';
            }

            container.innerHTML = html;
        } catch (error) {
            console.error('❌ خطأ أثناء تحميل البيانات:', error);
            container.innerHTML = '<p class="text-danger">⚠️ حدث خطأ أثناء تحميل البيانات.</p>';
        }
    }
});
</script>


</body>
</html>
