<?php
namespace App\Http\Controllers;
use GuzzleHttp\Promise\RejectionException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use App\Models\Cartrige;
use App\Models\Printmodel;
use App\Models\Cartstorage;
use App\Models\Otdel;
use Throwable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
#use Barryvdh\DomPDF\Facade\Pdf;
use PDF;
use App;
#use Dompdf\Dompdf;
class CartStorageController extends Controller
{

public $repository;
public function __construct(Cartstorage $cartmodel){ $this->repository=$cartmodel;}

    public function cartstorage_mp(){

        return view('cartstoragemp');
    }
    

    public function regCart(){
	$cartReestr = Cartrige::orderBy('name')->get();
	$techReestr = Printmodel::orderBy('name')->get();
        return view('addcartmod', ['cartReestr'=>$cartReestr, 'techReestr'=>$techReestr]);
	
    }

    public function onFill(){
	$listOnFill = Cartstorage::where('act',1)->get();
//	$tab = view('onfill', ['listOnFill'=>$listOnFill]);
//	$pdf = PDF::loadView('onfill', compact($tab));
	return view('onfill', ['listOnFill'=>$listOnFill]);
	// $pdf = PDF::loadView('onfill', compact('listOnFill'));
//	    $pdf = PDF::loadView('pdf');
//	return response()->json($data);
//	dd($pdf);
//        return $pdf->download('act.pdf');
	//return $pdf->stream();
/*
$dompdf = new Dompdf();
$dompdf->loadHtml('hello world');
$dompdf->setPaper('A4', 'landscape');
$options = $dompdf->getOptions();
$options->setDefaultFont('Courier');
$dompdf->setOptions($options);
$dompdf->render();
$dompdf->stream();	
*/
}

    public function clearAct(){
	$listAct = Cartstorage::where('act',1)->get();
	foreach ($listAct as $one){
	    $one->act=0;
	    $one->disloc="склад";
	    $one->save();
	}
    }


    public function onStorage(){
	$listOnFill = Cartstorage::where('disloc','Склад')->get();
        return view('onfill', ['listOnFill'=>$listOnFill]);
	
    }
/*
    public function onSumm(){
	$reestr = Cartrige::all();
	foreach ($reestr as $cart){
	$summ = Cartstorage::where('id_name',$cart->id)->count();
	$list = Arr:add($cart->printName->name => $summ);
	}
        return view('onfill', ['list'=>$list]);
	
    }
*/

    public function getCart(){
        return view('givecartmod');
	
    }

    public function fillCart(){
        return view('fillcartmod');
	
    }

    public function workCart(){
	$otdReestr = Otdel::orderBy('otd_name')->get();
        return view('workcartmod', ['otdReestr'=>$otdReestr]);
	
    }

    public function killCart(){
        return view('killcart');
	
    }

    public function regNewCart(Request $req){
	
try{    
    
    if($req->proc == 'registration'){
        $newCart = Cartstorage::where('sh_code', $req->sh_code)->first();
       if($newCart){
	  return "existscart";
        }
	$newCart = new Cartstorage();
	$newCart->id_name = $req->id_name;
	$newCart->id_mod = $req->id_print;
	$newCart->sh_code = $req->sh_code;
	$newCart->status = 1;
	$newCart->disloc = "Склад";
	$newCart->cin = 0;
	$newCart->save();
      
    }
}catch(Throwable $e){

    return response()->json([
          'error' => [
          'code' => $e->getCode(),
          'message' => $e->getMessage(),
          ]
        ], 400);
}

    if($req->proc == 'give'){
    $newCart = Cartstorage::where('sh_code', $req->sh_code)->first();
    if(!$newCart){
	return "nullcart";
    }
    if($newCart->disloc=="Склад"){
	return "errstor";	
     }else{
	$newCart->disloc = "Cклад";
	$newCart->save();
	}
    }

    if($req->proc == 'fill'){
   $newCart = Cartstorage::where('sh_code', $req->sh_code)->first();
        if(!$newCart){
	return "nullcart";
    }
    if($newCart->disloc=="на заправке"){
	return "errfill";	
     }else{

	$newCart->disloc = "на заправке";
	$newCart->cin += 1;
	$newCart->act=1;
	$newCart->save();
     }
    }

    if($req->proc == 'work'){
	$newCart = Cartstorage::where('sh_code', $req->sh_code)->first();
    if(!$newCart){
	return "nullcart";
    }
	$newCart->disloc = $req->otd;
	$newCart->save();
    }

    if($req->proc == 'kill'){
	$newCart = Cartstorage::where('sh_code', $req->sh_code)->first();
	    if(!$newCart){
	return "nullcart";
    }
	$newCart->status = 0;
	$newCart->disloc = "rip";
	$newCart->save();
    }


}


}
