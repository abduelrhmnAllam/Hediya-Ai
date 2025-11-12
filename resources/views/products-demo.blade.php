<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>الفئات والمنتجات</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
            font-family: "Tajawal", sans-serif;
        }
        .accordion-button {
            background-color: #fff;
            font-weight: 600;
        }
        .accordion-button:not(.collapsed) {
            background-color: #e9f5ff;
            color: #0d6efd;
        }
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
        .card-title {
            font-size: 14px;
            min-height: 38px;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h2 class="text-center mb-5">🛍️ تصفح الفئات والمنتجات</h2>

    <div class="accordion" id="categoriesAccordion">
        @foreach ($categories as $category)
            <div class="accordion-item mb-3 shadow-sm">
                <h2 class="accordion-header" id="heading-{{ $category->id }}">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                            data-bs-target="#collapse-{{ $category->id }}" aria-expanded="false"
                            aria-controls="collapse-{{ $category->id }}" data-category-id="{{ $category->id }}">
                        {{ $category->name }}
                    </button>
                </h2>
                <div id="collapse-{{ $category->id }}" class="accordion-collapse collapse"
                     aria-labelledby="heading-{{ $category->id }}" data-bs-parent="#categoriesAccordion">
                    <div class="accordion-body" id="products-container-{{ $category->id }}">
                        <p class="text-info">⏳ جاري تحميل البيانات...</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>


    <script>
document.addEventListener('DOMContentLoaded', () => {
    const accordionButtons = document.querySelectorAll('.accordion-button');

    accordionButtons.forEach(button => {
        button.addEventListener('click', async () => {
            const categoryId = button.dataset.categoryId;
            const container = document.getElementById(`products-container-${categoryId}`);

            // 🧠 لو اتحمل قبل كده، ما يعيدش التحميل
            if (!categoryId || !container || container.dataset.loaded) return;

            container.innerHTML = '<p class="text-info">⏳ جاري تحميل الفئات والمنتجات...</p>';

            try {
                const response = await fetch(`/categories/${categoryId}/tree`);
                if (!response.ok) throw new Error("Response not OK");

                const data = await response.json();
                const subcategories = data.category.children || [];
                const feeds = data.category.feeds || {}; // ← المنتجات مجمعة حسب الـ Feed

                let html = '';

                // 📂 الفئات الفرعية
                if (subcategories.length > 0) {
                    html += `<h6 class="mb-2">📂 الفئات الفرعية:</h6><ul>`;
                    subcategories.forEach(sub => {
                        html += `<li>${sub.name}</li>`;
                    });
                    html += `</ul><hr>`;
                }

                // 🛍️ المنتجات حسب كل Feed
                if (Object.keys(feeds).length > 0) {
                    for (const [feedName, products] of Object.entries(feeds)) {
                        html += `
                            <h5 class="mt-4 mb-3 text-primary fw-bold">
                                🛒 ${feedName}
                            </h5>
                            <div class="products-slider">
                        `;

                        products.forEach(p => {
                            // ✅ الصورة
                            const imageUrl = Array.isArray(p.pictures) && p.pictures.length > 0 ? p.pictures[0] : '';

                            // ✅ السعر (مع معالجة الفواصل)
                            let priceValue = p.price ?? 0;
                            if (typeof priceValue === "string") {
                                priceValue = priceValue.replace(/,/g, ''); // إزالة الفواصل
                            }
                            const price = parseFloat(priceValue) || 0;

                            // ✅ العملة و SKU
                            const currency = p.currency_id ?? p.currency ?? 'EGP';
                            const sku = p.sku ?? 'غير متوفر';

                            // ✅ بناء الكارت
                            html += `
                                <div class="product-card card shadow-sm">
                                    ${imageUrl
                                        ? `<img src="${imageUrl}" class="card-img-top" alt="${p.name}" style="height:150px;object-fit:cover;">`
                                        : `<div class="bg-secondary text-white text-center p-5">لا توجد صورة</div>`}
                                    <div class="card-body text-center">
                                        <h6 class="card-title">${p.name}</h6>
                                        <p class="text-success fw-bold mb-2">
                                            ${price.toLocaleString(undefined, {minimumFractionDigits: 2, maximumFractionDigits: 2})} ${currency}
                                        </p>
                                        <small class="text-muted d-block mb-2">SKU: ${sku}</small>
                                        <div class="d-flex justify-content-center gap-2">
                                            <a href="${p.url ?? '#'}" target="_blank" class="btn btn-sm btn-buy">🛒 اشتري الآن</a>
                                            <a href="/products/${p.id}" class="btn btn-sm btn-details">🔍 تفاصيل</a>
                                        </div>
                                    </div>
                                </div>
                            `;
                        });

                        html += `</div><hr>`;
                    }
                } else {
                    html += '<p class="text-muted">لا توجد منتجات متاحة في هذه الفئة.</p>';
                }

                // ✅ عرض المحتوى
                container.innerHTML = html;
                container.dataset.loaded = true;

            } catch (error) {
                console.error('❌ خطأ أثناء تحميل البيانات:', error);
                container.innerHTML = '<p class="text-danger">⚠️ حدث خطأ أثناء تحميل البيانات.</p>';
            }
        });
    });
});
</script>


</body>
</html>
