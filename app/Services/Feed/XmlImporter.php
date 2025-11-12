<?php

namespace App\Services\Feed;

use App\Models\{Feed, FeedRun, Brand, Category, Product, ProductImage, ProductColor, ProductSize, FeedProduct};
use App\Support\ProductFingerprint;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use XMLReader;
use DateTimeImmutable;

class XmlImporter
{
    protected function ensureXmlFile(string $filePath): string
{
    $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

    // case 1: xml direct
    if (in_array($ext, ['xml','yml'])) {
        return $filePath;
    }

    // case 2: gz compressed → extract to tmp xml
    if ($ext === 'gz') {
        $gz = gzopen($filePath, 'rb');
        if (!$gz) throw new \RuntimeException("Cannot open GZ file");

        $tmp = storage_path('app/feeds/tmp_'.uniqid().'.xml');
        $out = fopen($tmp, 'wb');

        while (!gzeof($gz)) {
            fwrite($out, gzread($gz, 4096));
        }

        gzclose($gz);
        fclose($out);

        return $tmp;
    }

    throw new \RuntimeException("Unsupported file extension: ".$ext);
}

 public function import(string $filePath, Feed $feed): FeedRun
{
   // resolve actual file path (UI upload OR CLI)
if (str_starts_with($filePath,'/') || str_starts_with($filePath,'C:\\')) {
    // absolute path passed (controller gave full path)
    $xmlPath = $filePath;
} elseif (str_starts_with($filePath,'tmp_feeds/')) {
    // uploaded via web tmp_feeds/
    $xmlPath = public_path($filePath);
} else {
    // relative storage path
    $xmlPath = storage_path('app/'.$filePath);
}

// file must exist physically here
if (!file_exists($xmlPath)) {
    throw new \RuntimeException("XML file missing: ".$xmlPath);
}


    // handle .gz etc
    $xmlPath = $this->ensureXmlFile($xmlPath);

    $fileHash = @hash_file('sha1', $xmlPath) ?: null;

    $run = FeedRun::create([
        'feed_id'    => $feed->id,
        'file_name'  => basename($filePath),
        'file_hash'  => $fileHash,
        'meta'       => ['source'=>'xml','path'=>$xmlPath],
        'imported_at'=> now(),
    ]);

    $xr = new XMLReader();
    if (!$xr->open($xmlPath, null, LIBXML_NOWARNING | LIBXML_NOERROR)) {
        throw new \RuntimeException("Cannot open XML: ".$xmlPath);
    }

    // 1) Parse categories
    $this->parseCategories($xr, $feed);

    // 2) Parse offers
    $xr->close();
    $xr->open($xmlPath, null, LIBXML_NOWARNING | LIBXML_NOERROR);

    $batch = [];
    $batchSize = 200;
    $inserted = 0;

    while ($xr->read()) {
        if ($xr->nodeType === XMLReader::ELEMENT && $xr->name === 'offer') {
            $node = $this->expandNode($xr);
            $data = $this->parseOffer($node);

            DB::transaction(function() use ($data, $feed, $run) {
                $this->upsertProductGraph($data, $feed, $run);
            }, 3);
        }
    }

    $xr->close();
    return $run;
}



    protected function parseCategories(XMLReader $xr, Feed $feed): void
    {
        // Move to <categories>
        while ($xr->read()) {
            if ($xr->nodeType === XMLReader::ELEMENT && $xr->name === 'categories') {
                // Inside, each <category id="..." parentId="...">Name</category>
                while ($xr->read()) {
                    if ($xr->nodeType === XMLReader::END_ELEMENT && $xr->name === 'categories') break;
                    if ($xr->nodeType === XMLReader::ELEMENT && $xr->name === 'category') {
                        $guid = $xr->getAttribute('id');
                        $parentGuid = $xr->getAttribute('parentId');
                        $name = trim($this->readNodeText($xr));

                        // Upsert category (two-phase to resolve parent after insert)
                        $cat = Category::updateOrCreate(
                            ['supplier_guid'=>$guid],
                            ['name'=>$name, 'slug'=>Str::slug($name)]
                        );

                        if ($parentGuid) {
                            $parent = Category::firstOrCreate(
                                ['supplier_guid'=>$parentGuid],
                                ['name'=>$parentGuid, 'slug'=>Str::slug($parentGuid)]
                            );
                            if ($cat->parent_id !== $parent->id) {
                                $cat->parent_id = $parent->id;
                                $cat->save();
                            }
                        }
                    }
                }
                break;
            }
        }
    }

