<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UFiles extends Model
{
    protected $fillable = ['user_id', 'original_filename', 'server_filename','converted_filename'];
    public static function getFileEntry($id){ //возвращает запись в бд по айди файла
        $entry = self::where(['id' => $id, 'user_id' => Auth::id()])
            ->first();
        return $entry;
    }
}
