<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Models\SignatureType;
use App\Http\Controllers\Controller;
use App\Models\MstDbCodeReadr;
use App\Models\MstMapLocation;
use App\Models\User;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    protected $coderead;
    protected $maploc;

    public function __construct(MstDbCodeReadr $coderead, MstMapLocation $maploc)
    {
        $this->coderead = $coderead;
        $this->maploc = $maploc;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->maploc->getTable();
        try {
            $payload = $request->all();
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved signature type data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve signature type data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0,
                    'error' => $e
                ],
            ], 500);
        }
    }

    public function add(Request $request)
    {
        $data = [
            'loc_app' => ['required', 'string'],
            'loc_db_codereadr' => ['required', 'string'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            if (is_string($validated['loc_app'])) {
                $validated['loc_app'] = json_decode($validated['loc_app'], true);
            }

            if (is_string($validated['loc_db_codereadr'])) {
                $validated['loc_db_codereadr'] = json_decode($validated['loc_db_codereadr'], true);
            }

            $this->maploc->create($validated);

            return response()->json([
                'status' => 200,
                'message' => 'Signature type created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Signature type failed to create',
            ], 500);
        }
    }

    public function edit(int $id)
    {
        try {
            $location = $this->maploc->firstWhere('id', $id);
            if (empty($location)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Location not found",
                ], 404);
            }
            return response()->json([
                "status" => 200,
                "message" => "Successfully retrieved Location data",
                'data' => $location
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to retrieve Location data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function update(Request $request, int $id)
    {
        $data = [
            'loc_app' => ['required', 'string'],
            'loc_db_codereadr' => ['required', 'string'],
        ];

        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $location = $this->maploc->firstWhere('id', $id);
            if (empty($location)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Signature type not found",
                ], 404);
            }
            if (is_string($validated['loc_app'])) {
                $validated['loc_app'] = json_decode($validated['loc_app'], true);
            }

            if (is_string($validated['loc_db_codereadr'])) {
                $validated['loc_db_codereadr'] = json_decode($validated['loc_db_codereadr'], true);
            }

            if ($location->update($validated)) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully updated signature type data",
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to updated signature type data",
                "error" => "Server error",
            ], 500);
        }
    }

    public function list()
    {
        try {
            $dataGet = $this->coderead->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved mst db codereadr',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve mer signature type data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }
}
