@extends('mails.layouts')

@section('style')
    <style>
        .card-header {
            background-color: #7fad39;
        }

        .ml-16 {
            margin-left: 4rem
        }

        .payment-details {
            font-family: monospace;
            font-style: italic;
            width: 90%;
            margin: auto;
            text-align: center;
        }

        .pl-4 {
            padding-left: 4rem
        }
    </style>
@endsection

@section('content')
    <div class="container mt-4">
        <div class="card" style="width: 100%;">
            <h1 class="card-header text-center text-white p-3">
                Rappel de commande non traitée pour {{ $data->user->first_name }} </h1>
            <div class="container pt-4">
                <div>
                    Bonjour, pour rappel, vous avez une commande pas encore traitée :
                </div>

                <p class="mt-4">
                    <strong>Numero de commande : </strong> {{ $data->id }}
                </p>
                <p>
                    <strong>Date de facturation : </strong> {{ $data->date_time }}
                </p>
                <p>
                    <strong>Choix de livraison : </strong> {{$data->delivery_option}}
                </p>
            </div>
            <table class="payment-details mb-4">
                <thead class="font-weight-bold">
                    <tr>
                        <th></th>
                        <th>Nom du produit</th>
                        <th>Prix par unité</th>
                        <th>Quantité</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($data->orders as $product)
                        <tr>
                            <td></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ $product->original_price }} USD</td>
                            <td>{{ $product->quantity }}</td>
                            <td>{{ $product->original_price * $product->quantity }} USD</td>
                        </tr>
                    @endforeach
                    {{-- <tr>
                        <td></td>
                        <td>Nom</td>
                        <td>2000 MGA</td>
                        <td>1</td>
                        <td>2000 MGA</td>
                    </tr> --}}
                    <tr class="font-weight-bold">
                        <td>Type de livraison</td>
                        <td>{{ $data->type_receive }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $data->delivery_charge }} USD</td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $data->grand_total }} USD</td>
                    </tr>
                </tbody>
            </table>
            <div class="container pb-4">
                <strong>Address de livraison</strong>
                <div class="d-flex flex-column pl-4">
                    <span>{{ $user->first_name }}</span>
                    {{-- <span>Lorem ipsum dolor, sit amet consectetur </span>
                    <span>Lorem, ipsum.</span>
                    <span>Lorem ipsum dolor sit.</span> --}}
                </div>

            </div>
        </div>

    </div>
@endsection
