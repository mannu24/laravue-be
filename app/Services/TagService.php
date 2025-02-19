<?php

namespace App\Services;

use App\Repositories\TagRepository;
use Illuminate\Support\Facades\DB;
use Exception;

class TagService
{
    protected $tagRepository;

    public function __construct(TagRepository $tagRepository)
    {
        $this->tagRepository = $tagRepository;
    }

    public function addTags(array $tags, int $recordId, string $recordType, int $userId)
    {
        try {
            DB::beginTransaction();
            foreach ($tags as $tagName) {
                if (!empty($tagName)) {
                    $tag = $this->tagRepository->findOrCreateTag($tagName, $userId);
                    $this->tagRepository->associateTag($tag->id, $recordId, $recordType);
                }
            }
            DB::commit();
            return true;
        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }
}
