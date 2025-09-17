<?php

namespace MetaFox\Photo\Events;

use Illuminate\Foundation\Events\Dispatchable;

class CreatePhoto
{
    use Dispatchable;

    /** @var string */
    private $moduleId;

    /** @var int */
    private $targetId;

    /** @var array<string, mixed> */
    private $response;

    /**
     * CreatePhoto constructor.
     *
     * @param string $moduleId
     * @param int    $targetId
     */
    public function __construct($moduleId, $targetId)
    {
        $this->moduleId = $moduleId;
        $this->targetId = $targetId;
    }

    public function getModuleId(): string
    {
        return $this->moduleId;
    }

    public function getTargetId(): int
    {
        return $this->targetId;
    }

    /**
     * @return array<string, mixed>
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param string $moduleId     - module name: groups, pages v.v...
     * @param int    $targetId     - module item id.
     * @param int    $privacy
     * @param int    $parentUserId - module item id
     *
     * @return void
     */
    public function addResponse($moduleId, $targetId, $privacy, $parentUserId)
    {
        $this->response = [
            'module_id'      => $moduleId,
            'target_id'      => $targetId,
            'privacy'        => $privacy,
            'parent_user_id' => $parentUserId,
        ];
    }
}
