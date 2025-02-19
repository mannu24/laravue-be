<?php

namespace App\Repositories;

use App\Models\Tag;
use App\Models\TagAssociate;

class TagRepository
{

    protected Tag $tag;
    protected TagAssociate $associate;

    public function __construct(
        Tag $tag,
        TagAssociate $associate
    ) {
        $this->tag = $tag;
        $this->associate = $associate;
    }

    public function findOrCreateTag(string $tagName, int $userId)
    {
        return $this->tag->firstOrCreate(
            ['name' => $tagName],
            ['created_by_id' => $userId]
        );
    }

    public function associateTag(int $tagId, int $recordId, string $recordType)
    {
        return $this->associate->firstOrCreate([
            'tag_id' => $tagId,
            'record_id' => $recordId,
            'record_type' => $recordType,
        ]);
    }
}
