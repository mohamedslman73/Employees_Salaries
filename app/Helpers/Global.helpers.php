<?php

function amount($amount,$format = false){
    if($format){
        return number_format($amount,2).' '.__('LE');
    }
    return $amount.' '.__('LE');
}


function whereBetween( &$eloquent,$columnName,$form,$to){
    if(!empty($form) && empty($to)){
        $eloquent->whereRaw("$columnName >= ?",[$form]);
    }elseif(empty($form) && !empty($to)){
        $eloquent->whereRaw("$columnName <= ?",[$to]);
    }elseif(!empty($form) && !empty($to)){
        $eloquent->where(function($query) use($columnName,$form,$to) {
            $query->whereRaw("$columnName BETWEEN ? AND ?",[$form,$to]);
        });
    }
}















