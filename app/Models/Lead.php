<?php

namespace App\Models;

use App\Models\Model;

class Lead extends Model
{

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function createdAtHuman()
    {
        return $this->created_at->format('d-m-Y') . ' (' . $this->created_at->diffForHumans() . ')';
    }

    public function isSubscribed()
    {
        return $this->subscribed === '1' ? true : false;
    }
}
