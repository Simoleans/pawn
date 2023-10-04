<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payments;


class PrintController extends Controller
{
    public function printPayment($id)
    {
        $payment = Payments::find($id);
        $pdf = PDF::loadView('pdf.reports.payment', compact('payment'))->setPaper('legal', 'portrait');
        return $pdf->stream('payment.pdf');
    }
}
