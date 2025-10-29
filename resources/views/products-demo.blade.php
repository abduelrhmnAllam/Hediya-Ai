<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="verify-admitad" content="a6dab9fb35" />
    <title>الفئات والمنتجات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
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
<body class="bg-light">

<div class="container py-4">
    <h2 class="text-center mb-4">🛍️ تصفح الفئات والمنتجات</h2>

    <div class="accordion" id="categoriesAccordion">
        @foreach ($categories as $category)
            @include('partials.category-node', ['category' => $category, 'level' => 0])
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

        // ✅ خليه مفتوح من البداية
        collapse.classList.add('show');
        button.classList.remove('collapsed');
        button.setAttribute('aria-expanded', 'true');

        // ✅ حمّل المنتجات مباشرة
        container.innerHTML = '<p class="text-info">⏳ جاري تحميل الفئات والمنتجات...</p>';

        try {
            const response = await fetch(`/categories/${categoryId}/tree`);
            if (!response.ok) {
                container.innerHTML = '<p class="text-danger">⚠️ حدث خطأ أثناء تحميل البيانات.</p>';
                continue;
            }

            const data = await response.json();
            const subcategories = data.category.children || [];
            const products = data.category.products || [];

            let html = '';

            // ✅ الفئات الفرعية
            if (subcategories.length > 0) {
                html += '<div class="ms-3">';
                subcategories.forEach(sub => {
                    html += `
                        <div class="border rounded p-2 mb-2 bg-white shadow-sm">
                            <strong>${sub.name}</strong>
                            <div id="sub-${sub.id}" class="ms-3 text-muted">اضغط لعرض التفاصيل...</div>
                        </div>
                    `;
                });
                html += '</div>';
            }

            // ✅ المنتجات في سلايدر
            if (products.length > 0) {
                html += `
                    <hr>
                    <h6 class="mb-3">🛒 المنتجات:</h6>
                    <div class="products-slider">
                `;

                products.forEach(p => {
                    let images = [];
                    try {
                        if (typeof p.pictures === 'string') {
                            images = JSON.parse(p.pictures);
                            if (typeof images === 'string') images = JSON.parse(images);
                        } else if (Array.isArray(p.pictures)) {
                            images = p.pictures;
                        }
                    } catch (e) {
                        console.error('خطأ أثناء فك الصور:', e);
                    }

                    html += `
                        <div class="product-card card shadow-sm">
                            ${images && images.length > 0
                                ? `<img src="${images[0].replace(/\\\//g, '/')}" class="card-img-top" alt="${p.name}" style="height:150px;object-fit:cover;">`
                                : `<div class="bg-secondary text-white text-center p-5">No Image</div>`}
                            <div class="card-body text-center">
                                <h6 class="card-title">${p.name}</h6>
                               <p class="text-success fw-bold mb-2">
                                  ${Number(String(p.price).replace(/,/g, '')).toFixed(2)} ${p.currency_id}
                                </p>

                                <div class="d-flex justify-content-center gap-2">
                                    <a href="${p.url}" target="_blank" class="btn btn-sm btn-buy">🛒 اشتري الآن</a>
                                    <a href="/products/${p.id}" class="btn btn-sm btn-details">🔍 تفاصيل</a>
                                </div>
                            </div>
                        </div>
                    `;
                });

                html += '</div>';
            }

            // ✅ لو مفيش فئات ولا منتجات
            if (!subcategories.length && !products.length) {
                html = '<p class="text-muted">لا توجد فئات فرعية أو منتجات.</p>';
            }

            container.innerHTML = html;
            container.dataset.loaded = true;
        } catch (error) {
            console.error('❌ خطأ أثناء تحميل البيانات:', error);
            container.innerHTML = '<p class="text-danger">⚠️ حدث خطأ أثناء تحميل البيانات.</p>';
        }
    }
});
</script>



</body>
</html>
