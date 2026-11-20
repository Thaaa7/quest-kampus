@props(['name', 'fill' => false, 'class' => 'h-6 w-6'])
{!! \App\Support\Icon::svg($name, (bool) $fill, $class) !!}
