@extends('auth.app')


@section('content')
    <div class="bg-img"></div>
    <div class="container ">
        <div class="columns is-centered">

            <div class="column is-half">
                <div class="notification is-danger">
                    {{__('This link is not valid, please contact the administrator.')}}
                </div>
            </div>
        </div>
    </div>
@endsection
