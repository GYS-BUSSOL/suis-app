<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\TransSendQr;
use App\Models\TransSendQrDetail;
use App\Models\User;
use BaconQrCode\Renderer\GDLibRenderer;
use BaconQrCode\Writer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\DB;

class PartnerController extends Controller
{
    protected $sendqr;
    protected $sendqrDetail;

    public function __construct(TransSendQr $sendqr, TransSendQrDetail $sendqrDetail)
    {
        $this->sendqr = $sendqr;
        $this->sendqrDetail = $sendqrDetail;
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

    public function listAccessLevel()
    {
        try {
            $dataGet = DB::table('tbl_accesslevel')
                ->where('acc_level', '2c909420874a45f001877175054544d0')
                ->get();

            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Access Level Partner data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Access Level Partner data',
                'data' => [
                    'rows' => [],
                    'total_record' => 0
                ],
            ], 500);
        }
    }

    private function fileAttachment($file, $visitorPhoto)
    {
        $filename = $visitorPhoto . '.' . $file->getClientOriginalExtension();
        $file->move(public_path('storage/Photo'), $filename);
        return $filename;
    }

    public function add(Request $request)
    {
        $data = [
            'visitor_name' => ['required', 'string'],
            'purpose' => ['required', 'string'],
            'non_exp' => ['required', 'string'],
            'duration' => ['required', 'string'],
            'location' => ['required', 'string'],
            'acc_level' => ['required', 'array']
        ];
        $validated = $this->handleValidationException($request, $data);
        if ($validated instanceof JsonResponse) {
            return $validated;
        }

        try {
            $duration = $request->input('duration');
            $start_date = null;
            $exp_date = null;
            $userName = session('nama');
            $uploadedAttachment = null;

            $prefix = '24' . Carbon::now()->format('ym');
            // Ambil 3 digit terakhir dari nomor QR dengan prefix tertentu
            $query = DB::table('trans_sendQr')
                ->selectRaw('RIGHT(header_value, 4) as qr_no')
                ->where('header_value', 'like', $prefix . '%')
                ->orderBy('header_value', 'desc')
                ->first();
            // Jika ada query, increment nomor terakhir; jika tidak, mulai dari 1
            if ($query) {
                $kode = intval($query->qr_no) + 1;
            } else {
                $kode = 1;
            }
            // Format nomor menjadi 3 digit dengan padding nol di depan
            $batas = str_pad($kode, 4, "0", STR_PAD_LEFT);
            // Gabungkan prefix dan nomor untuk menghasilkan kode QR
            $kodeqr = $prefix . $batas;
            $qrImage = $kodeqr . '.png';

            $path = public_path('storage/barcode/' . $qrImage);
            $visitorPhoto = "visitor_" . $kodeqr;

            if ($request->hasFile('visitor_photo')) {
                $request->validate([
                    'visitor_photo' => ['file', 'extensions:doc,pdf,gif,jpeg,png', 'max:2048']
                ]);
                $uploadedAttachment = $this->fileAttachment($request->file('visitor_photo'), $visitorPhoto);
            }

            if ($duration) {
                $duration = trim($duration, '"');
                list($start_date, $exp_date) = explode(' to ', $duration);
                $start_date = Carbon::createFromFormat('Y-m-d', $start_date)->format('Y-m-d');
                $exp_date = Carbon::createFromFormat('Y-m-d', $exp_date)->format('Y-m-d');
            }
            // data yang dimasukan table trans_sendQr
            $insSendQr = [
                "aplication_name"    => "Security Information System",
                "header_value"       => $kodeqr,
                "visitor_name"       => $validated['visitor_name'],
                "visitor_photo"      => $uploadedAttachment,
                "purpose"            => $validated['purpose'],
                "start_date"         => $start_date,
                "exp_date"           => $exp_date,
                "location"           => $validated['location'],
                "non_exp"            => $validated['non_exp'],
                "istransfer"         => 0,
                "status"             => 0,
                "crd_status"         => 0,
                "vault_status"       => 0,
                "crby"               => $userName,
                "crdt"               => date("Y-m-d H:i")
            ];

            $transSendQr = $this->sendqr->create($insSendQr);
            if ($transSendQr) {
                $headerID = $transSendQr->id;
                $data = DB::table('trans_sendQr')->where('id', $headerID)->first();
                // data untuk dimasukkan ke tabel trans_sendQr_detail
                foreach ($validated['acc_level'] as $level) {
                    $insDtl = [
                        "id_header"  => $headerID,
                        "acc_level"  => $level,
                        "istransfer" => 0,
                    ];

                    $this->sendqrDetail->create($insDtl);
                }
            }
            return response()->json([
                'status' => 200,
                'message' => 'Partner Barcode created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Partner Barcode failed to create' . $e->getMessage(),
            ], 500);
        }
    }
}
