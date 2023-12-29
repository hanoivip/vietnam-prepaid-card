@extends('hanoivip::layouts.app')

@section('title', 'Webtopup delay card')

@section('content')

<div class="container">
    <div class="row">
        <div class="col-md-8 col-md-offset-2">
            <div class="panel panel-default">
            
<p>{{__('hanoivip.payment::webtopup.pending')}}</p>

<a href="{{ route('vpcard.flow1.query', ['trans' => $trans]) }}" class="btn btn-primary">Refresh</a>

</div></div></div></div>

@endsection
