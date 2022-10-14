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
                Rappel de commande non traitée pour {{ $userInRappel->first_name }} </h1>
            <div class="container pt-4">
                <div>
                    Bonjour, pour rappel, vous avez une commande pas encore traitée :
                </div>

                <p class="mt-4">
                    <strong>Numero de commande : </strong> {{ $detailPaiment->id }}
                </p>
                <p>
                    <strong>Date de facturation : </strong> {{ $detailPaiment->created_at }}
                </p>
                <p>
                    <strong>Choix de livraison : </strong> {{$detailPaiment->delivery_option}}
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
                    @foreach ($detailPaiment->orderStore as $orderStore)
                        <tr>
                            <td></td>
                            <td>{{ $orderStore->product->name }}</td>
                            <td>{{ $orderStore->product->original_price }} {{$orderStore->product->store->countrie->currency}}</td>
                            <td>{{ $orderStore->quantity }}</td>
                            <td>{{ $orderStore->total }} {{$orderStore->product->store->countrie->currency}}</td>
                        </tr>
                    @endforeach
                  
                    <tr class="font-weight-bold">
                        <td>Type de livraison</td>
                        <td>{{ $detailPaiment->type_receive }}</td>
                        <td></td>
                        <td></td>
                        <td>a test USD</td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $detailPaiment->grand_total }} {{$detailPaiment->orderStore[0]->product->store->countrie->currency}}</td>
                    </tr>
                </tbody>
            </table>
            <div class="container pb-4">
                <strong>Address de livraison</strong>
                <div class="d-flex flex-column pl-4">
                    <span>{{ $userInRappel->first_name }}</span>
                    {{-- <span>Lorem ipsum dolor, sit amet consectetur </span>
                    <span>Lorem, ipsum.</span>
                    <span>Lorem ipsum dolor sit.</span> --}}
                </div>

            </div>
        </div>

    </div>
@endsection
