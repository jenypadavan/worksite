<?php

namespace App\Http\Controllers;

use App\Models\FreeIp;
use App\Models\ListPc;
use App\Models\MisDoc;
use App\Validators\PcValidator;
use Exception;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Relations\MorphTo;


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
        $validate = $this->validator->getPcNotEmptyValidator($request);

        if ($validate->fails())
            throw new Exception('Неверные данные');

        $value = $this->getValues($request);

        $res = ListPc::where($value['type'], $value['value'])->get();
        $cin = $res->count();

        if ($value == 'all') {
            $res = ListPc::all();
            $cin = $res->count();
        }

        return view("pctable", ['tab' => $res, 'cin' => $cin]);

    }

    public function ffip()
    {

        //  $list36=FreeIp::with(['findfreeip'])->whereNull('listPc.ip')->where('freeIp.ip', 'like', '10.174.36.%')->get();
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

    public function addfio(Request $req)
    {
        $id = $req->get('id');
        $fio = $req->get('fio');
        $model = ListPc::find($id);
        $model->doctor = $fio;
        $model->save();
    }

    private function getValues($request)
    {
        $keys = ['inpnum' => 'inv', 'inpip' => 'ip', 'krp' => 'korp'];
        $value = null;

        foreach ($keys as $key => $item) {
            if (!$value)
                $value = $request->get($key) ? ['type' => $item, 'value' => $request->get($key)] : [];
        }

        return $value;
    }

}



