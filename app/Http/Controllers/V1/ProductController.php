<?php

namespace App\Http\Controllers\V1;

use App\Repositories\V1\ProductRepository;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Utilities\ResponseHandler;

class ProductController extends Controller
{
    protected ProductRepository $productRepository;

    public function __construct(ProductRepository $productRepository, Request $request)
    {
        parent::__construct($request);
        $this->productRepository = $productRepository;
    }

    /**
     * GET /api/products
     */
    public function index(Request $request)
    {
        $rules = [
    'filters' => 'sometimes|array',

    'filters.name'       => 'sometimes|string',
    'filters.vendor'     => 'sometimes|string',
    'filters.currency_id'=> 'sometimes|string',
    'filters.price'      => 'sometimes|numeric',
    'filters.available'  => 'sometimes|boolean',
    'filters.category'   => 'sometimes|string',

    'order_by' => 'sometimes|in:id,name,vendor,price,currency_id,available,old_price',
    'order'    => 'sometimes|in:asc,desc',

    'rpp' => 'sometimes|integer|min:1',
    'page'=> 'sometimes|integer|min:1',
];


        $validated = $this->validated($rules, $request->all());
        if ($validated->fails()) {
            return ResponseHandler::error(__('common.errors.validation'), 422, 2001, $validated->errors());
        }

        return $this->productRepository->productListing($request);
    }

    /**
 * GET /api/products/{id}
 */
public function show($id)
{
    return $this->productRepository->showProduct($id);
}

}
