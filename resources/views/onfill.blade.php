<H1>Акт передачи картриджей на заправку от {{date("d.m.Y");}}</H1>
<table class="table table-bordered">
    <tr>
	<th>Код</th><th>Модель картриджа</th><th>Марка принтера</th>
    </tr>
    @foreach($listOnFill as $one)
	<tr>
	    <td>{{$one->sh_code}}</td>
	    <td>{{$one->cartName->name}}</td>
	    <td>{{$one->printName->name}}</td>
	</tr>
    @endforeach
</table>