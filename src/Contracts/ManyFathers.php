<?php

namespace App\Contracts;

interface ManyFathers
{
    public function getFathers();
    public function saveFathers(array $fathers);
    public function updateFathers(array $fathers);
    public function deleteFathers(array $fathers);
}