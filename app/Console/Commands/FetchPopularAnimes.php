<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Anime;

class FetchPopularAnimes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:fetch-popular-animes';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $endpoint = "https://api.jikan.moe/v4/top/anime";
        $client = new \GuzzleHttp\Client();
        
        Anime::truncate();

        for ($i=1; $i <= 4; $i++) {

            $response = $client->request('GET', $endpoint, ['query' => [
                'filter' => "bypopularity", 
                'page' => $i,
            ]]);

            $statusCode = $response->getStatusCode();
            $content = json_decode($response->getBody()->getContents(),true);
            
            if($statusCode == 200){

                $data = [];

                foreach ($content['data'] as $key => $anime) {
                    
                    $title_slug = [];

                    foreach ($anime['titles'] as $title_key => $title) {
                        
                        if($title['type'] == 'English'){
                            $title_slug[] = [
                                'lang' => 'EN',
                                'title' => $title['title'],
                                'slug' => self::slugify($title['title'],'-')
                            ];
                        }

                        if($title['type'] == 'French'){
                            $title_slug[] = [
                                'lang' => 'PL',
                                'title' => $title['title'],
                                'slug' => self::slugify($title['title'],'-')
                            ];
                        }
                            
                    }

                    $data[] = [
                        "mal_id" => $anime['mal_id'],
                        "url" => $anime['url'],
                        "title_slug" => json_encode($title_slug),
                        "synopsis" => $anime['synopsis'],
                        "popularity" => $anime['popularity'],
                        "created_at" => now(),
                        "updated_at" => now()
                    ];
                        
                }

                Anime::insert($data);
            }

        }

    }

    private static function slugify($text, string $divider = '-')
    {
        // replace non letter or digits by divider
        $text = preg_replace('~[^\pL\d]+~u', $divider, $text);

        // transliterate
        $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);

        // remove unwanted characters
        $text = preg_replace('~[^-\w]+~', '', $text);

        // trim
        $text = trim($text, $divider);

        // remove duplicate divider
        $text = preg_replace('~-+~', $divider, $text);

        // lowercase
        $text = strtolower($text);

        if (empty($text)) {
        return 'n-a';
        }

        return $text;
    }
}
