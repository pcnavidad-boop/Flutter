<?php

namespace App\View\Components\Admin;

use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class Layout extends Component
{
    public string $title;

    public function __construct(string $title = 'Admin Panel')
    {
        $this->title = $title;
    }

    public function render(): View|Closure|string
    {
        return view('components.admin.layout');
    }
}
