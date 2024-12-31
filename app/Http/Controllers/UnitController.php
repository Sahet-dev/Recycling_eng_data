<?php
namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index()
    {
        // Fetch units with their related details and images (if images are stored in the DB or as URLs)
        $units = Unit::with('details')->get();
        return response()->json($units);
    }

    public function store(Request $request): JsonResponse
    {
        // Validate the request
        $request->validate([
            'units' => 'required|array',
            'units.*.unit' => 'required|integer',
            'units.*.details' => 'required|array',
            'units.*.details.*.title' => 'nullable|string',
            'units.*.details.*.section' => 'nullable|string',
            'units.*.details.*.content' => 'nullable|string',
            'units.*.details.*.example' => 'nullable|string' ,
        ]);

        foreach ($request->units as $unitData) {
            // Insert unit data into the `units` table
            $unit = Unit::create([
                'unit' => $unitData['unit']
            ]);

            foreach ($unitData['details'] as $detailData) {
                // Insert details for each unit
                $unit->details()->create([
                    'title' => $detailData['title'] ?? null,
                    'section' => $detailData['section'] ?? null,
                    'content' => $detailData['content'] ?? null,
                    'example' => $detailData['example'] ?? null,
                ]);
            }
        }

        return response()->json(['message' => 'Data inserted successfully'], 201);
    }

    // In UnitController.php
    public function showUnit($unitId): JsonResponse
    {
        $unit = Unit::with('details')->findOrFail($unitId);
        return response()->json($unit);
    }

    /**
     * Update a specific unit and its details.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id): JsonResponse
    {
        $request->validate([
            'unit' => 'required|integer',
            'details' => 'required|array',
            'details.*.title' => 'nullable|string',
            'details.*.section' => 'nullable|string',
            'details.*.content' => 'nullable|string',
            'details.*.example' => 'nullable|string',
        ]);

        $unit = Unit::find($id);

        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
        }

        // Update unit fields
        $unit->update(['unit' => $request->unit]);

        // Update or create details
        foreach ($request->details as $detailData) {
            $unit->details()->updateOrCreate(
                ['id' => $detailData['id'] ?? null], // Match existing detail by ID
                [
                    'title' => $detailData['title'],
                    'section' => $detailData['section'],
                    'content' => $detailData['content'],
                    'example' => $detailData['example'],
                ]
            );
        }

        return response()->json(['message' => 'Unit updated successfully', 'unit' => $unit->load('details')], 200);
    }

}
