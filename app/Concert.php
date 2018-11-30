<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Concert extends Model
{
    protected $guarded = [];
    protected $dates = ['date'];

    public function getFormattedDateAttribute()
    {
        return $this->date->format('F j, Y');
    }

    public function getFormattedStartTimeAttribute()
    {
        return $this->date->format('g:i a');
    }

    public function getTicketPriceInDollarsAttribute()
    {
        return number_format($this->ticket_price/100, 2);
    }

    public function scopePublished($query)
    {
        return $query->whereNotNull('published_at');
    }
}
