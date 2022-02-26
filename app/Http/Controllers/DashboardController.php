<?php

namespace App\Http\Controllers;

use App\Services\SallaAuthService;
use Illuminate\Support\Facades\Auth;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;

class DashboardController extends Controller
{
    /**
     * @var SallaAuthService
     */
    private $salla;

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
                return redirect()->route('oauth.redirect');
            }

            // let's get the store details to show it
            $store = $this->salla->getStoreDetail();

            // let's get the product of store via salla service
                $products = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/products')['data'];
                 $orders = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders?page = 1 ')['data'];
                 $number =  $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders?page')['pagination']['totalPages'];

                 $orders2 = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders?page = 2 ')['data'];
                 $list = array();
                 for ($i=1 ; $i<=$number ; $i++){
                 array_push($list, $this->salla->request('GET', 'https://api.salla.dev/admin/v2/orders?page= '.$i)['data']);
                 }

                return response()->json(['orders'=>$list]
                );
             
            //    $data = $this->salla->request('GET', 'https://accounts.salla.sa/oauth2/user/info')['data'];
            //   $categroes = $this->salla->request('GET', 'https://api.salla.dev/admin/v2/categoriess')['data'];
            // return   $customers = $this->salla->request('GET', 'https://api.salla.dev/admin/v2 /customers');
            // return response()->json([
            //     'order' => $orders
              
            // ]);
                 


              



              

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
}
