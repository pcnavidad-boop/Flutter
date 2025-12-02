<?php

namespace App\View\Components\Admin;

use Illuminate\View\Component;
use Illuminate\Contracts\View\View;
use Closure;

class Sidebar extends Component
{
    public function render(): View|Closure|string
    {
        return view('components.admin.sidebar');
    }
}
