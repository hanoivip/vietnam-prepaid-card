@extends('hanoivip::layouts.app')

@section('title', 'Webtopup result')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">

<p>Payment detail: {{$data->getDetail()}}</p>

@if ($data->isPending())
<form method="post" action="{{route('vpcard.flow1.query')}}">
    {{ csrf_field() }}
    <input type="hidden" id="trans" name="trans" value="{{$trans}}"/>
    	<button type="submit" class="btn btn-primary">Refresh</button>
</form>
@endif

@if ($data->isSuccess())
<p>Card amount: {{ $data->getAmount() }} {{ $data->getCurrency() }}</p><br/>
<a href="{{ route('recharge') }}" class="btn btn-primary">Buy game item</a><br/>
<a href="{{ route('vpcard.flow1') }}" class="btn btn-secondary">Pay more</a><br/>
@endif

@if ($data->isFailure())
<a href="{{ route('vpcard.flow1') }}" class="btn btn-primary">Pay again</a>
@endif

</div></div></div></div>
@endsection
