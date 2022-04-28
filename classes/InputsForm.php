<?php

namespace baptiste;

class InputsForm
{
    public function __construct(){
        
    }

    public function list()
    {
        return  snippet('form-card', ['text' => 'df', 'class' => 'error']);
    }
}
