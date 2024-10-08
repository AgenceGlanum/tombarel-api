<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Document extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $table = 'dossiers';

    public function getAllDocuments()
    {
        return self::all();
    }

    public function getDocument($id)
    {
        return self::find($id);
    }
}
