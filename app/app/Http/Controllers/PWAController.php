<?php namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class PWAController extends Controller
{
    public function manifest()
    {
        return response()->json([
            "dir" => "ltr",
            "lang" => "en",
            "name" => "AccessLocator",
            "display" => "fullscreen",
            "start_url" => "/?using_pwa=1",
            "short_name" => "AccessLocator",
            "background_color" => "#e7f3f5",
            "theme_color" => "#202767",
            "description" => "Your personalized access to the world",
            "orientation" => "portrait",
            "background_color" => "#202767",
            "related_applications" => [],
            "prefer_related_applications" => false,
            "icons" => [
                [
                    "src" => "/images/logo-192x192.png",
                    "type" => "image/png",
                    "sizes" => "192x192"
                ],
                [
                    "src" => "/images/logo-512x512.png",
                    "type" => "image/png",
                    "sizes" => "512x512"
                ]
            ]
        ]);
    }
}