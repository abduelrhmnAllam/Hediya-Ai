@extends('layouts.app')

@section('content')
<div class="max-w-6xl mx-auto py-12">

    <h1 class="text-3xl font-bold mb-10">Feed Runs</h1>

    <table class="w-full bg-white shadow rounded-xl overflow-hidden">
        <thead class="bg-gray-100 text-left text-gray-600 text-sm">
            <tr>
                <th class="py-3 px-4">Run ID</th>
                <th class="py-3 px-4">Feed Code</th>
                <th class="py-3 px-4">Created</th>
                <th class="py-3 px-4"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-200 text-sm text-gray-800">
            @foreach($runs as $run)
           <tr>
    <td class="py-3 px-4 font-mono">{{ $run->id }}</td>
    <td class="py-3 px-4">{{ $run->feed->code }}</td>

    <td class="py-3 px-4 text-right">
        <a href="/feeds/runs/{{ $run->id }}" class="text-indigo-600 hover:underline">
            View Details →
        </a>
    </td>

    <td class="py-3 px-4 text-right">
        <form action="/feeds/runs/{{ $run->id }}" method="POST" onsubmit="return confirm('Delete this run permanently?')">
            @csrf
            @method('DELETE')
            <button class="text-red-600 hover:underline">Delete</button>
        </form>
    </td>
</tr>

            @endforeach
        </tbody>
    </table>

    <div class="mt-6">
        {{ $runs->links() }}
    </div>

</div>
@endsection
