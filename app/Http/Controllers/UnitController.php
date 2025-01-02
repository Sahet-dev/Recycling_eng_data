<?php
namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\QuizDetail;
use App\Models\QuizUnit;
use App\Models\Unit;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class UnitController extends Controller
{
    public function index()
    {
        $units = Unit::with(['details', 'images'])->get();

        // Transform only the fields you need:
        $transformed = $units->map(function ($unit) {
            return [
                // from the 'units' table
                'id'     => $unit->id,
                'unit'   => $unit->unit,

                // from the 'details' relationship
                'details' => $unit->details->map(function ($detail) {
                    return [
                        'id'      => $detail->id,
                        'unit_id' => $detail->unit_id,
                        'title'   => $detail->title,
                        // exclude 'content', 'section', 'example', etc.
                    ];
                }),

                // from the 'images' relationship
                'images' => $unit->images->map(function ($img) {
                    return [
                        'id'      => $img->id,
                        'unit_id' => $img->unit_id,
                        'url'     => $img->url,
                    ];
                }),
            ];
        });

        return response()->json($transformed);
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



}
