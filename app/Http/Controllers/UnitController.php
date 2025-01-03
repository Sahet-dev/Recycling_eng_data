<?php
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizDetail;
use App\Models\QuizUnit;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class UnitController extends Controller
{
    public function index(): JsonResponse
    {
        $units = Unit::with(['details', 'images'])
            ->where('visibility', Unit::VISIBLE)
            ->orderBy('unit', 'asc')
            ->paginate(10);  // Paginate results

        // Transform the data to match your frontend needs
        $transformed = $units->getCollection()->map(function ($unit) {
            return [
                'id'       => $unit->id,
                'unit'     => $unit->unit,
                'visibility' => $unit->visibility,
                'details'  => $unit->details->map(function ($detail) {
                    return [
                        'id'      => $detail->id,
                        'unit_id' => $detail->unit_id,
                        'title'   => $detail->title,
                    ];
                }),
                'images'   => $unit->images->map(function ($img) {
                    return [
                        'id'      => $img->id,
                        'unit_id' => $img->unit_id,
                        'url'     => $img->url,
                    ];
                }),
            ];
        });

        // Return paginated response with meta and links
        return response()->json([
            'data' => $transformed,
            'links' => $units->links(), // Pagination links
            'meta'  => [
                'current_page' => $units->currentPage(),
                'per_page' => $units->perPage(),
                'total' => $units->total(),
                'last_page' => $units->lastPage(),
            ], // Pagination meta data
        ]);
    }



    public function store(Request $request): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

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

    public function showUnit($unitId, Request $request): JsonResponse
    {


        $unit = Unit::with('details')->findOrFail($unitId);
        return response()->json($unit);
    }


    public function update(Request $request, $id): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $request->validate([
            'unit' => 'required|integer',
            'visibility' => 'required|in:visible,hidden',
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
        $unit->update([
            'unit' => $request->unit,
            'visibility' => $request->visibility,
            ]);


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

    public function getQuizByUnitId($unitId): JsonResponse
    {
        // Fetch the quiz unit along with its details and questions
        $unit = QuizUnit::with('quizDetails.questions')->find($unitId);
        if (!$unit) {
            return response()->json(['message' => 'Unit not found'], 404);
            }
        return response()->json($unit);
    }

    public function storeQuiz(Request $request) {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        $data = $request->all();  // Assuming your JSON is sent in the body of the POST request

        DB::transaction(function () use ($data) {
            foreach ($data as $unitData) {
                $unit = QuizUnit::create([
                    'title' => $unitData['title']
                ]);

                foreach ($unitData['details'] as $detail) {
                    $quizDetail = $unit->quizDetails()->create([
                        'title' => $detail['title'],
                        'instructions' => $detail['instructions'] ?? null
                    ]);

                    foreach ($detail['questions'] as $question) {
                        $quizDetail->questions()->create([
                            'text' => $question['text'] ?? null,
                            'answer' => $question['answer'] ?? null // Now supports array or string
                        ]);
                    }
                }
            }
        });

        return response()->json(['status' => 'success'], 200);
    }

    public function updateQuizByUnitId(Request $request, $unitId): JsonResponse
    {
        if (!$request->user() || $request->user()->role !== 'admin') {
            return response()->json(['message' => 'Access denied'], 403);
        }

        DB::beginTransaction();

        try {
            // Find the QuizUnit
            $unit = QuizUnit::findOrFail($unitId);

            // Update the QuizUnit properties (e.g., title)
            $unit->update([
                'title' => $request->input('title', $unit->title),
            ]);

            // Update each QuizDetail and its associated questions
            $quizDetails = $request->input('quiz_details', []);
            foreach ($quizDetails as $detailData) {
                // Find or create the QuizDetail
                $quizDetail = QuizDetail::findOrNew($detailData['id']);
                $quizDetail->fill([
                    'unit_id' => $unitId,
                    'title' => $detailData['title'],
                    'instructions' => $detailData['instructions'] ?? null,
                ]);
                $quizDetail->save();

                // Update Questions for each QuizDetail
                foreach ($detailData['questions'] as $questionData) {
                    $question = Question::findOrNew($questionData['id']);
                    $question->fill([
                        'detail_id' => $quizDetail->id,
                        'text' => $questionData['text'],
                        'answer' => $questionData['answer'],
                    ]);
                    $question->save();
                }
            }

            // Commit the transaction
            DB::commit();

            return response()->json([
                'message' => 'Quiz updated successfully',
                'data' => $unit->load('quizDetails.questions'), // Return the updated unit with quiz details
            ]);

        } catch (\Exception $e) {
            // Rollback in case of an error
            DB::rollBack();

            // Return error response
            return response()->json(['message' => 'Failed to update quiz', 'error' => $e->getMessage()], 500);
        }
    }






    public function updateVisibility($id, $status): JsonResponse
    {
        $unit = Unit::findOrFail($id);

        // Validate the status
        if (!in_array($status, [Unit::VISIBLE, Unit::HIDDEN])) {
            return response()->json(['error' => 'Invalid status'], 400);
        }

        // Update the unit's visibility
        $unit->update(['visibility' => $status]);

        return response()->json(['message' => 'Unit visibility updated successfully']);
    }

}
