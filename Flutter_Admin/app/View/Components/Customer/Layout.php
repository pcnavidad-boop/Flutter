<?php

namespace App\View\Components\Customer;

use Illuminate\View\Component;
use Illuminate\View\View;

class Layout extends Component
{
    public string $title;

    public function __construct(string $title = 'Hotel Crepúsculo')
    {
        $this->title = $title;
    }

    public function render(): View
    {
        return view('components.customer.layout');
    }
}
