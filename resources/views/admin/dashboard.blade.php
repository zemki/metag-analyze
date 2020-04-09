@extends('admin.layout')

@section('content')

    <section class="bg-blue-300 w-full p-4 rounded">

        <div class="container mx-auto mx-auto mx-auto mx-auto">
            <h1 class="font-bold text-4xl mb-2">
                Hello, {{$user->email}}.
            </h1>
            <h2 class="font-bold text-lg mb-2">
                I hope you are having a great day!
            </h2>
        </div>

    </section>
    <section class="flex">
        <div class="max-w-sm rounded overflow-hidden flex-1 p-4 text-center border-solid border-2 mt-1 mr-1 border-gray-400  bg-white lg:rounded-r p-4 ">

            <p class="font-bold text-xl mb-2">{{$usercount}}</p>
            <p class="font-bold text-lg mb-2">Users</p>

        </div>
        <div class="max-w-sm rounded overflow-hidden flex-1 p-4 text-center border-solid border-2 mt-1 mr-1 ml-1 border-gray-400  bg-white lg:rounded-r p-4 ">

            <p class="font-bold text-xl mb-2">{{$projectscount}}</p>
            <p class="font-bold text-lg mb-2">Projects</p>

        </div>
        <div class="max-w-sm rounded overflow-hidden flex-1 p-4 text-center border-solid border-2 mt-1 mr-1 ml-1 border-gray-400  bg-white lg:rounded-r p-4 ">

            <p class="font-bold text-xl mb-2">{{$casescount}}</p>
            <p class="font-bold text-lg mb-2">Cases</p>

        </div>

        <div class="max-w-sm rounded overflow-hidden flex-1 p-4 text-center border-solid border-2 mt-1 ml-1 border-gray-400  bg-white lg:rounded-r p-4 ">

            <p class="font-bold text-xl mb-2">{{$entriescount}}</p>
            <p class="font-bold text-lg mb-2">Total Entries</p>

        </div>

        <div class="max-w-sm rounded overflow-hidden flex-1 p-4 text-center border-solid border-2 mt-1 mr-1 ml-1 border-gray-400  bg-white lg:rounded-r p-4 ">

            <p class="font-bold text-xl mb-2">{{$actionscount}}</p>
            <p class="font-bold text-lg mb-2">Actions</p>

        </div>
    </section>
    <div class="w-full">
        <div class="mt-3 text-center font-bold">
            <p class="text-xl">
                ACTIONS
            </p>
        </div>
        <div class="content">
            <table class="table-auto">
                <thead>
                <tr>
                    <th class="px-4 py-2">id</th>
                    <th class="px-4 py-2">Author</th>
                    <th class="px-4 py-2">Name</th>
                    <th class="px-4 py-2">Description</th>
                    <th class="px-4 py-2">Url</th>
                    <th class="px-4 py-2">Time</th>
                </tr>
                </thead>
                <tbody>
                @foreach($actions as $action)
                    <tr
                            @if(strpos($action['description'],'delete'))
                            class="bg-red-600 text-gray-300 hover:bg-red-800"
                            @endif
                    >
                        <td class="border px-4 py-2">{{$action['id']}}</td>
                        <td class="border px-4 py-2">{{$action['user']['email']}}</td>
                        <td class="border px-4 py-2">{{$action['name']}}</td>
                        <td class="border px-4 py-2">{{$action['description']}}</td>
                        <td class="border px-4 py-2">{{$action['url']}}</td>
                        <td class="border px-4 py-2">{{$action['time']}}</td>
                    </tr>
                @endforeach

                </tbody>
            </table>
            {{ $actions->links() }}

        </div>
    </div>

@endsection
