<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class ImageController extends Controller
{
    public function storeBulk(Request $request)
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }
        // expecting structure: [ { "unit": 1, "images": [ { "id":1, "url":"images/1.jpg" }, ... ] }, ... ]

        $imagesData = $request->all(); // or $request->input('images');

        foreach ($imagesData as $entry) {
            $unitId = $entry['unit'];  // e.g. 1, 2, 3, ...
            foreach ($entry['images'] as $img) {
                Image::create([
                    'unit_id' => $unitId,
                    'url'     => $img['url'],
                ]);
            }
        }

        return response()->json([
            'message' => 'Images inserted successfully'
        ], 201);
    }

}
