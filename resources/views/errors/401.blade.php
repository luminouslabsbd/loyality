@php
$guard = request()->segment(2);
$layout = (in_array($guard, ['affiliate', 'staff', 'partner', 'admin'])) ? $guard : 'member';
$home = route($layout.'.index');
@endphp
@extends($layout . '.layouts.default')

@section('page_title', trans('common.401_title') . config('default.page_title_delimiter') .  config('default.app_name'))

@section('content')
<section class="mx-auto">
    <div class="py-8 px-4 mx-auto max-w-screen-xl lg:py-16 lg:px-6">
        <div class="mx-auto max-w-screen-sm text-center">
            <h1 class="mb-4 text-7xl tracking-tight font-extrabold lg:text-9xl text-primary-600 dark:text-primary-500">401</h1>
            <p class="mb-4 text-3xl tracking-tight font-bold text-gray-900 md:text-4xl dark:text-white">{{ trans('common.401_title') }}</p>
            <p class="mb-4 text-lg font-light text-gray-500 dark:text-gray-400">{!! trans('common.401_description') !!}</p>
            <a href="{{ $home }}" class="inline-flex text-white bg-primary-600 hover:bg-primary-800 focus:ring-4 focus:outline-none focus:ring-primary-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:focus:ring-primary-900 my-4">{{ trans('common.go_back_home') }}</a>
        </div>   
    </div>
</section>
@stop