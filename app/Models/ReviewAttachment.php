<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

enum ReviewAttachmentFileType: string
{
    case IMAGE = 'image';
    case VIDEO = 'video';
}

class ReviewAttachment extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'transaction_item_id',
        'file_path',
        'file_type',
        'caption',
    ];

    // Relationships
    public function transaction_item()
    {
        return $this->belongsTo(TransactionItem::class);
    }
}
