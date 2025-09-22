<?php
// app/Models/PanicLog.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanicLog extends Model
{
    use HasFactory;
    protected $fillable = ['user_id', 'latitude', 'longitude', 'status'];
}