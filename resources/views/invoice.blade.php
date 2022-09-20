<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <title> Invoice ## {{$id}} </title>
  <style>
    .clearfix:after {
      content: "";
      display: table;
      clear: both;
    }

    a {
      color: #5D6975;
      text-decoration: underline;
    }

    body {
      position: relative;
      width: 21cm;
      margin: 0 auto;
      color: #001028;
      background: #FFFFFF;
      font-family: Arial, sans-serif;
      font-size: 12px;
      font-family: Arial;
    }

    header {
      padding: 10px 0;
      margin-bottom: 30px;
    }

    h1 {
      border-top: 1px solid #5D6975;
      border-bottom: 1px solid #5D6975;
      color: #5D6975;
      font-size: 2.4em;
      line-height: 1.4em;
      font-weight: normal;
      text-align: center;
      margin: 0 0 20px 0;
    }

    #project {
      float: left;
    }

    #project span {
      color: #5D6975;
      text-align: right;
      width: 52px;
      margin-right: 10px;
      display: inline-block;
      font-size: 0.8em;
    }

    #company {
      float: right;
      text-align: right;
    }

    #project div,
    #company div {
      white-space: nowrap;
    }

    table {
      width: 100%;
      border-collapse: collapse;
      border-spacing: 0;
      margin-bottom: 20px;
    }

    table tr:nth-child(2n-1) td {
      background: #F5F5F5;
    }

    table th,
    table td {
      text-align: center;
    }

    table th {
      padding: 5px 20px;
      color: #5D6975;
      border-bottom: 1px solid #C1CED9;
      white-space: nowrap;
      font-weight: normal;
    }

    table .service,
    table .desc {
      text-align: left;
    }

    table td {
      padding: 20px;
      text-align: right;
    }

    table td.service,
    table td.desc {
      vertical-align: top;
    }

    table td.unit,
    table td.qty,
    table td.total {
      font-size: 1.2em;
    }

    table td.grand {
      border-top: 1px solid #5D6975;
    }

    #notices .notice {
      color: #5D6975;
      font-size: 1.2em;
    }

    footer {
      color: #5D6975;
      width: 100%;
      /* height: 30px;
      position: absolute;
      bottom: 0; */
      border-top: 1px solid #C1CED9;
      padding: 8px 0;
      text-align: center;
    }

    @media print {
      * {
        -webkit-print-color-adjust: exact;
      }

      html {
        background: none;
        padding: 0;
      }

      body {
        box-shadow: none;
        margin: 0;
      }

      span:empty {
        display: none;
      }

      .add,
      .cut {
        display: none;
      }
    }

    @page {
      margin: 0;
    }

    @media print {
      .hideMe {
        display: none;
      }
    }

    .noPrint {
      display: flex;
      flex-direction: row;
      justify-content: center;
      align-items: center;
      margin: 20px 0px;
    }
  </style>
</head>

<body>
  <header class="clearfix">
    <div class="noPrint">
      <button onclick="window.print();" class="hideMe" style="padding: 10px;border-radius: 5px;; cursor: pointer;">
        Print Invoice
      </button>
    </div>
    <h1>INVOICE  #{{$id}} </h1>
    <div id="company" class="clearfix">
      <div> {{$general->store_name}} </div>
      <div> {{$general->address}} ,<br /> {{$general->city}} , {{$general->state}}, {{$general->zip}} </div>
      <div> {{$general->mobile}} </div>
      <div><a href="mailto:{{$general->email}} ">{{$general->email}}</a></div>
    </div>
    <div id="project">
      <div><span>User </span>  {{$data->user_first_name}}  {{$data->user_last_name}} </div>
      @if($delivery_address !='')
        <div><span>ADDRESS</span>  {{$delivery_address->address}} </div>
      @endif
      <div><span>EMAIL</span> <a href="mailto:{{$data->user_email}}"> {{$data->user_email}} </a></div>
      <div><span>DATE</span>  {{$data->dateTime}} </div>
      <div><span>MOBILE</span>  {{$data->user_mobile}} </div>
    </div>
  </header>
  <main>
    <table>
      <thead>
        <tr>
          <th class="desc">Product</th>
          <th>PRICE</th>
          <th>QTY</th>
        </tr>
      </thead>
      <tbody>
      @foreach($data->orders as $order)
        @if($order->size == '1' )
            <tr>
                <td class="desc">
                @if($order->extra_fields && $order->extra_fields->variations && !empty($order->extra_fields->variations))
                    {{$order->title}} - {{$order->extra_fields->variations[0]->items[$order->variants]->title}}
                @else
                    {{$order->title}}
                @endif
                </td>
                <td class="unit">
                    @if($order->extra_fields && $order->extra_fields->variations && !empty($order->extra_fields->variations))
                        @if($order->extra_fields->variations[0]->items[$order->variants]->discount > 0)
                            $ {{$order->extra_fields->variations[0]->items[$order->variants]->discount}}
                        @endif

                        @if($order->extra_fields->variations[0]->items[$order->variants]->discount <= 0)
                            $ {{$order->extra_fields->variations[0]->items[$order->variants]->price}}
                        @endif

                    @endif
                </td>
                <td class="qty"> X {{$order->quantiy}} </td>
            </tr>
		@endif


        @if($order->size == '0' )
            <tr>
                <td class="desc">
                {{$order->title}}
                </td>
                <td class="unit">
                    @if($order->discount > 0)
                        {{$order->sell_price}}
                    @endif
                    @if($order->discount <= 0)
                        {{$order->real_price}}
                    @endif
                </td>
                <td class="qty"> X {{$order->quantiy}} </td>
            </tr>
		@endif

		@endforeach
        <tr>
          <td colspan="2">SUBTOTAL</td>
          <td class="total">$ {{$data->total}} </td>
        </tr>
        <tr>
          <td colspan="2">DELIVERY CHARGE</td>
          <td class="total">$ {{$data->delivery_charge}} </td>
        </tr>
        <tr>
          <td colspan="2">TAX  {{$general->tax}}% </td>
          <td class="total">${{$data->serviceTax}} </td>
        </tr>
        <tr>
          <td colspan="2">DISCOUNT</td>
          <td class="total">- $ {{$data->discount}} </td>
        </tr>
        <tr>
          <td colspan="2" class="grand total">GRAND TOTAL</td>
          <td class="grand total">$ {{$data->grand_total}} </td>
        </tr>
      </tbody>
    </table>

  </main>
  <footer>
    Invoice was created on a computer and is valid without the signature and seal.
  </footer>
</body>

</html>
