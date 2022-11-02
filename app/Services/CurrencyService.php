<?php
namespace App\Services;

use App\Models\Countrie;
use App\Models\Currency;
use Carbon\Carbon;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class CurrencyService
{
    public function gelAllCurrency(){
        // $currency = Currency::with('countrie')->get();
        $country = Countrie::with('otherCurrency')->get();
        $updated_at = $country[0]['otherCurrency']->updated_at ;
        return [
            'data' => $country,
            'all_updated_at'=>Carbon::parse($updated_at)->toDateTimeString(),
            'status' => 200
        ];
    }

    public function updateCurrency(){
        $countries = Countrie::get();
        foreach ($countries as $countrie) {
            if ($countrie->currency == 'MGA') {
              $euro = $this->get('MGA','EUR');
              $usd = $this->get('MGA','USD');
              $countrie->otherCurrency()->update([
                'Mga' => 1,
                'Dollard' => $usd,
                'Euro' => $euro,
              ]);
            }


            if ($countrie->currency == '€') {
                $mga = $this->get('EUR','MGA');
                $usd = $this->get('EUR','USD');
                $countrie->otherCurrency()->update([
                    'Mga' => $mga,
                    'Dollard' => $usd,
                    'Euro' => 1,
                ]);
            }

            if ($countrie->currency == '$') {
                $mga = $this->get('USD','MGA');
                $euro = $this->get('USD','EUR');
                $countrie->otherCurrency()->update([
                    'Mga' => $mga,
                    'Dollard' => 1,
                    'Euro' => $euro,
                ]);
            }
          
        }
        
        $currency = Currency::first();
        $updated = Carbon::parse($currency->updated_at);
        return [
            'updated_at' => $updated->diffForHumans(),
            'data' => Countrie::with('otherCurrency')->get(),
            'status' => 201
        ];
    }

    public function get($from,$to)
    {
        $headers = [
            'apikey' => 'wnSikWoFTF8UQKOD0U124pnKfilwHTV0'
        ];
        $client = new Client();
        $request = new Request('GET', 'https://api.apilayer.com/exchangerates_data/convert?from='.$from.'&to='.$to.'&amount=1', $headers);
        $res = $client->sendAsync($request)->wait();

        if ($res) {
            $res = json_decode($res->getBody(),true)['result'];
            return $res;
        }
    }
}
