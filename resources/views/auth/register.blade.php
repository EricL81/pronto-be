@extends('layouts.app')
@section('content')
<div class="container my-5">
@if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
    <div class="row">
        <div class="col-12 col-md-4 offset-md-4">
            <h2>{{__('ui.signUp')}}</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-12 col-md-4 offset-md-4">
            <form action="/register" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="exampleInputName1" class="form-label">{{__('ui.name')}}</label>
                    <input name="name" type="text" class="form-control" id="exampleInputName1"
                        aria-describedby="NameHelp">
                    <div id="NameHelp" class="form-text">{{__('ui.neverShareName')}}</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputEmail1" class="form-label">{{__('ui.email')}}</label>
                    <input name="email" type="email" class="form-control" id="exampleInputEmail1"
                        aria-describedby="emailHelp">
                    <div id="emailHelp" class="form-text">{{__('ui.neverShareEmail')}}</div>
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">{{__('ui.password')}}</label>
                    <input name="password" type="password" class="form-control" id="exampleInputPassword1">
                </div>
                <div class="mb-3">
                    <label for="exampleInputPassword1" class="form-label">{{__('ui.pwdConfirm')}}</label>
                    <input name="password_confirmation" type="password" class="form-control" id="exampleInputPassword1">
                </div>

                <button type="submit" class="btn btn-primary">{{__('ui.submit')}}</button>
            </form>

        </div>
    </div>
</div>

@endsection