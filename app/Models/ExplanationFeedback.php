<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExplanationFeedback extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'explanation_feedbacks';
    protected $fillable = [
        'bible_explanation_id',
        'is_positive',
        'comment',
        'testament',
        'book',
        'chapter',
        'verses',
        'user_ip',
        'user_agent',
    ];

    protected $casts = [
        'is_positive' => 'boolean',
        'chapter' => 'integer',
    ];

    /**
     * Get the Bible explanation that owns this feedback.
     */
    public function bibleExplanation()
    {
        return $this->belongsTo(BibleExplanation::class);
    }
}
