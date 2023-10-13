@extends('pdf.layout.main')
@section('content')
    <br>
    <div class="w-100">
        <div class="text-white p-2 rounded text-center text-uppercase">
            DETALLES DE GARANTIA
        </div>
        <br>
        <div class="text-white p-2 rounded text-center text-uppercase">
            (Expreado en Bolivianos)
        </div>
        <br>
        <br>
        <br>
        <br>
        <div class="w-100">
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            Fecha
                        </th>
                        <th class="text-center font-bold py-3">
                            Fecha Inicio
                        </th>
                        <th class="text-center font-bold py-3">
                            Vencimiento
                        </th>
                        <th class="text-center font-bold py-3">
                            Nro Contrato
                        </th>
                        <th class="text-center font-bold py-3">
                            Cod Cliente
                        </th>
                        <th class="text-center font-bold py-3">
                            Codigo
                        </th>
                    </tr>
                </thead>
                <tbody>
                        <tr>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->created_at }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->date_contract }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->date_contract_expiration }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->code_contract }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->client->code }}
                            </td>
                            <td class="text-center py-3 text-sm md:text-lg">
                                {{ $loan->code }}
                            </td>
                        </tr>
                </tbody>
            </table>
            <br>
            <br>
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            Tasa de Interes
                        </th>
                        <th class="text-center font-bold py-3">
                            Interes
                        </th>
                        <th class="text-center font-bold py-3">
                            Gastos de Conservacion
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->interest_rate }}%
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->legal_interest }}%
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->conservation_expense }}%
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <br>
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            Cliente
                        </th>
                        <th class="text-center font-bold py-3">
                            Domicilio
                        </th>
                        <th class="text-center font-bold py-3">
                            CI
                        </th>
                        <th class="text-center font-bold py-3">
                            Telefono
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->client->full_name }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->client->address }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->client->code }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->client->phone }}
                        </td>
                    </tr>
                </tbody>
            </table>
<br>
<br>
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            Moneda
                        </th>
                        <th class="text-center font-bold py-3">
                            Valor Acordado
                        </th>
                        <th class="text-center font-bold py-3">
                            Usuario
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->currency }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->balance_pay }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->user->name }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <br>
            <br>
            <br>
            <br>
            <br>
            <br>
            <table class="table table-bordered">
                <thead class="border-b">
                    <tr>
                        <th class="text-center font-bold py-3">
                            REPRESENTANTE LEGAL <br>
                            E.FECTIVA S.R.L. <br>
                            ACREEDOR
                        </th>
                        <th class="text-center font-normal py-3">
                           Cliente <br>
                           CI <br>
                           Telefono
                        </th>

                    </tr>
                </thead>
                {{-- <tbody>
                    <tr>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->currency }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->balance_pay }}
                        </td>
                        <td class="text-center py-3 text-sm md:text-lg">
                            {{ $loan->user->name }}
                        </td>
                    </tr>
                </tbody> --}}
            </table>
        </div>
    </div>
@endsection
