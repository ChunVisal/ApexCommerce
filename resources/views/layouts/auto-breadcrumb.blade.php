@php
    $map = config('breadcrumbs');
    $current = request()->route()?->getName();
    $from = request()->query('from');   // deepest page the user came from

    // climb from a page up to the root; returns route names, root first
    $climb = function ($name) use ($map) {
        $chain = [];
        $steps = 0;
        while ($name && isset($map[$name]) && $steps < 10) {
            array_unshift($chain, $name);
            $name = $map[$name][1];
            $steps++;
        }
        return $chain;
    };

    $chain = $climb($current);
    $deepest = $current;

    // arrived from a deeper page that sits below this one? keep it in the trail
    if ($from && isset($map[$from])) {
        $fromChain = $climb($from);
        if (in_array($current, $fromChain)) {
            $chain = $fromChain;
            $deepest = $from;
        }
    }

    $trail = [];
    foreach ($chain as $name) {
        // the page you are on gets no tag; every other crumb remembers the deepest page
        $url = $name === $current ? url()->current() : route($name, ['from' => $deepest]);
        $trail[$url] = $map[$name][0];
    }
@endphp

@if (count($trail) > 1)
    <x-breadcrumb :breadcrumbs="$trail" />
@endif