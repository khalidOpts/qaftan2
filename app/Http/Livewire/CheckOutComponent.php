<?php

namespace App\Http\Livewire;

use Illuminate\Http\Request;
use Livewire\Component;
use App\Models\order;
use App\Models\orderItem;
use App\Models\shipping;
use App\Models\transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Cart;

use Srmklive\PayPal\Services\ExpressCheckout;

class CheckOutComponent extends Component
{
    public $ship_to_diffrent;
    public $firstname;
    public $lastname;
    public $email;
    public $mobile;
    public $line1;
    public $line2;
    public $city;
    public $province;
    public $country;
    public $zipcode;

    public $s_firstname;
    public $s_lastname;
    public $s_email;
    public $s_mobile;
    public $s_line1;
    public $s_line2;
    public $s_city;
    public $s_province;
    public $s_country;
    public $s_zipcode;
    
    
    public $paymentMode;
    public $thankyou;
    

    public $firstName;
    public $lastName;
    public $card_num;
    public $exp_month;
    public $exp_year;
    public $cvc;

    // protected $messages = [
    //     'email.required' => 'The Email Address cannot be empty.',
    //     'email.email' => 'The Email Address format is not valid.',
    // ];
    public function update($fields){
        $this->validateOnly($fields,[
            'firstname'=>'required',
            'lastname'=>'required',
            'email'=>'required|email',
            'mobile'=>'required|numeric',
            'line1'=>'required',
            'city'=>'required',
            'province'=>'required',
            'country'=>'required',
            'zipcode'=>'required',
        ]);

        if($this->ship_to_diffrent){
            $this->validateOnly($fields,[
                's_firstname'=>'required',
                's_lastname'=>'required',
                's_email'=>'required|email',
                's_mobile'=>'required|numeric',
                's_line1'=>'required',
                's_city'=>'required',
                's_province'=>'required',
                's_country'=>'required',
                's_zipcode'=>'required',
                'paymentMode'=>'required'   
            ]);
        }

        if($this->paymentMode == 'card'){
            $this->validateOnly($fields,[
                 'card_num'=>'required|numeric',
                 'exp_month'=>'required|numeric',
                 'exp_year'=>'required|numeric',
                 'cvc'=>'required|numeric',
                 'firstName'=>'required',
                 'lastName'=>'required'
            ]);
        }

    }
    function palceOrder(){
        $this->validate([
            'firstname'=>'required',
            'lastname'=>'required',
            'email'=>'required|email',
            'mobile'=>'required|numeric',
            'line1'=>'required',
            'city'=>'required',
            'province'=>'required',
            'country'=>'required',
            'zipcode'=>'required',
            'paymentMode'=>'required'
        ]);

        if($this->paymentMode == 'card'){
            $this->validate([
                 'card_num'=>'required|numeric',
                 'exp_month'=>'required|numeric',
                 'exp_year'=>'required|numeric',
                 'cvc'=>'required|numeric',
                 'firstName'=>'required',
                 'lastName'=>'required'
            ]);
        }


        $order = new order();
        $order->user_id = Auth::user()->id;
        $order->subtotal =session()->get('checkout')['subtotal'];
        $order->discount =session()->get('checkout')['discount'];
        $order->total =session()->get('checkout')['total'];

        $order->firstname =$this->firstname;
        $order->lastname =$this->lastname;
        $order->email =$this->email;
        $order->mobile =$this->mobile;
        $order->line1 =$this->line1;
        $order->line2 =$this->line2;
        $order->city =$this->city;
        $order->province =$this->province;
        $order->country =$this->country;
        $order->zipcode =$this->zipcode;
        $order->status ='ordered';
        $order->is_shipping_diffrent =$this->ship_to_diffrent ?1:0;
        $order->save();

        foreach(Cart::instance('cart')->content() as $item){
            $orderItem = new orderItem();
            $orderItem->product_id = $item->id;
            $orderItem->order_id = $order->id;
            $orderItem->price = $item->price;
            $orderItem->quantity = $item->qty;
            $orderItem->save();
        }

        if($this->ship_to_diffrent){
            $this->validate([
                's_firstname'=>'required',
                's_lastname'=>'required',
                's_email'=>'required|email',
                's_mobile'=>'required|numeric',
                's_line1'=>'required',
                's_city'=>'required',
                's_province'=>'required',
                's_country'=>'required',
                's_zipcode'=>'required',
            ]);
            $shipping = new shipping();
            $shipping->order_id = $order->id;
            $shipping->firstname =$this->s_firstname;
            $shipping->lastname =$this->s_lastname;
            $shipping->email =$this->s_email;
            $shipping->mobile =$this->s_mobile;
            $shipping->line1 =$this->s_line1;
            $shipping->line2 =$this->s_line2;
            $shipping->city =$this->s_city;
            $shipping->province =$this->s_province;
            $shipping->country =$this->s_country;
            $shipping->zipcode =$this->s_zipcode;
            $shipping->save();
        }

        if($this->paymentMode == 'cod'){
            $transaction = new transaction();
            $transaction->user_id  = Auth::user()->id; 
            $transaction->order_id  = $order->id; 
            $transaction->mode  = $this->paymentMode; 
            $transaction->status  = 'pending'; 
            $transaction->save();

            $this->thankyou=1;
            Cart::instance('cart')->destroy();
            session()->forget('checkout');
        }
        
        // start payPal transaction
    //    else if ($this->paymentMode == 'paypal') { 
      
    //     $data = [];
    //     $data['items'] =[
    //         [
    //             'name'=>'Apple',
    //             'price'=> $item->price,
    //             'description'=>'MackBook pro 14 inch',
    //             'qty'=>$item->qty
    //         ]
    //     ];
    //     $i=1;
    //     $data['invoice_id'] = $i++;
    //     $data['invoice_description'] ='Order Invoice';
    //     $data['return_url'] =route('pay.success');
    //     $data['cancel_url'] =route('pay.cancel');
    //     $data['total'] =$item->qty*$item->price;

    //     $provider = new ExpressCheckout;
    //     $response = $provider->setExpressCheckout($data);
    //     $response = $provider->setExpressCheckout($data,true);

    //     return redirect($response['paypal_link']);
    // }
        // $this->cancel();
        // $this->success();
    }

