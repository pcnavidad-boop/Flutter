<?php

namespace App\View\Components\Customer;

use Illuminate\View\Component;
use Illuminate\View\View;

class Navbar extends Component
{
    public function render(): View
    {
        return view('components.customer.navbar');
    }
}
