<?php

namespace App\Repositories\V1;

use App\Models\Product;
use App\Utilities\ResponseHandler;
use App\Utilities\FilterHelper;
use Illuminate\Http\Request;

class ProductRepository extends BaseRepository
{
    protected string $logChannel;

    public function __construct(Request $request, Product $product)
    {
        parent::__construct($product);
        $this->logChannel = 'products_logs';
    }

    /**
     * 📦 عرض قائمة المنتجات مع كل التفاصيل
     */
    public function productListing($request)
    {
        try {
            // 🧠 نجلب كل الأعمدة + الفئة المرتبطة
            $query = $this->model::with(['category:id,name,slug,parent_id'])
                ->select([
                    'id',
                    'external_id',
                    'available',
                    'category_id',
                    'currency_id',
                    'name',
                    'description',
                    'price',
                    'old_price',
                    'vendor',
                    'url',
                    'pictures',
                    'identifier_exists',
                    'modified_time',
                    'created_at',
                    'updated_at',
                ]);

            // الأعمدة المسموح بها للتصفية
            $allowedColumns = [
                'name',
                'vendor',
                'currency_id',
                'price',
                'available',
            ];

            // 🔍 تطبيق الفلاتر
            $filters = $request->input('filters', []);
            if (!empty($filters)) {
                $query = FilterHelper::applyFilters($query, $filters, $allowedColumns);

                // تصفية حسب الفئة بالاسم (اختياري)
                if (isset($filters['category'])) {
                    $query->whereHas('category', function ($q) use ($filters) {
                        $q->where('name', 'like', '%' . $filters['category'] . '%');
                    });
                }
            }

            // ترتيب النتائج
            $orderBy = $request->input('order_by', 'id');
            $order   = $request->input('order', 'desc');
            if ($orderBy && in_array($orderBy, array_merge($allowedColumns, ['id', 'old_price']))) {
                $query->orderBy($orderBy, $order);
            }

            // عدد النتائج في الصفحة (pagination)
            $rpp = $request->input('rpp', 10);
            $products = $query->paginate($rpp);

            return ResponseHandler::success($products, __('common.success'));
        } catch (\Exception $e) {
            $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
            return ResponseHandler::error($this->prepareExceptionLog($e), 500, 24);
        }
    }

    /**
 * 📄 عرض تفاصيل منتج واحد
 */
public function showProduct($id)
{
    try {
        $product = $this->model::with(['category:id,name,slug,parent_id'])
            ->select([
                'id',
                'external_id',
                'available',
                'category_id',
                'currency_id',
                'name',
                'description',
                'price',
                'old_price',
                'vendor',
                'url',
                'pictures',
                'identifier_exists',
                'modified_time',
                'created_at',
                'updated_at',
            ])
            ->find($id);

        if (!$product) {
            return ResponseHandler::error(__('common.errors.not_found'), 404, 4040);
        }

        return ResponseHandler::success($product, __('common.success'));
    } catch (\Exception $e) {
        $this->logData($this->logChannel, $this->prepareExceptionLog($e), 'error');
        return ResponseHandler::error($this->prepareExceptionLog($e), 500, 25);
    }
}


}
