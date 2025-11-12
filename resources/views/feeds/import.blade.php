@extends('layouts.app')

@section('content')

<div class="min-h-screen bg-gray-100 py-16">
    <div class="max-w-3xl mx-auto bg-white shadow-xl rounded-xl p-10 space-y-8">

        <h1 class="text-3xl font-bold tracking-tight">Import XML Feed</h1>
        <p class="text-gray-500 text-base">
            Upload XML/YML feed & assign country + default currency
        </p>

        {{-- upload only --}}
        <div>
            <label class="block font-semibold text-gray-800">Choose File</label>
            <input type="file" id="feedFile" accept=".xml,.yml,.yaml,.gz" class="w-full border rounded-lg px-4 py-3 text-lg">

            <div id="progressBox" class="mt-3 hidden">
                <div id="uploadResponse" class="text-sm mt-2 text-gray-700"></div>

                <div class="bg-gray-200 w-full rounded-full h-2">
                    <div id="progressBar" class="bg-indigo-600 h-2 rounded-full" style="width:0%"></div>
                </div>
                <div id="progressText" class="text-xs text-gray-600 mt-1"></div>
            </div>
        </div>

        {{-- import form --}}
        <form action="{{ url('/feeds/import') }}" method="POST" class="space-y-8">
            @csrf

            <input type="hidden" name="uploaded_file" id="uploaded_file" />

            {{-- FEED CODE --}}
            <div class="space-y-1">
                <label class="block font-semibold text-gray-800">Feed Code <span class="text-red-500">*</span></label>
                <input type="text" name="code" class="w-full border rounded-lg px-4 py-3 text-lg" placeholder="e.g. levelshoes_sa" required>
                <span class="text-xs text-gray-500">Same as CLI: --feed=</span>
            </div>

            {{-- FEED NAME --}}
            <div class="space-y-1">
                <label class="block font-semibold text-gray-800">Feed Name</label>
                <input type="text" name="name" class="w-full border rounded-lg px-4 py-3 text-lg" placeholder="Optional descriptive name">
                <span class="text-xs text-gray-500">Same as CLI: --name=</span>
            </div>

            {{-- COUNTRY --}}
            <div class="space-y-1">
                <label class="block font-semibold text-gray-800">Country</label>
                <input type="text" name="country" class="w-full border rounded-lg px-4 py-3 text-lg" placeholder="SA / AE / EG / ...">
                <span class="text-xs text-gray-500">Same as CLI: --country=</span>
            </div>

            {{-- CURRENCY --}}
            <div class="space-y-1">
                <label class="block font-semibold text-gray-800">Default Currency</label>
                <input type="text" name="currency" class="w-full border rounded-lg px-4 py-3 text-lg" placeholder="SAR / AED / EGP">
                <span class="text-xs text-gray-500">Same as CLI: --currency=</span>
            </div>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 transition text-white font-semibold py-4 rounded-lg text-lg">
                Import Feed
            </button>

        </form>
    </div>
</div>
<script>
document.getElementById('feedFile').addEventListener('change', function(){
    let file = this.files[0];
    let formData = new FormData();
    formData.append("file", file);

    let xhr = new XMLHttpRequest();
    xhr.open("POST", "/feeds/upload", true);
    xhr.setRequestHeader("X-CSRF-TOKEN", "{{ csrf_token() }}");

    document.getElementById('progressBox').classList.remove('hidden');

    xhr.upload.onprogress = function(e){
        if(e.lengthComputable){
            let percent = (e.loaded / e.total) * 100;
            document.getElementById('progressBar').style.width = percent+"%";
            document.getElementById('progressText').innerText = percent.toFixed(0)+"% uploading...";
        }
    }

    xhr.onload = function(){
        let res = JSON.parse(xhr.responseText);
        document.getElementById('uploaded_file').value = res.path;
        document.getElementById('progressText').innerText = "File Uploaded ✅";
    }

    xhr.send(formData);
});
</script>

@endsection
