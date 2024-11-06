<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anime;

class AnimeController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request, $slug)
    {
        // dd($slug);
        $lang = 'PL'; // default language

        if($request->has('lang')){
            $lang = strtoupper($request->query('lang'));
        }

        $anime = Anime::whereJsonContains('title_slug',['lang' => $lang])
                    ->whereJsonContains('title_slug',['slug' => $slug])
                    ->get();

        return response()->json([
            'status' => true,
            'data' => $anime,
        ]);
    }
}