    // public function cancel(){
    //     dd('you cancelled this payment');
    // }
    // public function success(Request $request){
    //     $provider = new ExpressCheckout;
    //     $response = $provider->getExpressCheckoutDetails($request->token);

    //     if($response){
    //         // $this->thankyou=1;
    //         Cart::instance('cart')->destroy();
    //         session()->forget('checkout');
    //         return redirect()->route('thankyou');
    //     }else{
    //         return view('Livewire.error-component'); 
    //     }

    // }


   
    
        
        // end payPal transaction
       

   

  
        // }
        //  function restCart(){
        //     $this->thankyou=1;
        //     Cart::instance('cart')->destroy();
        //     session()->forget('checkout');
        //  }
        //  function makeTransaction($order_id,$status){
        //     $transaction = new transaction();
        //     $transaction->user_id  = Auth::user()->id; 
        //     $transaction->order_id  = $order_id; 
        //     $transaction->mode  = $this->paymentMode; 
        //     $transaction->status  = $status; 
        //     $transaction->save();
        //  }
         function verifyForCheckout(){
            if(!Auth::check()){
                return redirect()->route('login');
            }else if($this->thankyou){
                return redirect()->route('thankyou');
            }else if(!session()->get('checkout')){
                return redirect()->route('shop.cart'); 
            }
        }
        
    public function render()
    {
        $this->verifyForCheckout();
        $order = new order();
        return view('livewire.check-out-component',['order' => $order]);
    }
}
