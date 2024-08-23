<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use GuzzleHttp\Client;

class FlutterWaveLog extends Model
{
    use HasFactory;

    public function is_order_paid()
    {

        if ($this->status == 'Paid') {
            return 1;
        }
        $tx_ref = $this->uuid;
        
        // Create a new Guzzle client instance
        $client = new Client();

        // Specify the URL you want to send the request to
        $url = 'https://api.flutterwave.com/v3/transactions/verify_by_reference?tx_ref=' . $tx_ref;

        // Specify the headers you want to include in the request
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer FLWSECK-4131a12ce00186825da8070013bef461-18bda11a003vt-X',
            // Add any other headers as needed
        ];


        // Make the HTTP POST request with the specified parameters
        $response = $client->get($url, [
            'headers' => $headers,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // You can now work with the response as needed
        // For example, you might decode the JSON response:
        $parsedResponse = json_decode($responseBody, true);

        if ($parsedResponse == null) {
            throw new \Exception('Error Processing Request', 1);
        }

        $status = 0;
        $payment_link = '';
        if (isset($parsedResponse['status'])) {
            $status = strtolower($parsedResponse['status']);
            if ($status == 'success') {
                if (isset($parsedResponse['data'])) {
                    if (isset($parsedResponse['data']['status'])) {
                        $status = $parsedResponse['data']['status'];
                        if ($status == 'successful') {
                            $this->flutterwave_payment_data = json_encode($parsedResponse['data']);
                            $this->flutterwave_reference = null;
                            $this->status = 'Paid';
                            $this->save();
                            $status = 1;
                        }
                    }
                }
            }
        }
        if ($status == 1) {
            $consultation = Consultation::find($this->consultation_id);
            if ($consultation != null) {
                $paymentRecord = new PaymentRecord();
                $paymentRecord->consultation_id = $consultation->id;
                $paymentRecord->description = 'Consultation payment for ' . $consultation->name_text;
                $paymentRecord->amount_payable = $consultation->total_due;
                $amount = $this->flutterwave_payment_amount;
                $paymentRecord->amount_paid = $this->flutterwave_payment_amount;
                $paymentRecord->balance = $consultation->total_due - $amount;
                $paymentRecord->payment_date = Carbon::now();
                $paymentRecord->payment_time = Carbon::now();
                $paymentRecord->payment_method = 'Flutterwave';
                $paymentRecord->payment_reference = rand(100000, 999999) . rand(100000, 999999);
                $paymentRecord->payment_status = 'Success';
                $paymentRecord->payment_remarks = 'Payment through mobile money.';
                $paymentRecord->payment_phone_number = $this->flutterwave_payment_customer_phone_number;
                $paymentRecord->payment_channel = 'Mobile App';
                $paymentRecord->created_by_id = $consultation->created_by_id;
                $paymentRecord->cash_receipt_number = $paymentRecord->payment_reference;
                $paymentRecord->save();
            }

            /* 
            consultation_id					


            */
        }
        return $status;
    }


    public function generate_payment_link(
        $phone_number,
        $phone_number_type,
        $amount,
        $uuid
    ) {
        $this->flutterwave_payment_amount = $amount;
        $ip = $_SERVER['REMOTE_ADDR'];
        $data['tx_ref'] = $uuid;
        $data['voucher'] = $uuid;
        $data['amount'] = $amount;
        $data['currency'] = 'UGX';
        $data['network'] = $phone_number_type;
        $data['email'] = 'mubahood360@gmail.com';
        $data['phone_number'] = $phone_number;
        $data['fullname'] = $this->name;
        $data['client_ip'] = $ip;
        $data['device_fingerprint'] = '62wd23423rq324323qew1';
        $data['meta'] = json_encode($this);

        // Create a new Guzzle client instance
        $client = new Client();

        // Specify the URL you want to send the request to
        $url = 'https://api.flutterwave.com/v3/charges?type=mobile_money_uganda';

        // Specify the headers you want to include in the request
        $headers = [
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer FLWSECK-4131a12ce00186825da8070013bef461-18bda11a003vt-X',
            // Add any other headers as needed
        ];

        // Specify the raw body content
        $body = json_encode($data);

        // Make the HTTP POST request with the specified parameters
        $response = $client->post($url, [
            'headers' => $headers,
            'body' => $body,
        ]);

        // Get the response body as a string
        $responseBody = $response->getBody()->getContents();

        // You can now work with the response as needed
        // For example, you might decode the JSON response:
        $parsedResponse = json_decode($responseBody, true);

        if ($parsedResponse == null) {
            throw new \Exception('Error Processing Request', 1);
        }
        $payment_link = '';
        if (isset($parsedResponse['meta'])) {
            if (isset($parsedResponse['meta']['authorization'])) {
                if (isset($parsedResponse['meta']['authorization']['redirect'])) {
                    $payment_link = $parsedResponse['meta']['authorization']['redirect'];
                }
            }
        }
        return $payment_link;
    }
}
