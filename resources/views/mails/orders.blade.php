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
                Nous vous remercions pour votre commande </h1>
            <div class="container pt-4">
                <p>
                    Bonjour {{$user->first_name}},
                </p>

                <p>
                    Pour information, nous avons reçu votre commande n° : {{$detailPaiment->id}}. Elle est maintenant en cours de traitement :
                </p>

                <p>
                    <strong>Numero de commande : </strong> {{$detailPaiment->id}}
                </p>
                <p>
                    <strong>Date de facturation : </strong> {{$detailPaiment->created_at}}
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
					@foreach ($detailPaiment->orderUser as $orderUser)
						<tr>
							<td></td>
							<td>{{$orderUser->product->name}}</td>
							<td>{{$orderUser->product->priceLocale}} {{ $currency }}</td>
							<td>{{$orderUser->quantity}}</td>
							<td>{{$orderUser->product->priceLocale}} {{ $currency }}</td>
						</tr>
					@endforeach
                    
                    <tr class="font-weight-bold">
                        <td>Type de livraison</td>
                        <td>{{ $detailPaiment->type_receive }}</td>
                        <td></td>
                        <td></td>
                        <td>{{ $detailPaiment->type_receive == 'standard' ? 20 : 45 }} {{ $currency }}</td>
                    </tr>
                    <tr class="font-weight-bold">
                        <td>Total</td>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td>{{ $detailPaiment->totalLocal}} {{ $currency }}</td>
                    </tr>
                </tbody>
            </table>
            <div class="container pb-4">
                <strong>Address de livraison</strong>
                <div class="d-flex flex-column pl-4">
                    <span>{{$user->first_name}}</span>
                    
                </div>

            </div>
        </div>

    </div>
@endsection