    protected function parseOffer(\DOMElement $offer): array
    {
        $attrId     = $offer->getAttribute('id') ?: null;
        $deleted    = $offer->getAttribute('deleted') === 'true' || $offer->getAttribute('available') === 'false';

        $get = function(string $tag) use ($offer) {
    foreach ($offer->getElementsByTagName('*') as $el) {
        if (strtolower($el->tagName) === strtolower($tag)) {
            return trim($el->textContent);
        }
    }
    return null;
};


        $brandName  = $get('vendor'); // brand
        $name       = $get('name');
        $origName   = $get('original_name');
        $desc       = $get('description');
        $material   = $get('material');
        $gender     = $this->getParam($offer, 'gender');
        $color      = $this->getParam($offer, 'color');
        $sizesStr   = $this->getParam($offer, 'size');
        $currency   = $get('currencyId');
        $price      = $this->toDecimal($get('price'));
         $old_price = $this->toDecimal($get('oldprice'));
        $qty        = $this->toInt($get('qty_actual'));
        $sizeCount  = $this->toInt($get('size_count'));
        $url        = $get('url');
        $brandPage  = $get('brand_page_url');
        $catGuid    = $get('categoryId');
        $cat1       = $get('category1_name');
        $cat2       = $get('category2_name');
        $cat3       = $get('category3_name');
        $modified   = $this->parseEpochSeconds($get('modified_time')); // looks like epoch-like value in snippet

        // pictures
        $pics = [];
        foreach ($offer->getElementsByTagName('picture') as $pic) {
            $v = trim($pic->textContent);
            if ($v) $pics[] = $v;
        }

        // sku: prefer explicit in URLs ?sku=...
        $sku = $this->extractSkuFromUrls([
            $get('android_url'), $get('ios_url'), $url
        ]) ?: $attrId;

        $fingerprint = ProductFingerprint::make($brandName, $name);

        // gather raw map (light)
        $raw = [
            'offer_id'=>$attrId,
            'deleted'=>$deleted,
            'android_url'=>$get('android_url'),
            'ios_url'=>$get('ios_url'),
        ];

        return compact(
            'attrId','deleted','brandName','name','origName','desc','material','gender','color',
            'sizesStr','currency','old_price','price','qty','sizeCount','url','brandPage','catGuid','cat1','cat2','cat3',
            'pics','sku','fingerprint','modified','raw'
        );
    }

    protected function upsertProductGraph(array $d, Feed $feed, FeedRun $run): void
    {
        // 1) Brand
        $brand = null;
        if (!empty($d['brandName'])) {
            $brand = Brand::firstOrCreate(['name'=>$d['brandName']], ['slug'=>Str::slug($d['brandName'])]);
        }

        // 2) Master product by fingerprint
        $product = Product::where('fingerprint', $d['fingerprint'])->first();

        if (!$product) {
            $product = Product::create([
                'brand_id'        => $brand?->id,
                'name'            => $d['name'] ?? $d['origName'] ?? 'Unnamed',
                'original_name'   => $d['origName'],
                'fingerprint'     => $d['fingerprint'],
                'master_sku'      => null, // نسيبه فاضي لو مش ثابت
                'short_description'=> null,
                'long_description' => $d['desc'],
                'material'        => $d['material'],
                'gender'          => $d['gender'],
                'status'          => $d['deleted'] ? 'hidden' : 'active',
                'attributes'      => null,
            ]);
        } else {
            // light update
            $product->fill([
                'brand_id'      => $brand?->id ?? $product->brand_id,
                'original_name' => $product->original_name ?: $d['origName'],
                'long_description' => $product->long_description ?: $d['desc'],
                'material'      => $product->material ?: $d['material'],
                'gender'        => $product->gender ?: $d['gender'],
            ])->save();
        }

        // 3) Images (dedupe by url)
        if (!empty($d['pics'])) {
            $existing = ProductImage::where('product_id',$product->id)->pluck('url')->all();
            $toInsert = [];
            $i = count($existing);
            foreach ($d['pics'] as $url) {
                if (!in_array($url, $existing, true)) {
                    $toInsert[] = ['product_id'=>$product->id,'url'=>$url,'sort_order'=>$i++,'created_at'=>now(),'updated_at'=>now()];
                }
            }
            if ($toInsert) ProductImage::insert($toInsert);
        }

        // 4) Colors
        if ($d['color']) {
            ProductColor::firstOrCreate(['product_id'=>$product->id,'color'=>$d['color']]);
        }

        // 5) Sizes split
        if ($d['sizesStr']) {
            $sizes = $this->splitSizes($d['sizesStr']);
            if ($sizes) {
                $existing = ProductSize::where('product_id',$product->id)->pluck('size')->all();
                $ins = [];
                foreach ($sizes as $s) {
                    if (!in_array($s, $existing, true)) {
                        $ins[] = ['product_id'=>$product->id,'size'=>$s,'created_at'=>now(),'updated_at'=>now()];
                    }
                }
                if ($ins) ProductSize::insert($ins);
            }
        }

        // 6) Category pivot
        if ($d['catGuid']) {
            $cat = Category::where('supplier_guid',$d['catGuid'])->first();
            if ($cat) {
                $exists = DB::table('product_categories')->where([
                    'product_id'=>$product->id, 'category_id'=>$cat->id
                ])->exists();
                if (!$exists) {
                    DB::table('product_categories')->insert([
                        'product_id'=>$product->id, 'category_id'=>$cat->id,
                        'created_at'=>now(),'updated_at'=>now()
                    ]);
                }
            }
        }

        // 7) FeedProduct snapshot (price/url/availability are per feed)
        $available = !$d['deleted'];
        $fp = FeedProduct::updateOrCreate(
            ['feed_run_id'=>$run->id, 'feed_offer_id'=>$d['attrId']],
            [
                'product_id'   => $product->id,
                'feed_id'      => $feed->id,
                'sku'          => $d['sku'],
                'currency'     => $d['currency'],
                'price'        => $d['price'],
                'old_price'    => $d['old_price'],
                'available'    => $available,
                'qty_actual'   => $d['qty'],
                'size_count'   => $d['sizeCount'],
                'modified_time'=> $d['modified'],
                'url'          => $d['url'],
                'brand_page_url'=> $d['brandPage'],
                'cat1_name'    => $d['cat1'],
                'cat2_name'    => $d['cat2'],
                'cat3_name'    => $d['cat3'],
                'raw'          => $d['raw'],
            ]
        );

        // Optionally: also keep (feed_id, offer_id) index for quick lookup across runs
        // Already indexed in migration.
    }

