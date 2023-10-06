@extends('pdf.layout.main')
@section('content')
    <br>
    <div class="container">
        <div class="row">
            <div class="col-xs-6" style="font-weight: 900;"><b>EFECTIVA TU ALIADA S.R.L</b></div>
            <div class="col-xs-4 border"><b>N </b></div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-4 col-xs-offset-4" style="font-weight: 900">CASA DE EMPEÑO Y CAMBIOS</div>
        </div>
        <div class="row text-center">
            <div class="col-xs-4 col-xs-offset-4"><small>AV. America N° 309 entre F. Granado y S. Roncal</small></div>
        </div>
        <div class="row text-center">
            <div class="col-xs-4 col-xs-offset-4"><small>Telefono 4320343</small></div>
        </div>
        <div class="row text-center">
            <div class="col-xs-4 col-xs-offset-4"><small>Whatsapp 71728686</small></div>
        </div>
        <div class="row text-center title">
            @if ($payment->type_payment == 'amortization')
                <div class="col-xs-6 col-xs-offset-3"><small>AMORTIZACION</small></div>
                @elseif($payment->type_payment == 'renovation')
                <div class="col-xs-6 col-xs-offset-3"><small>INTERES Y GASTOS DE CONSERVACION</small></div>
                @elseif ($payment->type_payment == 'complete')
                <div class="col-xs-6 col-xs-offset-3"><small>CANCELACION DEL CONTRATO</small></div>

            @endif
        </div>
        <br>
        <div class="row">
            <div class="col-xs-12"><b>*********************************************************************************************************************</b>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Lugar</div>
            <div class="col-xs-4" style="font-weight: 900;">{{ $payment->loan->client->issued }}</div>
        </div>
          <br>
        <div class="row">
            <div class="col-xs-3" style="font-weight: 900;">Fecha</div>
            <div class="col-xs-3" style="font-weight: 900;">{{ $payment->created_at->format("Y-m-d") }}</div>
            <div class="col-xs-3" style="font-weight: 900;">Hora</div>
            <div class="col-xs-3" style="font-weight: 900;">{{ $payment->created_at->format("H:m:s") }}</div>
        </div>
          <br>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Señor (a):</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->client->first_name.' '.$payment->loan->client->last_name }}
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Cedula:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ strtoupper($payment->loan->client->document) }}
            </div>
        </div>
        <br>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Cod. Cliente:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->client->code }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Cod. Contrato:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->code_contract }}
            </div>
        </div>
        <br>
        @if (count($payment->loan->items) > 0)
            <div class="row">
                <div class="col-xs-12"><b>*****************************************************************************************************************************</b>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-5" style="font-weight: 900;">PRENDAS</div>
            </div>
            <br>
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            Nombre
                        </th>
                        <th class="text-center font-bold py-3">
                            Valor estimado
                        </th>
                        <th class="text-center font-bold py-3">
                            Moneda
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payment->loan->items as $i)
                        <tr>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $i->item->name }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $i->item->estimated_value }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $i->item->currency}}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
        <div class="row">
            <div class="col-xs-12"><b>*********************************************************************************************************************</b>
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Saldo Adeudado</div>
            <div class="col-xs-4" style="font-weight: 900;">{{ $payment->loan->capital }}</div>
        </div>
          <br>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Interes 3%</div>
            <div class="col-xs-4" style="font-weight: 900;">{{ $payment->loan->legal_interest }}</div>
            {{-- <div class="col-xs-3" style="font-weight: 900;">Hora</div>
            <div class="col-xs-3" style="font-weight: 900;">{{ $payment->created_at->format("H:m:s") }}</div> --}}
        </div>
          <br>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Gastos Conservacion:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->conservation_expense }}
            </div>
        </div>
        <br>
        @if ($payment->type_payment == 'amortization')
            <div class="row">
                <div class="col-xs-5" style="font-weight: 900;font-size: 16px">AMORTIZACION:</div>
                <div class="col-xs-4" style="font-weight: 900;">
                    {{ $payment->amount }}
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-xs-5" style="font-weight: 900;">Fecha de vencimiento:</div>
                <div class="col-xs-4" style="font-weight: 900;">
                    {{ $payment->loan->date_contract_expiration->format('Y-m-d') }}
                </div>
            </div>
        @elseif ($payment->type_payment == 'complete')
            <div class="row">
                <div class="col-xs-5" style="font-weight: 900;font-size: 16px">TOTAL CANCELADO:</div>
                <div class="col-xs-4" style="font-weight: 900;">
                    {{ $payment->amount }}
                </div>
            </div>
            <br>
        @else
            <div class="row">
                <div class="col-xs-5" style="font-weight: 900; font-size: 16px">TOTAL:</div>
                <div class="col-xs-4" style="font-weight: 900;">
                    {{ strtoupper($payment->amount) }}
                </div>
            </div>
            <br>
        @endif
        {{-- <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Cod. Cliente:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->client->code }}
            </div>
        </div>
        <div class="row">
            <div class="col-xs-5" style="font-weight: 900;">Cod. Contrato:</div>
            <div class="col-xs-4" style="font-weight: 900;">
                {{ $payment->loan->code_contract }}
            </div>
        </div> --}}
        <br>
        <div class="row">
            <div class="col-xs-12"><b>*****************************************************************************************************************************</b>
            </div>
        </div>
        <br>
        <br>
        <div class="row text-center">
            <div class="col-xs-4 col-xs-offset-4"><small>..............................</small></div>
        </div>
        <div class="row text-center">
            <div class="col-xs-4 col-xs-offset-4"><small>FIRMA CLIENTE</small></div>
        </div>
        <br>
        <div class="row text-center title">
            <div class="col-xs-4 col-xs-offset-4"><small>Nombre: {{ $payment->loan->client->first_name.' '.$payment->loan->client->last_name }}</small></div>
        </div>
        <div class="row text-center title">
            <div class="col-xs-4 col-xs-offset-4"><small>CI: {{ $payment->loan->client->document }}</small></div>
        </div>
        <div class="row text-center title">
            <div class="col-xs-6 col-xs-offset-3"><small>Cel: {{ $payment->loan->client->phone }}</small></div>
        </div>
    </div>

    <style>
        .border {
            border: 1px solid #000;
        }

        .title {
            font-size: 20px;
            font-weight: 900;
        }
    </style>
@endsection
