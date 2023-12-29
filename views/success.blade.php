@extends('hanoivip::layouts.app')

@section('title', 'Payment success')

@section('content')

<p>{{__('hanoivip.game::newrecharge.success')}}</p>
<a href="{{ route('vpcard.flow1') }}"><button>Pay more</button></a>

@endsection
