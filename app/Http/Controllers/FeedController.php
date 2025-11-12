<?php

namespace App\Http\Controllers;

use App\Models\Feed;
use Illuminate\Http\Request;
use App\Services\Feed\XmlImporter;

class FeedController extends Controller
{
    public function importForm()
    {
        return view('feeds.import');
    }

    public function uploadFile(Request $request)
{
    $request->validate([
        'file' => 'required|file'
    ]);

    $filename = uniqid().'.xml';
    $path = public_path('tmp_feeds/'.$filename);

    if(!is_dir(public_path('tmp_feeds'))){
        mkdir(public_path('tmp_feeds'), 0777, true);
    }

    $request->file('file')->move(public_path('tmp_feeds'), $filename);

    return response()->json([
        'path' => 'tmp_feeds/'.$filename,
        'full' => $path
    ]);
}



public function importUpload(Request $request, XmlImporter $importer)
{
    $request->validate([
        'uploaded_file' => 'required|string',
        'code'          => 'required'
    ]);

    $feed = Feed::firstOrCreate(
        ['code'=>$request->code],
        [
            'name' => $request->name ?: $request->code,
            'type' => 'xml',
            'default_currency' => $request->currency,
            'country_code' => $request->country,
            'is_active' => true,
        ]
    );

    // pass EXACT string path eg: tmp_feeds/xxxx.xml
    $run = $importer->import($request->uploaded_file, $feed);

    return back()->with('success',"Imported! FeedRun #{$run->id}");
}


}



