    /* ========================= Utilities ========================= */

    protected function expandNode(XMLReader $xr): \DOMElement
    {
        $doc = new \DOMDocument();
        $node = $xr->expand();
        return $doc->importNode($node, true);
    }

    protected function readNodeText(XMLReader $xr): string
    {
        // reads inner text of current element
        $depth = $xr->depth;
        $text = '';
        while ($xr->read()) {
            if ($xr->nodeType === XMLReader::TEXT || $xr->nodeType === XMLReader::CDATA) {
                $text .= $xr->value;
            } elseif ($xr->nodeType === XMLReader::END_ELEMENT && $xr->depth === $depth) {
                break;
            }
        }
        return trim($text);
    }

    protected function getParam(\DOMElement $offer, string $name): ?string
    {
        foreach ($offer->getElementsByTagName('param') as $p) {
            if ($p->hasAttribute('name') && mb_strtolower($p->getAttribute('name')) === mb_strtolower($name)) {
                return trim($p->textContent);
            }
        }
        return null;
    }

    protected function extractSkuFromUrls(array $urls): ?string
    {
        foreach ($urls as $u) {
            if (!$u) continue;
            $parts = parse_url($u);
            if (!empty($parts['query'])) {
                parse_str($parts['query'], $q);
                if (!empty($q['sku'])) return trim($q['sku']);
            }
        }
        return null;
    }

  protected function toDecimal(?string $s): ?float
{
    if ($s === null || $s === '') return null;

    // إزالة الرموز الغريبة (زي SAR أو EGP)
    $s = preg_replace('/[^\d,.\-]/', '', $s);

    // لو فيها فاصل آلاف (1,299.50)
    if (preg_match('/\d+,\d{3}/', $s)) {
        $s = str_replace(',', '', $s);
    }

    // تأكد إن النقطة هي الفاصل العشري
    $s = str_replace(',', '.', $s);

    return is_numeric($s) ? (float)$s : null;
}


    protected function toInt(?string $s): ?int
    {
        if ($s === null || $s === '') return null;
        return (int)filter_var($s, FILTER_SANITIZE_NUMBER_INT);
    }

    protected function parseEpochSeconds(?string $v): ?\Carbon\Carbon
    {
        if (!$v) return null;
        // بعض الـ feeds بتبعت رقم كبير (epoch seconds أو ms). نحاول نحدده
        $num = (int)$v;
        if ($num > 2000000000) { // likely ms
            $num = (int) round($num / 1000);
        }
        try {
            return \Carbon\Carbon::createFromTimestamp($num);
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function splitSizes(string $sizes): array
    {
        // أمثلة: "39,40,41", "35-36,34", "35.5,36,36.5"
        $sizes = str_replace([';','/','|'], ',', $sizes);
        $parts = array_map('trim', explode(',', $sizes));
        $out = [];
        foreach ($parts as $p) {
            if ($p === '') continue;
            // توسيع 35-36 -> 35,36
            if (preg_match('/^(\d+(?:\.\d+)?)\s*-\s*(\d+(?:\.\d+)?)$/', $p, $m)) {
                $start = (float)$m[1]; $end = (float)$m[2];
                if ($start <= $end) {
                    for ($x=$start; $x <= $end; $x+=1.0) {
                        $out[] = (fmod($x,1.0) === 0.0) ? (string)intval($x) : (string)$x;
                    }
                } else {
                    $out[] = $p; // لو range عكسي
                }
            } else {
                $out[] = $p;
            }
        }
        // unique & keep order
        $out = array_values(array_unique($out));
        return $out;
    }
}
