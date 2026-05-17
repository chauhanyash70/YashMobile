<?php

namespace App\Http\Traits;

use Carbon\Carbon;
use App\Models\Barcode;
use App\Models\Invoice;
use Milon\Barcode\DNS1D;
use Milon\Barcode\DNS2D;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

trait Traits
{

    public static function uploadFile($file, $path)
    {
        $filePath = Storage::disk(config('app.filesystem_disk'))->put($path, $file, 'public');
        return $filePath;
    }

    public static function removeFile($path)
    {
        Storage::disk(config('app.filesystem_disk'))->delete($path);
        return true;
    }

    public static function generateOnlyNumber($length)
    {
        $rand = rand(1, 9999);
        return str_pad($rand, $length, '0', STR_PAD_LEFT);
    }


    /**
     * Get File Type
     */
    public static function getFileType($file)
    {

        $imageExtensions = ['jpg', 'jpeg', 'gif', 'png', 'bmp'];
        $videoExtensions = ['webm', 'mkv', 'mp4'];
        if (in_array($file->extension(), $imageExtensions)) {
            $type = 'image';
        } else if (in_array($file->extension(), $videoExtensions)) {
            $type = 'video';
        } else {
            $type = 'file';
        }
        return $type;
    }

    /**
     * Get Greeting 
     */
    public static function getGreeting()
    {
        $now = Carbon::now();
        if ($now->between(Carbon::createFromTime(5, 0), Carbon::createFromTime(11, 59))) {
            return "Good Morning";
        } elseif ($now->between(Carbon::createFromTime(12, 0), Carbon::createFromTime(16, 59))) {
            return "Good Afternoon";
        } elseif ($now->between(Carbon::createFromTime(17, 0), Carbon::createFromTime(20, 59))) {
            return "Good Evening";
        } else {
            return "Good Night";
        }
    }

    /**
     * Get Invoice Number
     */
    public static function getInvoiceNumber()
    {
        $invoice = Invoice::orderBy('id', 'desc')->first();
        if ($invoice) {
            return $invoice->invoice_no + 1;
        } else {
            return 10001;
        }
    }

    /**
     * Generate Barcode
     */
    public static function generateBarcode($code)
    {
        /* $barcodePath = DNS2D::getBarcodePNGPath($code, 'PDF417', 85, 33, array(1, 1, 1), true); */
        /* $barcodePath = DNS1D::getBarcodePNGPath($code, 'PHARMA2T',3,33,array(1,1,1), true); */
        $barcode = new DNS1D();
        $barcodePath = $barcode->getBarcodePNG($code, 'EAN13',3,65,array(1,1,1), true);
        $folder = 'admin/barcodes';
        $filename = $folder . '/' . Str::random(10) . '.png';
        Storage::disk(config('app.filesystem_disk'))->makeDirectory($folder);
        Storage::disk(config('app.filesystem_disk'))->put($filename, base64_decode($barcodePath));
        return $filename;
    }

    /**
     * Generate random number
     */
    public static function generateRandomNumber($length)
    {
        $rand = rand(1, 999999999999);
        return str_pad($rand, $length, '0', STR_PAD_LEFT);
    }

    /**
     * Generate barcode
     */
    public static function generateBarcodeSequence()
    {
        $barcode = Barcode::orderBy('id', 'desc')->first();
        if ($barcode) {
            return $barcode->barcode + 1;
        } else {
            return 909999814101;
        }
    }

}
