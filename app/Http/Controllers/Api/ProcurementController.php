<?php

namespace App\Http\Controllers\Api;

use Carbon\Carbon;
use App\Http\Controllers\Controller;
use App\Models\TransSendQr;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\{Request, JsonResponse};
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    public function listAccessLevel()
    {
        try {
            $dataGet = DB::table('tbl_accesslevel')
                ->where('acc_level', '2c909420874a45f001877175054544d0')
                ->get();

            $totalRecord = $dataGet->count();

            return response()->json([
                'status' => 200,
                'message' => 'Successfully retrieved Access Level Procurement data',
                'data' => [
                    'rows' => $dataGet,
                    'total_record' => $totalRecord
                ],
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'message' => 'Failed to retrieve Access Level Procurement data',
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
        return 'Photo/' . $filename;
    }

    public function add(Request $request)
    {
        $data = [
            'visitor_name' => ['required', 'string'],
            'purpose' => ['required', 'string'],
            'non_exp' => ['required', 'string'],
            'duration' => ['required', 'string'],
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

            $prefix = '230' . Carbon::now()->format('ym');
            // Ambil 3 digit terakhir dari nomor QR dengan prefix tertentu
            $query = DB::table('trans_sendQr')
                ->selectRaw('RIGHT(header_value, 3) as qr_no')
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
            $batas = str_pad($kode, 3, "0", STR_PAD_LEFT);
            // Gabungkan prefix dan nomor untuk menghasilkan kode QR
            $kodeqr = $prefix . $batas;

            $path = storage_path('storage/barcode/' . $kodeqr . '.png');
            // QrCode::format('png')->size(300)->generate($kodeqr, $path);

            $visitorPhoto = "visitor_" . $kodeqr;

            if ($request->hasFile('visitor_photo')) {
                Log::info('fileAttachment');
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
                "header_value"        => $kodeqr,
                "visitor_name"        => $validated['visitor_name'],
                "visitor_photo"        => $uploadedAttachment,
                "purpose"            => $validated['purpose'],
                "start_date"        => $start_date,
                "exp_date"            => $exp_date,
                "non_exp"            => $validated['non_exp'],
                "istransfer"        => 0,
                "status"            => 0,
                "crd_status"        => 0,
                "vault_status"        => 0,
                "crby"                => $userName,
                "crdt"                => date("Y-m-d H:i")
            ];

            $transSendQr = $this->sendqr->create($insSendQr);
            if ($transSendQr) {
                $headerID = $transSendQr->id;
                // data untuk dimasukkan ke tabel trans_sendQr_detail
                for ($i = 0; $i < count($validated['acc_level']); $i++) {
                    $insDtl = array(
                        "id_header"        => $headerID,
                        "acc_level"        => $validated['acc_level'][$i],
                        "istransfer"    => 0
                    );

                    $this->sendqr->create($insDtl);
                }
                // Ambil data dari database
                $data = DB::table('trans_sendQr')->where('id', $headerID)->first();

                if (!$data) {
                    return response()->json(['error' => 'Data tidak ditemukan'], 404);
                }

                // Data untuk view PDF
                $description = "
                    <html>
                    <h1>{$data->visitor_name}</h1><br>
                    <div>
                        <b>NBP ACCESS - VISITOR </b><br>
                        <b>PURPOSE/KEPERLUAN : {$data->purpose} </b><br>
                        [{$data->location}: ACCESS GRANTED / AKSES DIBERIKAN]
                    </div>
                    </html>
                ";

                $viewData = [
                    'visitor_name' => $data->visitor_name,
                    'barcode' => $kodeqr,
                ];

                if (QrCode::format('png')->size(300)->generate($kodeqr, $path)) {
                    // Render HTML untuk PDF
                    $pdfHtml = view('cards', $viewData)->render();

                    // Generate PDF
                    $pdf = Pdf::loadHTML($pdfHtml)->setPaper('a4', 'portrait');
                    $pdfPath = public_path("storage/card/{$kodeqr}.pdf");
                    file_put_contents($pdfPath, $pdf->output());
                    // Update database
                    DB::table('trans_sendQr')
                        ->where('id', $headerID)
                        ->update([
                            'barcode' => "{$kodeqr}.pdf",
                            'qr_image' => $kodeqr,
                            'status' => 1,
                            'description' => $description,
                        ]);
                }
            }
            return response()->json([
                'status' => 200,
                'message' => 'Procurement Barcode created successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 500,
                'error' => 'Server error',
                'message' => 'Procurement Barcode failed to create' . $e->getMessage(),
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
