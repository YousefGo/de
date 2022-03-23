<?php

namespace App\Http\Controllers;

use App\Services\SallaAuthService;
use Facade\FlareClient\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\URL;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use function PHPUnit\Framework\isNull;

class DashboardController extends Controller
{
    /**
     * @var SallaAuthService
     */
    private $salla;
    private $data = array();

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(SallaAuthService $salla)
    {
        $this->middleware('auth');
        $this->salla = $salla;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable|\Illuminate\Http\RedirectResponse
     * @throws IdentityProviderException
     */
    public function __invoke()
    {
        $products = [];
        $store = null;
        if (auth()->user()->token) {
            // set the access token to our service
            // you can load the user profile from your database in your app
            $this->salla->forUser(auth()->user());
        
            // you need always to check the token before made a request
            // If the token expired, lets request a new one and save it to the database
            try {
                $this->salla->getNewAccessToken();
            } catch (IdentityProviderException $exception) {
                // in case the token access token & refresh token is expired
                // lets redirect the user again to Salla authorization service to get a new token
                dd( $exception);
                return redirect()->route('oauth.redirect');
            }

            // let's get the store details to show it
            $store = $this->salla->getStoreDetail();

           
            
                   $file = fopen("ids.csv","r");
                         $t = array();
                         while(! feof($file))
                         {
                         array_push($t,fgetcsv($file)) ;
                         }

                         fclose($file);
                        //   $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders/'.$t[0][0]);
                         $len =  count($t)-1;
                        //   print_r($t[23029][0]);
                        // // // loop through the array
                         for ($i=0 ; $i<$len ; $i++){
                        // // echo ($i+1).".". $t[$i][0]."<br>";
                        $key = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strval($t[$i][0]));

                      $data =    $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders/'.$key );
                     
                          array_push($this->data,$data['data']);
                         }

                        
                               $fileName = 'orderDeatilsInfo10.csv';


                  $headers = array(
                      "Content-type"        => "text/csv",
                      "Content-Disposition" => "attachment; filename=$fileName",
                      "Pragma"              => "no-cache",
                      "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                      "Expires"             => "0"
                  );
                  $columns = array('id', 'reference_id', 'status', 'payment_method','sub_total_amount','shipping_cost_amount'
                  ,'cash_on_delivery_amount','tax_percent','tax_amount_amount');
                
        
                $callback = function() use( $columns) {
                    $file = fopen('orderDeatilsInfoTest10Sample.csv', 'w');
                    fputcsv($file, $columns);
                     
                         for( $i=0 ; $i<count($this->data);$i++){

                            $row['id'] =   $this->data[0]['id'];
                            $row['reference_id'] =   $this->data[0]['reference_id'];
                            $row['status'] =   $this->data[0]['status']['name'];

                            $row['payment_method'] =   $this->data[0]['payment_method'];
                            $row['sub_total_amount'] =   $this->data[0]['amounts']['sub_total']['amount'];
                            $row['shipping_cost_amount'] =    $this->data[0]['amounts']['shipping_cost']['amount'];

                            $row['cash_on_delivery_amount'] =   $this->data[0]['amounts']['cash_on_delivery']['amount'];
                            $row['tax_percent'] =   $this->data[0]['amounts']['tax']['percent']; //tax_percent
                            $row['tax_amount'] =   $this->data[0]['amounts']['tax']['amount']['amount'];
                           


                            fputcsv($file, array($row['id'], $row['reference_id'], $row['status'],
                            $row['payment_method'], $row['sub_total_amount'], $row['shipping_cost_amount'],
                            $row['cash_on_delivery_amount'], $row['tax_percent'], $row['tax_amount_amount'],));
                         }
                    
                          

                           
                         
                     
                    fclose($file);
                };

        //      dd( $num);
            //  return  $var = $num['data'][0]['items'][0]['name'].'&&'.$num['data'][0]['items'][1]['name'];
            //   return response()->json(['number'=>$num['data'][0]['items'][0]['name']]);

    //   return       $order = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/customers');
                // $data = array();
                // $row = array();
                // $flag = true; 
                // $countr = 501 ;
                //  while($flag){

                //    $order = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders?page='.$countr);
                //     if($order === "empty"){
                //         $countr = $countr+1;
                //      continue ;
                //     }
                //     array_push($this->data,$order['data']);
                   
                //     if (!array_key_exists('next',$order['pagination']['links']) || $countr ==1000  ) {
                //         $flag = false;
                //     }
                  
                //     $countr = $countr+1;
                //     sleep(1);
                 
                //  }//
            
                


                //     while($flag){
                //     $customers = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/customers?page='.$countr);
                //     array_push($this->data,$customers['data']);
                //    // if there are not next element in json ojbect stop
                //     if (!array_key_exists('next',$customers['pagination']['links']) ||$countr==10 ) {
                //         $flag = false;
                //     }
                //     $countr = $countr+1;
                //     if ($countr %5 ==0){
                //         sleep(1);
                //     }
                //    }//
            // return response()->json([
            //     'date'=>$this->data
            // ]);
                
                //           $fileName = 'customers.csv';


                //   $headers = array(
                //       "Content-type"        => "text/csv",
                //       "Content-Disposition" => "attachment; filename=$fileName",
                //       "Pragma"              => "no-cache",
                //       "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                //       "Expires"             => "0"
                //   );
                //   $columns = array('id', 'first_name', 'last_name', 'mobile', 'mobile_code','email','gender'
                //   ,'city','country','location','updated_at');
                
        
                // $callback = function() use( $columns) {
                //     $file = fopen('customers3000_rest.csv', 'w');
                //     fputcsv($file, $columns);
                     
                //     for($i=0 ; $i<count($this->data);$i++){
                //         for($j=0 ; $j<count($this->data[$i]) ;$j++){
                         
                //             $row['id'] =   $this->data[$i][$j]['id'];
                //             $row['first_name'] =    $this->data[$i][$j]['first_name'];
                //             $row['last_name'] =    $this->data[$i][$j]['last_name'];

                            
                //             $row['mobile'] =    $this->data[$i][$j]['mobile'];

                //             $row['mobile_code'] =    $this->data[$i][$j]['mobile_code'];
                //             $row['email'] =    $this->data[$i][$j]['email'];
                //             $row['gender'] =    $this->data[$i][$j]['gender'];

                //             $row['city'] =    $this->data[$i][$j]['city'];
                //             $row['country'] =    $this->data[$i][$j]['country'];

                //             $row['location'] =    $this->data[$i][$j]['location'];
                //             $row['updated_at'] =    $this->data[$i][$j]['updated_at']['date'];


                //             fputcsv($file, array($row['id'], $row['first_name'], $row['last_name'],
                //             $row['mobile'], $row['mobile_code'],$row['email'],$row['gender'],
                //             //  $row['birthday'],
                //              $row['city'],
                //              $row['country'],
                //              $row['location'],
                //              $row['updated_at'],
                //             ));
                //         }
                        
                //     }
                //     fclose($file);
                // };
                 

      
        
                        // $columns = array('id', 'first_name', 'last_name', 'mobile', 'mobile_code','email','gender'      ,'birthday','city','country');


                    //      $file = fopen("ids.csv","r");
                    //      $t = array();
                    //      while(! feof($file))
                    //      {
                    //      array_push($t,fgetcsv($file)) ;
                    //      }

                    //      fclose($file);
                    //     //   $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders/'.$t[0][0]);
                    //      $len =  count($t)-1;
                    //     //   print_r($t[23029][0]);
                    //     // // // loop through the array
                    //      for ($i=0 ; $i<$len ; $i++){
                    //     // // echo ($i+1).".". $t[$i][0]."<br>";
                    //     $key = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strval($t[$i][0]));

                    //   $data =    $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders/'.$key );
                     
                    //       array_push($this->data,$data['data']);
                    //      }
                    // //    return  $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders/1093113851');
                    //    //1093113851
  
                    //   return response(["data"=>$this->data]);



        // $fileName = 'ordersTest.csv';


        //           $headers = array(
        //               "Content-type"        => "text/csv",
        //               "Content-Disposition" => "attachment; filename=$fileName",
        //               "Pragma"              => "no-cache",
        //               "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
        //               "Expires"             => "0"
        //           );
        //           $columns = array('id', 'reference_id','items','amount', 'currency', 'date','status','can_cancel');
                
        
        //         $callback = function() use( $columns) {
        //             $file = fopen('orders1.csv', 'w');
        //             fputcsv($file, $columns);
                     
        //             for($i=0 ; $i<count($this->data);$i++){
        //                 for($j=0 ; $j<count($this->data[$i]) ;$j++){
        //                     $row['items'] = ' ';
        //                     $row['id'] =   $this->data[$i][$j]['id'];
        //                     $row['reference_id'] =    $this->data[$i][$j]['reference_id'];
        //                     for($k=0 ; $k<count($this->data[$i][$j]['items']);$k++){
        //                         $row['items'] = $row['items'] .($k+1).'No'. $this->data[$i][$j]['items'][$k]['name']
        //                         ."Qyt".$this->data[$i][$j]['items'][$k]['quantity'].".";
        //                     }
                           
        //                     $row['amount'] =    $this->data[$i][$j]['total']['amount'];
        //                     $row['currency'] =    $this->data[$i][$j]['total']['currency'];
        //                     $row['date'] =    $this->data[$i][$j]['date']['date'];
        //                     $row['status'] =    $this->data[$i][$j]['status']['name'];
        //                     $row['can_cancel'] =    $this->data[$i][$j]['can_cancel']; 

        //                     fputcsv($file, array($row['id'], $row['reference_id'], $row['items'], $row['amount'],
        //                     $row['currency'], $row['date'],$row['status'],$row['can_cancel']));
        //                 }
                        
        //             }
                                
        
        //             fclose($file);

        //         };
            //   return   response()->stream($callback, 200, $headers);
            return   response()->stream($callback, 200, $headers);

               
             



                            
             
             
            //    $data = $this->salla->request('GET', 'https://accounts.salla.sa/oauth2/user/info')['data'];
            //   $categroes = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/categoriess')['data'];
            // return   $customers = $this->salla->request('GET', 'https://api.salla.dev/admin/v2 /customers');
           
                 


              



      
            /**
             * Or you can use Http client of laravel to get the products
             */
            //$response = Http::asJson()->withToken($this->salla->getToken()->access_token)
            //    ->get('https://api.salla.dev/admin/v2/products');

            //if ($response->status() === 200) {
            //    $products = $response->json()['data'];
            //}
        }
        return view('dashboard', [
            // get the first 8 products from the response
            'products' => array_slice($products, 0, min(8, count($products))),
            'store'    => $store 
        ]);
    }
    public function GenerateOrderDetail(){
        
    }
    }

