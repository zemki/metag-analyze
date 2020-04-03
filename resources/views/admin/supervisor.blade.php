@extends('admin.layout')

@section('content')


    <div>
        @if(isset($message))

            <span @created="showtoast({{$message}})"></span>

        @endif


        <h1 class="title">Create a Supervisor</h1>
        <h6 class="subtitle text-red-500">A valid email is required. <br> If you don't enter the email text,
            the default will be used.</h6>
        <form method="POST" action="{{url('admin/users/supervisor')}}" class="mt-2" >
            @csrf
            <input type="hidden" value="2" name="role">
            <label for="name" class="label">
                Email
            </label>
            <input type="email" class="shadow appearance-none border rounded w-1/4 py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="email" required>


            <p class="text-base">You can use markdown in this field, it will be rendered in the email.</p>
            <label for="email" class="label mt-2">
                Email text
            </label>

            <textarea class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" name="emailtext" rows="3" cols="10"></textarea>

            <input type="checkbox" name="testEmail" value="true"> Don't create user but send
            email<br>


            <button class="bg-transparent hover:bg-blue-500 text-blue-700 font-semibold hover:text-white py-2 px-4 border border-blue-500 hover:border-transparent rounded float-right" type="submit">Send email</button>

        </form>
    </div>


@endsection
