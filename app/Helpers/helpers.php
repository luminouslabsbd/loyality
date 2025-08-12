<?php

    /**
     * Helper functions
     */

    use Illuminate\Support\Facades\Crypt;
    use Illuminate\Support\Facades\DB;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;
    use SimpleSoftwareIO\QrCode\Facades\QrCode;

    include 'urls.php';
    include 'localization.php';
    include 'strings.php';
    include 'conversions.php';

//
//if (!function_exists('hexeToRgb')){
//    function hexeToRgb($hexColor)
//    {
//        list($r, $g, $b) = sscanf($hexColor, "#%02x%02x%02x");
//        return "rgb(" . implode(', ', [$r, $g, $b]) . ")";
//    }
//}

    if (!function_exists('encodeHash')){
        function encodeHash($data)
        {
            $jsonString = json_encode(json_decode($data));
            return Crypt::encrypt($jsonString);
        }
    }

    if (!function_exists('decodeHash')){
        function decodeHash($data)
        {
            return json_decode(Crypt::decrypt($data));
        }
    }

    if (!function_exists('whatsappLink')){
        function whatsappLink($id,$number){
            if ($id && $number) {
                $link = 'https://wa.me/' . $number;
                $link .= '?text=VOUCHER_CODE: ' . urlencode($id);
                return $link;
            }
        }
    }

    if (!function_exists('generateQRCode')){
        function generateQRCode($hash , $data){
            $e = DB::table('hash_qr_code')->insertGetId([
                'tenant_id' => $data->TenantID,
                'campaign_id' => $data->CampaignID,
                'product_id' => $data->ProductID ?? null,
                'order_id' => $data->OrderID ?? null,
                'purchase_value' => $data->PurchaseValue,
                'hash' => $hash,
                'qr_code_path' => 'a',
                'encript_id' => Str::random(7),
                'whatsapp_number' => null,
                'member_email' => $data->email ?? null,
                'whatsapp_bot_number' => $data->phone ?? null,
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $hashEncriptId = DB::table('hash_qr_code')->find($e);

            $tempDir = storage_path('app/tmp/');
            if (!file_exists($tempDir)) {
                mkdir($tempDir, 0755, true);
            }
            $qrCodeFileName = uniqid('qr_code_') . '.png';
            QrCode::format('png')->size(300)->generate(route('qr-scaned', ['hash_id' => $hashEncriptId->encript_id]), $tempDir . $qrCodeFileName);

            $storagePath = 'public/qrcodes/';
            $qrCodePath = $storagePath . $qrCodeFileName;
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }
            Storage::move("tmp/{$qrCodeFileName}", $qrCodePath);

            $fullQRCodeUrl = asset(Storage::url('' . $qrCodePath));

            if ($fullQRCodeUrl) {
                $data = [
                    'qr_code_path' => $fullQRCodeUrl,
                ];
                DB::table('hash_qr_code')->where('id', $e)->update($data);
                return $e;
            } else {
                return false;
            }
        }
    }

    if (!function_exists('addMonthWithCurrentDate')){
        function addMonthWithCurrentDate($month): string
        {
            $currentDate = new \DateTime();
            $currentDate->add(new \DateInterval("P{$month}M"));
            return $currentDate->format('Y-m-d');
        }
    }
