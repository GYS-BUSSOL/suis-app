<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\TransSendQr;
use App\Models\User;
use Illuminate\Http\{Request, JsonResponse};

class PartnerController extends Controller
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
                    'total_record' => $dataBuilder,
                    'error' => $e
                ],
            ], 500);
        }
    }

    public function list()
    {
        try {
            $dataGet = $this->sendqr->get();
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
