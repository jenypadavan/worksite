<?php

namespace App\Http\Controllers;

use App\Models\FreeIp;
use App\Models\ListPc;
use App\Validators\PcValidator;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;


class ListPcController extends Controller
{
    private $validator;

    public function __construct(PcValidator $validator)
    {
        $this->validator = $validator;
    }

    public function pcpanel()
    {
        return view("pc");
    }

    /**
     * @param Request $request
     * @return Application|Factory|View
     * @throws Exception
     */
    public function makepclist(Request $request)
    {
	$krp=$request->get('krp');
        $validate = $this->validator->getPcNotEmptyValidator($request);

        if ($validate->fails())
            throw new Exception('Неверные данные');

        $data = $this->getValues($request);

        $res = ListPc::where($data['field_name'], $data['value'])->get();
        $cin = $res->count();
/*
        if ($data['value'] == 'all') {
            $res = ListPc::all()->orderBy('etaj');
            $cin = $res->count();
        }
*/
        if(!empty($krp)){
            $res=ListPc::where('korp',$krp)->orderBy('etaj')->orderBy('pom')->get();
            $cin=$res->count();
            if($krp=='all') {$res=ListPc::all();$cin=$res->count();}
            return view("pctable",['tab'=>$res, 'cin'=>$cin]);
        }

        return view("pctable", ['tab' => $res, 'cin' => $cin]);

    }

    /**
     * @return Application|Factory|View
     */
    public function ffip()
    {

        $list36 = FreeIp::query()->select('free_ips.*')->
        leftJoin('list_pcs', function ($join) {
            $join->on('free_ips.ip', '=', 'list_pcs.ip');
        })->whereNull('list_pcs.ip')->where('free_ips.ip', 'like', '10.174.36.%')->get();

        $list37 = FreeIp::query()->select('free_ips.*')->
        leftJoin('list_pcs', function ($join) {
            $join->on('free_ips.ip', '=', 'list_pcs.ip');
        })->whereNull('list_pcs.ip')->where('free_ips.ip', 'like', '10.174.37.%')->get();

        $list38 = FreeIp::query()->select('free_ips.*')->
        leftJoin('list_pcs', function ($join) {
            $join->on('free_ips.ip', '=', 'list_pcs.ip');
        })->whereNull('list_pcs.ip')->where('free_ips.ip', 'like', '10.174.38.%')->get();

        $list39 = FreeIp::query()->select('free_ips.*')->
        leftJoin('list_pcs', function ($join) {
            $join->on('free_ips.ip', '=', 'list_pcs.ip');
        })->whereNull('list_pcs.ip')->where('free_ips.ip', 'like', '10.174.39.%')->get();

        return view('listfreeip', ['tab36' => $list36, 'tab37' => $list37, 'tab38' => $list38, 'tab39' => $list39]);
    }

    /**
     * @param Request $req
     * @return void
     */
    public function addfio(Request $req)
    {
        $id = $req->get('id');
        $fio = $req->get('fio');
        $model = ListPc::find($id);
        $model->doctor = $fio;
        $model->save();
    }

    /**
     * @param $request
     * @return array
     */
    private function getValues($request)
    {
        $keys = ['inpnum' => 'inv', 'inpip' => 'ip', 'krp' => 'korp'];
        $value = null;

        foreach ($keys as $key => $item) {
            if (!$value)
                $value = $request->get($key) ? ['field_name' => $item, 'value' => $request->get($key)] : [];
        }

        return $value;
    }

}



