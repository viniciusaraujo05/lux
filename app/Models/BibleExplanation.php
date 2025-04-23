<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BibleExplanation extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'bible_explanations';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'testament',
        'book',
        'chapter',
        'verses',
        'explanation_text',
        'source',
        'access_count'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'chapter' => 'integer',
        'access_count' => 'integer',
    ];

    /**
     * Increment the access count for this explanation.
     *
     * @return bool
     */
    public function incrementAccessCount()
    {
        $this->access_count++;
        return $this->save();
    }
}
