<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\TransSendQr;
use App\Models\User;
use Illuminate\Http\{Request, JsonResponse};

class ProcurementController extends Controller
{
    protected $sendqr;

    public function __construct(TransSendQr $sendqr)
    {
        $this->sendqr = $sendqr;
    }

    public function search(Request $request)
    {
        $tableColumn = $this->sendqr->getTable();
        try {
            $payload = $request->all();
            $dataBuilder = $this->setUpPayload($payload, $tableColumn);
            $builder = $dataBuilder['builder'];
            $countBuilderDistinct = $dataBuilder['distinct'];
            $dataGet = $builder->distinct()->get();
            $totalRecord = $countBuilderDistinct->get()->count();
            $totalShowData = $dataGet->count();

            // $resultData = [];

            // foreach ($dataGet as $arrDataParent) {
            //     $mulData = $this->customData(explode(",", $arrDataParent->st_user));
            //     $arrMULData = ['aplication_name' => []];

            //     foreach ($mulData as $mermbcw) {
            //         if (in_array($mermbcw['id'], explode(",", $arrDataParent->st_user))) {
            //             $arrMULData['aplication_name'][] = [
            //                 'id' => $mermbcw['id'],
            //             ];
            //         }
            //     }
            //     $mergedData = array_merge(
            //         json_decode(json_encode($arrDataParent), true),
            //         $arrMULData
            //     );
            //     $resultData[] = $mergedData;
            // }

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Procurement data',
                'data' => [
                    'rows' => $dataGet,
                    'total_data' => $totalShowData,
                    'total_record' => $totalRecord,
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Procurement data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0,
                    'error' => $e
                ],
            ], 500);
        }
    }


    // public function add(Request $request)
    // {
    //     $data = [
    //         'st_desc' => ['required', 'string'],
    //         'st_user' => ['required', 'array'],
    //     ];

    //     $validated = $this->handleValidationException($request, $data);
    //     if ($validated instanceof JsonResponse) {
    //         return $validated;
    //     }

    //     try {
    //         if (is_string($validated['st_user'])) {
    //             $validated['st_user'] = json_decode($validated['st_user'], true);
    //         }

    //         if (is_array($validated['st_user'])) {
    //             $validated['st_user'] = join(", ", $validated['st_user']);
    //         }
    //         $this->st->create($validated);

    //         return response()->json([
    //             'status' => 200,
    //             'message' => 'Signature type created successfully'
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             'status' => 500,
    //             'error' => 'Server error',
    //             'message' => 'Signature type failed to create',
    //         ], 500);
    //     }
    // }

    // public function edit(int $id)
    // {
    //     try {
    //         $signType = $this->st->firstWhere('st_id', $id);
    //         if (empty($signType)) {
    //             return response()->json([
    //                 "status" => 404,
    //                 "message" => "Signature type not found",
    //             ], 404);
    //         }
    //         return response()->json([
    //             "status" => 200,
    //             "message" => "Successfully retrieved signature type data",
    //             'data' => $signType
    //         ], 200);
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "status" => 500,
    //             "message" => "Failed to retrieve signature type data",
    //             "error" => "Server error",
    //         ], 500);
    //     }
    // }

    // public function update(Request $request, int $id)
    // {
    //     $data = [
    //         'st_desc' => ['required', 'string'],
    //         'st_user' => ['required', 'array'],
    //     ];

    //     $validated = $this->handleValidationException($request, $data);
    //     if ($validated instanceof JsonResponse) {
    //         return $validated;
    //     }

    //     try {
    //         $st = $this->st->firstWhere('st_id', $id);
    //         if (empty($st)) {
    //             return response()->json([
    //                 "status" => 404,
    //                 "message" => "Signature type not found",
    //             ], 404);
    //         }
    //         if (is_string($validated['st_user'])) {
    //             $validated['st_user'] = json_decode($validated['st_user'], true);
    //         }

    //         if (is_array($validated['st_user'])) {
    //             $validated['st_user'] = join(", ", $validated['st_user']);
    //         }

    //         if ($st->update($validated)) {
    //             return response()->json([
    //                 "status" => 200,
    //                 "message" => "Successfully updated signature type data",
    //             ], 200);
    //         }
    //     } catch (\Exception $e) {
    //         return response()->json([
    //             "status" => 500,
    //             "message" => "Failed to updated signature type data",
    //             "error" => "Server error",
    //         ], 500);
    //     }
    // }

    public function list()
    {
        try {
            $dataGet = $this->sendqr->get();
            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved mer Procurement data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve mer Procurement data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    public function destroy(int $id)
    {
        try {
            $proc = $this->sendqr->firstWhere('id', $id);
            if (empty($proc)) {
                return response()->json([
                    "status" => 404,
                    "message" => "Procurement not found",
                ], 404);
            }

            if ($proc->delete()) {
                return response()->json([
                    "status" => 200,
                    "message" => "Successfully deleted Procurement data"
                ], 200);
            }
        } catch (\Exception $e) {
            return response()->json([
                "status" => 500,
                "message" => "Failed to delete Procurement data",
                "error" => "Server error",
            ], 500);
        }
    }
}
