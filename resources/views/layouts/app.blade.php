<!DOCTYPE html>
<html lang="en">

<head>
    @include('layouts.head')
</head>

<body>
    <div class="loader-bg">
        <div class="loader-track">
            <div class="loader-fill"></div>
        </div>
    </div>
    @include('layouts.sidebar')
    @include('layouts.header')
    @yield('content')
    @include('layouts.script')
</body>

</html>
