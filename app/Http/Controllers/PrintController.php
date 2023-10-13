<?php

namespace App\Http\Controllers;

use App\Models\Loan;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Payments;


class PrintController extends Controller
{
    public function printPayment($id)
    {
        $payment = Payments::find($id);
        $pdf = PDF::loadView('pdf.reports.payment', compact('payment'))->setPaper('legal', 'portrait');
        return $pdf->download('payment.pdf');

    }

    public function printGaranty($id)
    {
        $loan = Loan::find($id);
        //dd($loan);
        $pdf = PDF::loadView('pdf.reports.garanty', compact('loan'))->setPaper('legal', 'portrait');
        return $pdf->download('garanty'.date('ymdhsm').'.pdf');

    }
}
