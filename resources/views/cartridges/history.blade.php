@extends('cartridges.index')
@section('inner_content')
    <table class="table">
        <thead>
        <tr>
            <th scope="col">Картридж</th>
            <th scope="col">Статус был</th>
            <th scope="col">Статус стал</th>
            <th scope="col">Дата</th>
        </tr>
        </thead>
        <tbody>
        @foreach($history as $hs)
            <tr>
                <th scope="row">{{$hs->cartridge->cartName->name}} {{$hs->cartridge->printName->name}}</th>
                <td>{{$hs->status_from}}</td>
                <td>{{$hs->status_to}}</td>
                <td>{{\Carbon\Carbon::parse($hs->catridge_id)->format('d.m.Y H:i:s')}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection
