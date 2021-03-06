<?php

namespace App\Http\Controllers\Api\Transformers;


class PayrollTransformer extends Transformer
{
    public function transform($item, $opt=null)
    {/*
        dd($item);
       return $item;*/

        return [
            'id' => $item['id'],
            'amount' => $item['amount'] .' LE',
            'Year' => $item['year'],
            'Month' => $item['month'],
            'staff_id' => $item['staff_id'],
            'created_at' =>$item['created_at']->diffForHumans(),
        ];
    }

}