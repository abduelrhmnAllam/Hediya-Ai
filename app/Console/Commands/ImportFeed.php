<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Feed;
use App\Services\Feed\XmlImporter;

class ImportFeed extends Command
{
    protected $signature = 'feed:import
        {file : Path to XML file}
        {--feed= : Feed code (e.g. levelshoes_sa)}
        {--name= : Feed name if creating new}
        {--country= : Country code (SA, AE, ...)}
        {--currency= : Default currency (SAR, AED, ...)}';

    protected $description = 'Import YML/XML catalog into normalized product schema';

    public function handle(XmlImporter $importer)
    {
        $file = $this->argument('file');
        if (!is_file($file)) {
            $this->error("File not found: {$file}");
            return self::FAILURE;
        }

        $code = $this->option('feed');
        if (!$code) {
            $this->error('Please provide --feed=feed_code');
            return self::FAILURE;
        }

        $feed = Feed::firstOrCreate(
            ['code'=>$code],
            [
                'name' => $this->option('name') ?: $code,
                'type' => 'xml',
                'default_currency' => $this->option('currency'),
                'country_code' => $this->option('country'),
                'is_active' => true,
            ]
        );

        $this->info("Importing {$file} into feed [{$feed->code}] ...");
        $run = $importer->import($file, $feed);
        $this->info("Done. FeedRun #{$run->id} saved for feed={$feed->id} file={$run->file_name}");

        return self::SUCCESS;
    }
}
