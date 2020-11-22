<?php

namespace App\Models;

use App\Models\Model;

class Message extends Model
{

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    public function createdAtHuman()
    {
        return $this->created_at->format('d-m-Y') . ' (' . $this->created_at->diffForHumans() . ')';
    }
}
